import type { MealFlow } from '@/types';
import { http } from './apiClient';

interface ApiMealFlow {
  id: number;
  name: string;
  dishIds: number[];
}

export function toMealFlow(flow: ApiMealFlow): MealFlow {
  return { id: String(flow.id), name: flow.name, dishIds: flow.dishIds.map(String) };
}

export async function listMealFlows(): Promise<MealFlow[]> {
  const { data } = await http.get<{ data: ApiMealFlow[] }>('/meal-flows');
  return data.data.map(toMealFlow);
}

export async function createMealFlow(name: string): Promise<MealFlow> {
  const { data } = await http.post<ApiMealFlow>('/meal-flows', { name });
  return toMealFlow(data);
}

export async function renameMealFlow(mealFlowId: string, name: string): Promise<MealFlow> {
  const { data } = await http.put<ApiMealFlow>(`/meal-flows/${mealFlowId}`, { name });
  return toMealFlow(data);
}

export async function setMealFlowDishes(mealFlowId: string, dishIds: string[]): Promise<MealFlow> {
  const { data } = await http.put<ApiMealFlow>(`/meal-flows/${mealFlowId}`, { dishIds: dishIds.map(Number) });
  return toMealFlow(data);
}

export async function deleteMealFlow(mealFlowId: string): Promise<void> {
  await http.delete(`/meal-flows/${mealFlowId}`);
}

export async function addDishToMealFlow(mealFlowId: string, dishId: string): Promise<MealFlow> {
  const flows = await listMealFlows();
  const flow = flows.find((candidate) => candidate.id === mealFlowId);
  const dishIds = new Set(flow?.dishIds ?? []);
  dishIds.add(dishId);
  return setMealFlowDishes(mealFlowId, [...dishIds]);
}

export async function removeDishFromMealFlow(mealFlowId: string, dishId: string): Promise<MealFlow> {
  const flows = await listMealFlows();
  const flow = flows.find((candidate) => candidate.id === mealFlowId);
  return setMealFlowDishes(
    mealFlowId,
    (flow?.dishIds ?? []).filter((id) => id !== dishId),
  );
}
