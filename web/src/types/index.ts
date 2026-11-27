export type Difficulty = 'easy' | 'medium' | 'hard';
export type MealSlot = 'lunch' | 'dinner';
export type UnitFamily = 'mass' | 'volume' | 'count';
export type TagKind = 'cuisine' | 'diet' | 'constraint';

export interface Unit {
  id: string;
  label: string;
  family: UnitFamily;
}

export interface Tag {
  id: string;
  label: string;
  kind: TagKind;
}

export interface Aisle {
  id: string;
  label: string;
}

export interface Catalog {
  units: Unit[];
  tags: Tag[];
  aisles: Aisle[];
}

/**
 * An ingredient line as the editor shows it: `value` is typed for the dish's `portions` people.
 * A dish read from the API is shown for ONE person (quantities are stored per person).
 */
export interface IngredientLine {
  id: string;
  name: string;
  value: number;
  unitId: string;
}

export interface RecipeStep {
  id: string;
  position: number;
  instruction: string;
}

export interface Dish {
  id: string;
  name: string;
  tagIds: string[];
  slots: MealSlot[];
  /** Number of people the ingredient `value`s are written for. */
  portions: number;
  prepTimeMinutes: number;
  difficulty: Difficulty;
  ingredients: IngredientLine[];
  steps: RecipeStep[];
  mealFlowIds: string[];
  /** When true the dish shows up in other users' "Tous les plats" search. */
  shared: boolean;
  ownedByCurrentUser: boolean;
  /** True when the current user adopted someone else's dish. */
  subscribed: boolean;
}

export interface SavedDish {
  dish: Dish;
  /** Count-unit quantities that are not whole numbers for one person: saved, but worth a second look. */
  warnings: { ingredient: string; quantityPerPerson: number }[];
}

export interface MealFlow {
  id: string;
  name: string;
  dishIds: string[];
}

export interface PlannedMeal {
  dishId: string | null;
  dishName: string | null;
  servings: number;
  /** The dish was removed since: the meal stays, waiting for another dish. */
  needsReplacement: boolean;
}

export interface PlannedDay {
  date: string;
  dayLabel: string;
  shortLabel: string;
  isToday: boolean;
  lunch: PlannedMeal | null;
  dinner: PlannedMeal | null;
}

export interface WeekPlan {
  weekStart: string;
  activeMealFlowId: string | null;
  hasPrevious: boolean;
  days: PlannedDay[];
}

export interface GroceryPack {
  unitId: string;
  size: number;
  count: number;
  price: number | null;
}

export interface GroceryItem {
  /** Stable key of the item, used to tick it. */
  id: string;
  name: string;
  value: number;
  unitId: string;
  aisleId: string | null;
  purchased: boolean;
  packs: { lines: GroceryPack[]; leftover: { quantity: number; unitId: string } } | null;
  estimatedPrice: number | null;
}

export interface ShoppingList {
  items: GroceryItem[];
  extras: ExtraItem[];
  estimatedTotal: number;
  unpricedItems: number;
}

export interface ExtraItem {
  id: string;
  label: string;
  aisleId: string | null;
}

export interface SpendingPoint {
  label: string;
  amount: number;
}

export interface SpendingSummary {
  monthTotal: number;
  currency: string;
  variationPercent: number | null;
  points: SpendingPoint[];
}

export interface HomeSummary {
  plannedMeals: { filled: number; total: number };
  spending: { total: number; currency: string; variationPercent: number | null };
  activeMealFlow: { id: string; name: string; dishCount: number } | null;
  averagePrepTimeMinutes: number;
}

export interface SlotPreference {
  day: number;
  slot: MealSlot;
  enabled: boolean;
  servings: number;
  flow: 'a' | 'b' | null;
}

export interface PlanningPreferences {
  mealFlowAId: string | null;
  mealFlowBId: string | null;
  slots: SlotPreference[];
}

export interface StorePack {
  unitId: string;
  size: number;
  price: number | null;
}

export interface StoreIngredient {
  ingredientId: number;
  name: string;
  aisleId: string | null;
  packs: StorePack[];
}

export interface AdminUser {
  id: number;
  name: string;
  email: string;
  isAdmin: boolean;
}

export interface CurrentUser {
  id: string;
  displayName: string;
  initials: string;
  isAdmin: boolean;
}
