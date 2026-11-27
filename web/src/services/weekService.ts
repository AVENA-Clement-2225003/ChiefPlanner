import type { MealSlot, PlannedDay, PlannedMeal, WeekPlan } from '@/types';
import { http } from './apiClient';
import { getPreferences } from './preferencesService';

interface ApiMeal {
  day: number;
  slot: MealSlot;
  dishId: number | null;
  dishName: string | null;
  servings: number;
  needsReplacement: boolean;
}

interface ApiWeek {
  meals: ApiMeal[];
  hasPrevious: boolean;
}

const DAY_LABELS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
const DAYS_IN_WEEK = 7;

/** Local date as `YYYY-MM-DD` (not UTC: a Sunday evening must stay a Sunday). */
export function isoDate(date: Date): string {
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${date.getFullYear()}-${month}-${day}`;
}

function mondayOf(date: Date): Date {
  const monday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  monday.setDate(monday.getDate() - ((monday.getDay() + 6) % DAYS_IN_WEEK));
  return monday;
}

/** 0 for Monday up to 6 for Sunday, the day index the API uses. */
export function dayIndexOf(date: string): number {
  return (new Date(`${date}T12:00:00`).getDay() + 6) % DAYS_IN_WEEK;
}

function toPlannedMeal(meal: ApiMeal | undefined): PlannedMeal | null {
  if (!meal) {
    return null;
  }
  return {
    dishId: meal.dishId === null ? null : String(meal.dishId),
    dishName: meal.dishName,
    servings: meal.servings,
    needsReplacement: meal.needsReplacement,
  };
}

export function toWeekPlan(week: ApiWeek, activeMealFlowId: string | null, today: Date = new Date()): WeekPlan {
  const monday = mondayOf(today);
  const todayKey = isoDate(today);

  const days: PlannedDay[] = DAY_LABELS.map((label, index) => {
    const date = new Date(monday);
    date.setDate(monday.getDate() + index);
    const find = (slot: MealSlot) => week.meals.find((meal) => meal.day === index && meal.slot === slot);
    return {
      date: isoDate(date),
      dayLabel: label,
      shortLabel: label.slice(0, 3),
      isToday: isoDate(date) === todayKey,
      lunch: toPlannedMeal(find('lunch')),
      dinner: toPlannedMeal(find('dinner')),
    };
  });

  return { weekStart: isoDate(monday), activeMealFlowId, hasPrevious: week.hasPrevious, days };
}

async function currentPlan(week: ApiWeek): Promise<WeekPlan> {
  const preferences = await getPreferences();
  return toWeekPlan(week, preferences.mealFlowAId);
}

export async function getWeekPlan(): Promise<WeekPlan> {
  const { data } = await http.get<ApiWeek>('/week');
  return currentPlan(data);
}

/** The "refresh" button: a new Proposal for the whole Week. */
export async function regenerateWeekPlan(): Promise<WeekPlan> {
  const { data } = await http.post<ApiWeek>('/week/proposal');
  return currentPlan(data);
}

/** Goes back to the Week before the last Proposal. */
export async function revertWeekPlan(): Promise<WeekPlan> {
  const { data } = await http.post<ApiWeek>('/week/revert');
  return currentPlan(data);
}

export async function setMeal(date: string, slot: MealSlot, meal: { dishId: string; servings?: number }): Promise<void> {
  await http.put(`/week/meals/${dayIndexOf(date)}/${slot}`, { dishId: Number(meal.dishId), servings: meal.servings });
}

export async function setMealServings(date: string, slot: MealSlot, servings: number): Promise<void> {
  await http.put(`/week/meals/${dayIndexOf(date)}/${slot}`, { servings });
}

export async function clearMeal(date: string, slot: MealSlot): Promise<void> {
  await http.delete(`/week/meals/${dayIndexOf(date)}/${slot}`);
}
