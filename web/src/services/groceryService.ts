import type { ExtraItem, GroceryItem, ShoppingList, SpendingSummary } from '@/types';
import { http } from './apiClient';

interface ApiShoppingItem {
  key: string;
  name: string;
  quantity: number;
  unitId: string;
  aisleId: string | null;
  purchased: boolean;
  packs: GroceryItem['packs'];
  estimatedPrice: number | null;
}

interface ApiShoppingList {
  items: ApiShoppingItem[];
  extras: ExtraItem[];
  estimatedTotal: number;
  unpricedItems: number;
}

export async function getShoppingList(): Promise<ShoppingList> {
  const { data } = await http.get<ApiShoppingList>('/shopping-list');
  return {
    ...data,
    items: data.items.map((item) => ({
      id: item.key,
      name: item.name,
      value: item.quantity,
      unitId: item.unitId,
      aisleId: item.aisleId,
      purchased: item.purchased,
      packs: item.packs,
      estimatedPrice: item.estimatedPrice,
    })),
  };
}

export async function getSpendingSummary(): Promise<SpendingSummary> {
  const { data } = await http.get<SpendingSummary>('/spendings/summary');
  return data;
}

export async function setGroceryPurchased(key: string, purchased: boolean): Promise<void> {
  await http.put('/shopping-list/ticks', { key, checked: purchased });
}

/** Closes the shopping trip: the paid amount goes to the spending history and the ticks are cleared. */
export async function validatePurchase(amount: number): Promise<void> {
  await http.post('/spendings', { amount });
  await http.delete('/shopping-list/ticks');
}

export async function addExtra(label: string, aisleId: string | null = null): Promise<ExtraItem> {
  const { data } = await http.post<ExtraItem>('/extras', { label, aisleId });
  return data;
}

export async function updateExtra(extraId: string, label: string, aisleId: string | null): Promise<ExtraItem> {
  const { data } = await http.put<ExtraItem>(`/extras/${extraId}`, { label, aisleId });
  return data;
}

export async function removeExtra(extraId: string): Promise<void> {
  await http.delete(`/extras/${extraId}`);
}
