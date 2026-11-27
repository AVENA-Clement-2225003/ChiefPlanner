import mealFlowsMock from '@/mocks/mealFlows.json';
import type { MealFlow } from '@/types';
import { NotFoundError, respondWith } from './apiClient';
import { getDish, listDishes, setDishMealFlows } from './dishService';

interface MealFlowRecord {
  id: string;
  name: string;
}

const mealFlows: MealFlowRecord[] = structuredClone(mealFlowsMock);

/** Membership lives on the dish (`dish.mealFlowIds`), so flows are composed here. */
async function composeMealFlows(): Promise<MealFlow[]> {
  const dishes = await listDishes();
  return mealFlows.map((flow) => ({
    id: flow.id,
    name: flow.name,
    dishIds: dishes.filter((dish) => dish.mealFlowIds.includes(flow.id)).map((dish) => dish.id),
  }));
}

export async function listMealFlows(): Promise<MealFlow[]> {
  return respondWith(await composeMealFlows());
}

export async function getMealFlow(mealFlowId: string): Promise<MealFlow> {
  const flows = await composeMealFlows();
  const flow = flows.find((candidate) => candidate.id === mealFlowId);
  if (!flow) {
    throw new NotFoundError('MealFlow', mealFlowId);
  }
  return respondWith(flow);
}

export async function addDishToMealFlow(mealFlowId: string, dishId: string): Promise<MealFlow> {
  const dish = await getDish(dishId);
  if (!dish.mealFlowIds.includes(mealFlowId)) {
    await setDishMealFlows(dishId, [...dish.mealFlowIds, mealFlowId]);
  }
  return getMealFlow(mealFlowId);
}

export async function removeDishFromMealFlow(mealFlowId: string, dishId: string): Promise<MealFlow> {
  const dish = await getDish(dishId);
  await setDishMealFlows(
    dishId,
    dish.mealFlowIds.filter((id) => id !== mealFlowId),
  );
  return getMealFlow(mealFlowId);
}

export async function createMealFlow(name: string): Promise<MealFlow> {
  const record: MealFlowRecord = { id: `flow-${mealFlows.length + 1}-${Date.now()}`, name };
  mealFlows.push(record);
  return respondWith({ ...record, dishIds: [] });
}
