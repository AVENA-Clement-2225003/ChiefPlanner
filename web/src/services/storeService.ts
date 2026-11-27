import type { StoreIngredient, StorePack } from '@/types';
import { http } from './apiClient';

/** "Mon magasin": the ingredients the user cooks with, as their own store sells them. */
export async function listStoreIngredients(): Promise<StoreIngredient[]> {
  const { data } = await http.get<{ data: StoreIngredient[] }>('/store');
  return data.data;
}

export async function saveStoreIngredient(
  ingredientId: number,
  aisleId: string | null,
  packs: StorePack[],
): Promise<StoreIngredient> {
  const { data } = await http.put<StoreIngredient>(`/store/${ingredientId}`, { aisleId, packs });
  return data;
}
