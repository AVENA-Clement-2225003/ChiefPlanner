import { useState } from 'react';
import { App as AntdApp, Button, Input, Spin } from 'antd';
import { DishEditor } from '@/components/dishes/DishEditor';
import { DishSidebarList } from '@/components/dishes/DishSidebarList';
import { PlusIcon, SearchIcon } from '@/components/icons';
import { useAsyncData } from '@/hooks/useAsyncData';
import { listCategories, listUnits } from '@/services/catalogService';
import { listOwnDishes, saveDish } from '@/services/dishService';
import { listMealFlows } from '@/services/mealFlowService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { Dish } from '@/types';

export function DishesPage() {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();

  const dishes = useAsyncData(listOwnDishes, []);
  const units = useAsyncData(listUnits, []);
  const categories = useAsyncData(listCategories, []);
  const mealFlows = useAsyncData(listMealFlows, []);

  const [selectedDishId, setSelectedDishId] = useState<string | null>(null);
  const [search, setSearch] = useState('');
  const [saving, setSaving] = useState(false);

  const allDishes = dishes.data ?? [];
  const visibleDishes = allDishes.filter((dish) =>
    dish.name.toLowerCase().includes(search.trim().toLowerCase()),
  );
  const selectedDish = allDishes.find((dish) => dish.id === selectedDishId) ?? visibleDishes[0] ?? null;

  const handleSave = async (dish: Dish) => {
    setSaving(true);
    try {
      await saveDish(dish);
      dishes.reload();
      mealFlows.reload();
      message.success('Plat enregistre');
    } finally {
      setSaving(false);
    }
  };

  if (!dishes.data || !units.data || !categories.data || !mealFlows.data) {
    return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 16 }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 28, fontWeight: 600, color: neutrals.textPrimary }}>
          Plats
        </span>
        <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
          <Input
            value={search}
            placeholder="Rechercher un plat"
            prefix={<SearchIcon size={14} style={{ color: neutrals.textSecondary }} />}
            onChange={(event) => setSearch(event.target.value)}
            style={{ width: 260 }}
          />
          <Button
            type="primary"
            icon={<PlusIcon size={14} />}
            onClick={() => message.info('La creation de plat sera branchee sur l&apos;API')}
          >
            Nouveau plat
          </Button>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '320px 1fr', gap: 24, alignItems: 'start' }}>
        <DishSidebarList
          dishes={visibleDishes}
          selectedDishId={selectedDish?.id ?? null}
          onSelect={(dish) => setSelectedDishId(dish.id)}
        />

        {selectedDish ? (
          <DishEditor
            dish={selectedDish}
            units={units.data}
            categories={categories.data}
            mealFlows={mealFlows.data}
            saving={saving}
            onSave={handleSave}
            onCreateMealFlow={() => message.info('Creation de mealflow disponible sur la page MealFlows')}
          />
        ) : (
          <span style={{ color: neutrals.textSecondary }}>Aucun plat ne correspond a cette recherche.</span>
        )}
      </div>
    </div>
  );
}
