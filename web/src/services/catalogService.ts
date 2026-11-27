import type { Aisle, Catalog, Tag, Unit } from '@/types';
import { http } from './apiClient';

/** Units, tags and aisles are fixed lists: loaded once, then read synchronously by the screens. */
let catalog: Catalog | null = null;

export async function loadCatalog(): Promise<Catalog> {
  if (catalog === null) {
    const { data } = await http.get<Catalog>('/catalog');
    catalog = data;
  }
  return catalog;
}

export async function listUnits(): Promise<Unit[]> {
  return (await loadCatalog()).units;
}

export async function listTags(): Promise<Tag[]> {
  return (await loadCatalog()).tags;
}

export async function listAisles(): Promise<Aisle[]> {
  return (await loadCatalog()).aisles;
}

export function unitLabel(unitId: string): string {
  return catalog?.units.find((unit) => unit.id === unitId)?.label ?? unitId;
}

export function tagLabel(tagId: string): string {
  return catalog?.tags.find((tag) => tag.id === tagId)?.label ?? tagId;
}

/** The first labels of a dish's tags, for a compact caption. */
export function tagsCaption(tagIds: string[], limit = 2): string {
  return tagIds.slice(0, limit).map(tagLabel).join(', ');
}
