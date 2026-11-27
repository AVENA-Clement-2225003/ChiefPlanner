import type { Difficulty, Dish, MealFlow, MealSlot, SavedDish } from '@/types';
import { http } from './apiClient';
import { listMealFlows, setMealFlowDishes } from './mealFlowService';

interface ApiDish {
  id: number;
  name: string;
  prepTimeMinutes: number;
  difficulty: Difficulty;
  slots: MealSlot[];
  tagIds: string[];
  isShared: boolean;
  ownedByCurrentUser: boolean;
  subscribed: boolean;
  ingredients: { ingredientId: number; name: string; quantityPerPerson: number; unitId: string }[];
  steps: { id: number; position: number; instruction: string }[];
}

export const NEW_DISH_ID = 'new';

/** A dish read from the API is shown for ONE person: the API stores quantities per person (ADR 0005). */
function toDish(api: ApiDish, flows: MealFlow[]): Dish {
  const id = String(api.id);
  return {
    id,
    name: api.name,
    tagIds: api.tagIds,
    slots: api.slots,
    portions: 1,
    prepTimeMinutes: api.prepTimeMinutes,
    difficulty: api.difficulty,
    ingredients: api.ingredients.map((line) => ({
      id: String(line.ingredientId),
      name: line.name,
      value: line.quantityPerPerson,
      unitId: line.unitId,
    })),
    steps: api.steps.map((step) => ({ id: String(step.id), position: step.position, instruction: step.instruction })),
    mealFlowIds: flows.filter((flow) => flow.dishIds.includes(id)).map((flow) => flow.id),
    shared: api.isShared,
    ownedByCurrentUser: api.ownedByCurrentUser,
    subscribed: api.subscribed,
  };
}

function toPayload(dish: Dish) {
  return {
    name: dish.name,
    prepTimeMinutes: dish.prepTimeMinutes,
    difficulty: dish.difficulty,
    slots: dish.slots,
    tagIds: dish.tagIds,
    isShared: dish.shared,
    servings: dish.portions,
    ingredients: dish.ingredients.map((line) => ({ ingredientName: line.name, quantity: line.value, unitId: line.unitId })),
    steps: dish.steps.map((step) => step.instruction),
  };
}

export function emptyDish(): Dish {
  return {
    id: NEW_DISH_ID,
    name: '',
    tagIds: [],
    slots: ['lunch', 'dinner'],
    portions: 2,
    prepTimeMinutes: 30,
    difficulty: 'easy',
    ingredients: [],
    steps: [],
    mealFlowIds: [],
    shared: false,
    ownedByCurrentUser: true,
    subscribed: false,
  };
}

/** The user's catalogue: the dishes they created and the ones they adopted. */
export async function listDishes(): Promise<Dish[]> {
  const [{ data }, flows] = await Promise.all([http.get<{ data: ApiDish[] }>('/dishes'), listMealFlows()]);
  return data.data.map((dish) => toDish(dish, flows));
}

export async function listOwnDishes(): Promise<Dish[]> {
  return (await listDishes()).filter((dish) => dish.ownedByCurrentUser);
}

/** Feed of the "Tous les plats" page: what the Community shares. */
export async function listSharedDishes(): Promise<Dish[]> {
  const [{ data }, flows] = await Promise.all([http.get<{ data: ApiDish[] }>('/dishes/shared'), listMealFlows()]);
  return data.data.map((dish) => toDish(dish, flows));
}

export async function getDish(dishId: string): Promise<Dish> {
  const [{ data }, flows] = await Promise.all([http.get<ApiDish>(`/dishes/${dishId}`), listMealFlows()]);
  return toDish(data, flows);
}

/** Creates the dish when it is new, otherwise updates it. Also applies the MealFlows ticked in the editor. */
export async function saveDish(dish: Dish): Promise<SavedDish> {
  const isNew = dish.id === NEW_DISH_ID;
  const { data } = isNew
    ? await http.post<{ dish: ApiDish; warnings: SavedDish['warnings'] }>('/dishes', toPayload(dish))
    : await http.put<{ dish: ApiDish; warnings: SavedDish['warnings'] }>(`/dishes/${dish.id}`, toPayload(dish));

  await setDishMealFlows(String(data.dish.id), dish.mealFlowIds);
  const flows = await listMealFlows();

  return { dish: toDish(data.dish, flows), warnings: data.warnings };
}

export async function deleteDish(dishId: string): Promise<void> {
  await http.delete(`/dishes/${dishId}`);
}

export async function setDishShared(dishId: string, shared: boolean): Promise<void> {
  await http.put(`/dishes/${dishId}/share`, { isShared: shared });
}

export async function setDishSubscription(dishId: string, subscribed: boolean): Promise<void> {
  await (subscribed ? http.put(`/dishes/${dishId}/subscription`) : http.delete(`/dishes/${dishId}/subscription`));
}

/** Makes the dish a member of exactly the given MealFlows, touching only the flows that change. */
export async function setDishMealFlows(dishId: string, mealFlowIds: string[]): Promise<void> {
  const wanted = new Set(mealFlowIds);
  const flows = await listMealFlows();

  await Promise.all(
    flows
      .filter((flow) => flow.dishIds.includes(dishId) !== wanted.has(flow.id))
      .map((flow) =>
        setMealFlowDishes(
          flow.id,
          wanted.has(flow.id) ? [...flow.dishIds, dishId] : flow.dishIds.filter((id) => id !== dishId),
        ),
      ),
  );
}
