import dishesMock from '@/mocks/dishes.json';
import type { Dish } from '@/types';
import { NotFoundError, respondWith } from './apiClient';

/** In-memory store seeded from the mock payload: edits survive until reload. */
const dishes: Dish[] = structuredClone(dishesMock) as unknown as Dish[];

function requireDish(dishId: string): Dish {
  const dish = dishes.find((candidate) => candidate.id === dishId);
  if (!dish) {
    throw new NotFoundError('Plat', dishId);
  }
  return dish;
}

export function listOwnDishes(): Promise<Dish[]> {
  return respondWith(dishes.filter((dish) => dish.ownedByCurrentUser));
}

export function listDishes(): Promise<Dish[]> {
  return respondWith(dishes);
}

/** Feed of the "Tous les plats" page: everything published by the community. */
export function listSharedDishes(): Promise<Dish[]> {
  return respondWith(dishes.filter((dish) => dish.shared));
}

export function getDish(dishId: string): Promise<Dish> {
  return respondWith(requireDish(dishId));
}

export function saveDish(updated: Dish): Promise<Dish> {
  const index = dishes.findIndex((dish) => dish.id === updated.id);
  if (index === -1) {
    throw new NotFoundError('Plat', updated.id);
  }
  dishes[index] = structuredClone(updated);
  return respondWith(dishes[index]);
}

export function setDishShared(dishId: string, shared: boolean): Promise<Dish> {
  const dish = requireDish(dishId);
  dish.shared = shared;
  return respondWith(dish);
}

export function setDishSubscription(dishId: string, subscribed: boolean): Promise<Dish> {
  const dish = requireDish(dishId);
  dish.subscribed = subscribed;
  return respondWith(dish);
}

export function setDishMealFlows(dishId: string, mealFlowIds: string[]): Promise<Dish> {
  const dish = requireDish(dishId);
  dish.mealFlowIds = [...mealFlowIds];
  return respondWith(dish);
}
