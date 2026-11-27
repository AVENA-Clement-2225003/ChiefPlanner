import extrasMock from '@/mocks/extras.json';
import groceriesMock from '@/mocks/groceries.json';
import spendingMock from '@/mocks/spending.json';
import type { ExtraItem, GroceryItem, SpendingSummary } from '@/types';
import { NotFoundError, respondWith } from './apiClient';

const groceries: GroceryItem[] = structuredClone(groceriesMock);
const extras: ExtraItem[] = structuredClone(extrasMock);
const spending: SpendingSummary = structuredClone(spendingMock);

export function listGroceries(): Promise<GroceryItem[]> {
  return respondWith(groceries);
}

export function listExtras(): Promise<ExtraItem[]> {
  return respondWith(extras);
}

export function getSpendingSummary(): Promise<SpendingSummary> {
  return respondWith(spending);
}

export function setGroceryPurchased(groceryId: string, purchased: boolean): Promise<GroceryItem> {
  const item = groceries.find((candidate) => candidate.id === groceryId);
  if (!item) {
    throw new NotFoundError('Ingredient', groceryId);
  }
  item.purchased = purchased;
  return respondWith(item);
}

/**
 * Closes the current shopping list: the paid amount feeds the spending history,
 * exactly like the "valider l'achat" form of the current production app.
 */
export function validatePurchase(amount: number): Promise<SpendingSummary> {
  spending.points.push({ label: `S${spending.points.length + 1}`, amount });
  spending.monthTotal += amount;
  groceries.forEach((item) => {
    item.purchased = false;
  });
  return respondWith(spending);
}

export function addExtra(label: string): Promise<ExtraItem> {
  const item: ExtraItem = { id: `extra-${Date.now()}`, label };
  extras.push(item);
  return respondWith(item);
}

export function removeExtra(extraId: string): Promise<void> {
  const index = extras.findIndex((item) => item.id === extraId);
  if (index !== -1) {
    extras.splice(index, 1);
  }
  return respondWith(undefined);
}
