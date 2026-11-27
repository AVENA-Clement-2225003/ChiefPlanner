import catalog from '@/mocks/catalog.json';
import type { Category, Unit } from '@/types';
import { respondWith } from './apiClient';

const units = catalog.units as Unit[];
const categories = catalog.categories as unknown as Category[];

export function listUnits(): Promise<Unit[]> {
  return respondWith(units);
}

export function listCategories(): Promise<Category[]> {
  return respondWith(categories);
}

export function unitLabel(unitId: string): string {
  return units.find((unit) => unit.id === unitId)?.label ?? unitId;
}

export function categoryLabel(categoryId: string): string {
  return categories.find((category) => category.id === categoryId)?.label ?? categoryId;
}
