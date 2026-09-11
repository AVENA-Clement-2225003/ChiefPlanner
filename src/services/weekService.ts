import weekMock from '@/mocks/week.json';
import type { MealSlot, PlannedMeal, WeekPlan } from '@/types';
import { respondWith } from './apiClient';
import { listDishes } from './dishService';

const weekPlan: WeekPlan = structuredClone(weekMock) as unknown as WeekPlan;

export function getWeekPlan(): Promise<WeekPlan> {
  return respondWith(weekPlan);
}

export function getTodayPlan(): Promise<WeekPlan['days'][number] | null> {
  return respondWith(weekPlan.days.find((day) => day.isToday) ?? null);
}

/** Mirrors the "regenerate" button: reshuffles the slots from the available dishes. */
export async function regenerateWeekPlan(): Promise<WeekPlan> {
  const dishes = await listDishes();
  const available = dishes.filter((dish) => dish.ownedByCurrentUser || dish.subscribed);

  weekPlan.days = weekPlan.days.map((day) => {
    const pick = (): PlannedMeal | null => {
      if (available.length === 0 || Math.random() < 0.25) {
        return null;
      }
      const dish = available[Math.floor(Math.random() * available.length)];
      return { dishId: dish.id, dishName: dish.name };
    };
    return { ...day, lunch: pick(), dinner: pick() };
  });

  return respondWith(weekPlan);
}

export function clearMeal(date: string, slot: MealSlot): Promise<WeekPlan> {
  const day = weekPlan.days.find((candidate) => candidate.date === date);
  if (day) {
    day[slot] = null;
  }
  return respondWith(weekPlan);
}

export function setMeal(date: string, slot: MealSlot, meal: PlannedMeal): Promise<WeekPlan> {
  const day = weekPlan.days.find((candidate) => candidate.date === date);
  if (day) {
    day[slot] = meal;
  }
  return respondWith(weekPlan);
}
