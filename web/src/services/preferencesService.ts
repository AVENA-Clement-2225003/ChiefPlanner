import type { PlanningPreferences } from '@/types';
import { http } from './apiClient';

interface ApiPreferences {
  mealFlowAId: number | null;
  mealFlowBId: number | null;
  slots: PlanningPreferences['slots'];
}

function toPreferences(api: ApiPreferences): PlanningPreferences {
  return {
    mealFlowAId: api.mealFlowAId === null ? null : String(api.mealFlowAId),
    mealFlowBId: api.mealFlowBId === null ? null : String(api.mealFlowBId),
    slots: api.slots,
  };
}

export async function getPreferences(): Promise<PlanningPreferences> {
  const { data } = await http.get<ApiPreferences>('/preferences');
  return toPreferences(data);
}

export async function savePreferences(preferences: PlanningPreferences): Promise<PlanningPreferences> {
  const { data } = await http.put<ApiPreferences>('/preferences', {
    mealFlowAId: preferences.mealFlowAId === null ? null : Number(preferences.mealFlowAId),
    mealFlowBId: preferences.mealFlowBId === null ? null : Number(preferences.mealFlowBId),
    slots: preferences.slots,
  });
  return toPreferences(data);
}
