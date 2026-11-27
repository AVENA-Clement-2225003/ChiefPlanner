import type { HomeSummary } from '@/types';
import { listDishes } from './dishService';
import { getSpendingSummary } from './groceryService';
import { listMealFlows } from './mealFlowService';
import { getWeekPlan } from './weekService';

/** Aggregates the four dashboard tiles from the week, the spendings, the MealFlows and the dishes. */
export async function getHomeSummary(): Promise<HomeSummary> {
  const [week, spending, mealFlows, dishes] = await Promise.all([
    getWeekPlan(),
    getSpendingSummary(),
    listMealFlows(),
    listDishes(),
  ]);

  const plannedMeals = week.days.flatMap((day) => [day.lunch, day.dinner]);
  const filled = plannedMeals.filter((meal) => meal !== null && meal.dishId !== null);

  const prepTimes = filled
    .map((meal) => dishes.find((dish) => dish.id === meal?.dishId)?.prepTimeMinutes)
    .filter((minutes): minutes is number => typeof minutes === 'number');

  const activeFlow = mealFlows.find((flow) => flow.id === week.activeMealFlowId) ?? null;

  return {
    plannedMeals: { filled: filled.length, total: plannedMeals.filter((meal) => meal !== null).length },
    spending: {
      total: spending.monthTotal,
      currency: spending.currency,
      variationPercent: spending.variationPercent,
    },
    activeMealFlow: activeFlow
      ? { id: activeFlow.id, name: activeFlow.name, dishCount: activeFlow.dishIds.length }
      : null,
    averagePrepTimeMinutes:
      prepTimes.length === 0
        ? 0
        : Math.round(prepTimes.reduce((total, minutes) => total + minutes, 0) / prepTimes.length),
  };
}
