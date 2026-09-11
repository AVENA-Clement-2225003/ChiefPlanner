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
import { listDishes } from '@/services/dishService';
import {
  getSpendingSummary,
  listExtras,
  listGroceries,
  setGroceryPurchased,
  validatePurchase,
} from '@/services/groceryService';
import { getHomeSummary } from '@/services/statsService';
import { clearMeal, getWeekPlan, regenerateWeekPlan, setMeal } from '@/services/weekService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { Dish, GroceryItem, MealSlot, PlannedDay } from '@/types';

function formatToday(day: PlannedDay | undefined): string {
  if (!day) {
    return 'Voici ou vous en etes cette semaine.';
  }
  const formatted = new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }).format(
    new Date(day.date),
  );
  return `${formatted} - voici ou vous en etes cette semaine.`;
}

export function HomePage() {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();

  const summary = useAsyncData(getHomeSummary, []);
  const week = useAsyncData(getWeekPlan, []);
  const spending = useAsyncData(getSpendingSummary, []);
  const groceries = useAsyncData(listGroceries, []);
  const extras = useAsyncData(listExtras, []);
  const dishes = useAsyncData(listDishes, []);

  const [regenerating, setRegenerating] = useState(false);
  const [purchaseOpen, setPurchaseOpen] = useState(false);
  const [pickerTarget, setPickerTarget] = useState<{ day: PlannedDay; slot: MealSlot } | null>(null);

  const today = week.data?.days.find((day) => day.isToday) ?? null;
  const availableDishes = (dishes.data ?? []).filter((dish) => dish.ownedByCurrentUser || dish.subscribed);

  const refreshPlanning = () => {
    week.reload();
    summary.reload();
  };

  const handleRegenerate = async () => {
    setRegenerating(true);
    try {
      await regenerateWeekPlan();
      refreshPlanning();
      message.success('Semaine regeneree');
    } finally {
      setRegenerating(false);
    }
  };

  const handleToggleGrocery = async (item: GroceryItem, purchased: boolean) => {
    await setGroceryPurchased(item.id, purchased);
    groceries.reload();
  };

  const handleValidatePurchase = async (amount: number) => {
    await validatePurchase(amount);
    setPurchaseOpen(false);
    groceries.reload();
    spending.reload();
    summary.reload();
    message.success('Achat enregistre');
  };

  const handleSelectDish = async (dish: Dish) => {
    if (!pickerTarget) {
      return;
    }
    await setMeal(pickerTarget.day.date, pickerTarget.slot, { dishId: dish.id, dishName: dish.name });
    setPickerTarget(null);
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

  if (!summary.data || !week.data || !spending.data || !groceries.data || !extras.data) {
    return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  }

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
        onOpenRecipe={() => message.info('La fiche recette arrive dans un prochain ecran')}
      />

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 24, alignItems: 'start' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
          <WeekPlanner
            days={week.data.days}
            regenerating={regenerating}
            onPreviousWeek={() => message.info('Historique des semaines precedentes a venir')}
            onRegenerate={handleRegenerate}
            onSelectSlot={(day, slot) => setPickerTarget({ day, slot })}
          />
          <SpendingChart summary={spending.data} />
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
          <GroceryList
            items={groceries.data}
            onTogglePurchased={handleToggleGrocery}
            onValidatePurchase={() => setPurchaseOpen(true)}
          />
          <ExtraList items={extras.data} onEdit={() => message.info('Edition de la liste extra a venir')} />
        </div>
      </div>

      <MealPickerModal
        open={pickerTarget !== null}
        day={pickerTarget?.day ?? null}
        slot={pickerTarget?.slot ?? null}
        dishes={availableDishes}
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
