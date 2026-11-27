export type DishCategory = 'poultry' | 'vegetarian' | 'fish' | 'meat' | 'starter' | 'dessert' | 'oven' | 'soup';
export type Difficulty = 'easy' | 'medium' | 'hard';
export type MealSlot = 'lunch' | 'dinner';

export interface Unit {
  id: string;
  label: string;
}

export interface Category {
  id: DishCategory;
  label: string;
}

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
  category: DishCategory;
  portions: number;
  prepTimeMinutes: number;
  difficulty: Difficulty;
  ingredients: IngredientLine[];
  steps: RecipeStep[];
  mealFlowIds: string[];
  /** When true the dish shows up in other users' "Tous les plats" search. */
  shared: boolean;
  ownedByCurrentUser: boolean;
  /** True when the current user subscribed to someone else's dish. */
  subscribed: boolean;
  imageUrl: string | null;
}

export interface MealFlow {
  id: string;
  name: string;
  dishIds: string[];
}

export interface PlannedMeal {
  dishId: string;
  dishName: string;
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
  days: PlannedDay[];
}

export interface GroceryItem {
  id: string;
  name: string;
  value: number;
  unitId: string;
  purchased: boolean;
}

export interface ExtraItem {
  id: string;
  label: string;
}

export interface SpendingPoint {
  label: string;
  amount: number;
}

export interface SpendingSummary {
  monthTotal: number;
  currency: string;
  variationPercent: number;
  points: SpendingPoint[];
}

export interface HomeSummary {
  plannedMeals: { filled: number; total: number };
  spending: { total: number; currency: string; variationPercent: number };
  activeMealFlow: { id: string; name: string; dishCount: number } | null;
  averagePrepTimeMinutes: number;
}

export interface CurrentUser {
  id: string;
  displayName: string;
  initials: string;
  isAdmin: boolean;
}
