import { useState } from 'react';
import { App as AntdApp, Spin } from 'antd';
import { ExtraList } from '@/components/home/ExtraList';
import { GroceryList } from '@/components/home/GroceryList';
import { MealPickerModal } from '@/components/home/MealPickerModal';
import { PurchaseValidationModal } from '@/components/home/PurchaseValidationModal';
import { SpendingChart } from '@/components/home/SpendingChart';
import { StatTiles } from '@/components/home/StatTiles';
import { TodayCard } from '@/components/home/TodayCard';
import { WeekPlanner } from '@/components/home/WeekPlanner';
import { useAsyncData } from '@/hooks/useAsyncData';
import { apiErrorMessage } from '@/services/apiClient';
import { listAisles } from '@/services/catalogService';
import { listDishes } from '@/services/dishService';
import {
  addExtra,
  getShoppingList,
  getSpendingSummary,
  removeExtra,
  setGroceryPurchased,
  validatePurchase,
} from '@/services/groceryService';
import { getHomeSummary } from '@/services/statsService';
import { clearMeal, getWeekPlan, regenerateWeekPlan, revertWeekPlan, setMeal, setMealServings } from '@/services/weekService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { Dish, ExtraItem, GroceryItem, MealSlot, PlannedDay } from '@/types';

function formatToday(day: PlannedDay | undefined): string {
  if (!day) {
    return 'Voici où vous en êtes cette semaine.';
  }
  const formatted = new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }).format(
    new Date(`${day.date}T12:00:00`),
  );
  return `${formatted} - voici où vous en êtes cette semaine.`;
}

export function HomePage() {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();

  const summary = useAsyncData(getHomeSummary, []);
  const week = useAsyncData(getWeekPlan, []);
  const spending = useAsyncData(getSpendingSummary, []);
  const shopping = useAsyncData(getShoppingList, []);
  const aisles = useAsyncData(listAisles, []);
  const dishes = useAsyncData(listDishes, []);

  const [regenerating, setRegenerating] = useState(false);
  const [purchaseOpen, setPurchaseOpen] = useState(false);
  const [pickerTarget, setPickerTarget] = useState<{ day: PlannedDay; slot: MealSlot } | null>(null);

  const today = week.data?.days.find((day) => day.isToday) ?? null;

  const refreshPlanning = () => {
    week.reload();
    summary.reload();
    shopping.reload();
  };

  const attempt = async (action: () => Promise<void>, failure: string) => {
    try {
      await action();
    } catch (error) {
      message.error(apiErrorMessage(error, failure));
    }
  };

  const handleRegenerate = async () => {
    setRegenerating(true);
    await attempt(async () => {
      await regenerateWeekPlan();
      refreshPlanning();
      message.success('Nouvelle proposition pour la semaine');
    }, 'La proposition a échoué.');
    setRegenerating(false);
  };

  const handleRevert = () =>
    attempt(async () => {
      await revertWeekPlan();
      refreshPlanning();
      message.success('Proposition précédente retrouvée');
    }, 'Aucune proposition précédente.');

  const handleToggleGrocery = async (item: GroceryItem, purchased: boolean) => {
    await setGroceryPurchased(item.id, purchased);
    shopping.reload();
  };

  const handleValidatePurchase = async (amount: number) => {
    await validatePurchase(amount);
    setPurchaseOpen(false);
    shopping.reload();
    spending.reload();
    summary.reload();
    message.success('Achat enregistré');
  };

  const handleAddExtra = async (label: string) => {
    await addExtra(label);
    shopping.reload();
  };

  const handleRemoveExtra = async (extra: ExtraItem) => {
    await removeExtra(extra.id);
    shopping.reload();
  };

  const handleSelectDish = async (dish: Dish) => {
    if (!pickerTarget) {
      return;
    }
    await setMeal(pickerTarget.day.date, pickerTarget.slot, { dishId: dish.id });
    setPickerTarget(null);
    refreshPlanning();
  };

  const handleServings = async (servings: number) => {
    if (!pickerTarget) {
      return;
    }
    await setMealServings(pickerTarget.day.date, pickerTarget.slot, servings);
    refreshPlanning();
  };

  const handleClearMeal = async () => {
    if (!pickerTarget) {
      return;
    }
    await clearMeal(pickerTarget.day.date, pickerTarget.slot);
    setPickerTarget(null);
    refreshPlanning();
  };

  if (!summary.data || !week.data || !spending.data || !shopping.data || !aisles.data) {
    return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  }

  const pickedMeal = pickerTarget ? pickerTarget.day[pickerTarget.slot] : null;

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 28, fontWeight: 600, color: neutrals.textPrimary }}>
          Bonjour
        </span>
        <span style={{ fontSize: 14, color: neutrals.textSecondary }}>{formatToday(today ?? undefined)}</span>
      </div>

      <StatTiles summary={summary.data} />

      <TodayCard
        day={today}
        onReplace={() => today && setPickerTarget({ day: today, slot: 'lunch' })}
        onOpenRecipe={() => message.info('La fiche recette arrive dans un prochain écran')}
      />

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 24, alignItems: 'start' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
          <WeekPlanner
            days={week.data.days}
            regenerating={regenerating}
            canRevert={week.data.hasPrevious}
            onRevert={handleRevert}
            onRegenerate={handleRegenerate}
            onSelectSlot={(day, slot) => setPickerTarget({ day, slot })}
          />
          <SpendingChart summary={spending.data} />
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
          <GroceryList
            items={shopping.data.items}
            aisles={aisles.data}
            estimatedTotal={shopping.data.estimatedTotal}
            unpricedItems={shopping.data.unpricedItems}
            currency={spending.data.currency}
            onTogglePurchased={handleToggleGrocery}
            onValidatePurchase={() => setPurchaseOpen(true)}
          />
          <ExtraList items={shopping.data.extras} onAdd={handleAddExtra} onRemove={handleRemoveExtra} />
        </div>
      </div>

      <MealPickerModal
        open={pickerTarget !== null}
        day={pickerTarget?.day ?? null}
        slot={pickerTarget?.slot ?? null}
        dishes={dishes.data ?? []}
        servings={pickedMeal?.servings ?? null}
        onServingsChange={handleServings}
        onClose={() => setPickerTarget(null)}
        onSelect={handleSelectDish}
        onClear={handleClearMeal}
      />

      <PurchaseValidationModal
        open={purchaseOpen}
        currency={spending.data.currency}
        onCancel={() => setPurchaseOpen(false)}
        onConfirm={handleValidatePurchase}
      />
    </div>
  );
}
