import { useState } from 'react';
import { App as AntdApp, Spin } from 'antd';
import { CategoryFilter, type CategoryFilterValue } from '@/components/discover/CategoryFilter';
import { DishResultGrid } from '@/components/discover/DishResultGrid';
import { DishSearchBar } from '@/components/discover/DishSearchBar';
import { useAsyncData } from '@/hooks/useAsyncData';
import { listCategories } from '@/services/catalogService';
import { listSharedDishes, setDishSubscription } from '@/services/dishService';
import type { Dish } from '@/types';

export function AllDishesPage() {
  const { message } = AntdApp.useApp();
  const dishes = useAsyncData(listSharedDishes, []);
  const categories = useAsyncData(listCategories, []);

  const [search, setSearch] = useState('');
  const [category, setCategory] = useState<CategoryFilterValue>('all');

  const handleToggleSubscription = async (dish: Dish) => {
    await setDishSubscription(dish.id, !dish.subscribed);
    dishes.reload();
    message.success(dish.subscribed ? 'Plat retire de vos plats' : 'Plat ajoute a vos plats');
  };

  if (!dishes.data || !categories.data) {
    return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  }

  const term = search.trim().toLowerCase();
  const visibleDishes = dishes.data.filter((dish) => {
    const matchesCategory = category === 'all' || dish.category === category;
    const matchesTerm =
      term.length === 0 ||
      dish.name.toLowerCase().includes(term) ||
      dish.ingredients.some((ingredient) => ingredient.name.toLowerCase().includes(term));
    return matchesCategory && matchesTerm;
  });

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 28, minHeight: '100%' }}>
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 20 }}>
        <DishSearchBar value={search} onChange={setSearch} />
        <CategoryFilter categories={categories.data} value={category} onChange={setCategory} />
      </div>

      <DishResultGrid dishes={visibleDishes} onToggleSubscription={handleToggleSubscription} />
    </div>
  );
}
