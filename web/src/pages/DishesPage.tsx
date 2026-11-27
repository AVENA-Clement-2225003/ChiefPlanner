import { useState } from 'react';
import { App as AntdApp, Button, Input, Spin } from 'antd';
import { DishEditor } from '@/components/dishes/DishEditor';
import { DishSidebarList } from '@/components/dishes/DishSidebarList';
import { PlusIcon, SearchIcon } from '@/components/icons';
import { useAsyncData } from '@/hooks/useAsyncData';
import { listTags, listUnits } from '@/services/catalogService';
import { deleteDish, emptyDish, listDishes, NEW_DISH_ID, saveDish } from '@/services/dishService';
import { apiErrorMessage } from '@/services/apiClient';
import { listMealFlows } from '@/services/mealFlowService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { Dish } from '@/types';

export function DishesPage() {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();

  const dishes = useAsyncData(listDishes, []);
  const units = useAsyncData(listUnits, []);
  const tags = useAsyncData(listTags, []);
  const mealFlows = useAsyncData(listMealFlows, []);

  const [selectedDishId, setSelectedDishId] = useState<string | null>(null);
  const [newDish, setNewDish] = useState<Dish | null>(null);
  const [search, setSearch] = useState('');
  const [saving, setSaving] = useState(false);
  const [warnings, setWarnings] = useState<string[]>([]);

  const allDishes = dishes.data ?? [];
  const visibleDishes = allDishes.filter((dish) => dish.name.toLowerCase().includes(search.trim().toLowerCase()));
  const selectedDish = newDish ?? allDishes.find((dish) => dish.id === selectedDishId) ?? visibleDishes[0] ?? null;

  const handleSave = async (dish: Dish) => {
    setSaving(true);
    try {
      const saved = await saveDish(dish);
      setWarnings(
        saved.warnings.map(
          (warning) => `« ${warning.ingredient} » : ${warning.quantityPerPerson} par personne.`,
        ),
      );
      setNewDish(null);
      setSelectedDishId(saved.dish.id);
      dishes.reload();
      mealFlows.reload();
      message.success('Plat enregistré');
    } catch (error) {
      message.error(apiErrorMessage(error, "Le plat n'a pas pu être enregistré."));
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (dish: Dish) => {
    await deleteDish(dish.id);
    setSelectedDishId(null);
    dishes.reload();
    mealFlows.reload();
    message.success('Plat supprimé');
  };

  const handleSelect = (dish: Dish) => {
    setNewDish(null);
    setWarnings([]);
    setSelectedDishId(dish.id);
  };

  if (!dishes.data || !units.data || !tags.data || !mealFlows.data) {
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
            onClick={() => {
              setWarnings([]);
              setNewDish(emptyDish());
            }}
          >
            Nouveau plat
          </Button>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '320px 1fr', gap: 24, alignItems: 'start' }}>
        <DishSidebarList
          dishes={visibleDishes}
          selectedDishId={newDish ? NEW_DISH_ID : (selectedDish?.id ?? null)}
          onSelect={handleSelect}
        />

        {selectedDish ? (
          <DishEditor
            dish={selectedDish}
            units={units.data}
            tags={tags.data}
            mealFlows={mealFlows.data}
            saving={saving}
            warnings={warnings}
            readOnly={!selectedDish.ownedByCurrentUser}
            onSave={handleSave}
            onDelete={handleDelete}
            onCreateMealFlow={() => message.info('Créez vos MealFlows depuis la page MealFlows')}
          />
        ) : (
          <span style={{ color: neutrals.textSecondary }}>
            {allDishes.length === 0
              ? 'Vous n’avez pas encore de plat : créez le premier avec « Nouveau plat ».'
              : 'Aucun plat ne correspond à cette recherche.'}
          </span>
        )}
      </div>
    </div>
  );
}
