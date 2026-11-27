import { useState } from 'react';
import { App as AntdApp, Spin } from 'antd';
import { DishResultGrid } from '@/components/discover/DishResultGrid';
import { DishSearchBar } from '@/components/discover/DishSearchBar';
import { TagFilter, type TagFilterValue } from '@/components/discover/TagFilter';
import { useAsyncData } from '@/hooks/useAsyncData';
import { listTags } from '@/services/catalogService';
import { listSharedDishes, setDishSubscription } from '@/services/dishService';
import type { Dish } from '@/types';

export function AllDishesPage() {
  const { message } = AntdApp.useApp();
  const dishes = useAsyncData(listSharedDishes, []);
  const tags = useAsyncData(listTags, []);

  const [search, setSearch] = useState('');
  const [tag, setTag] = useState<TagFilterValue>('all');

  const handleToggleSubscription = async (dish: Dish) => {
    await setDishSubscription(dish.id, !dish.subscribed);
    dishes.reload();
    message.success(dish.subscribed ? 'Plat retiré de vos plats' : 'Plat ajouté à vos plats');
  };

  if (!dishes.data || !tags.data) {
    return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  }

  const term = search.trim().toLowerCase();
  const visibleDishes = dishes.data.filter((dish) => {
    const matchesTag = tag === 'all' || dish.tagIds.includes(tag);
    const matchesTerm =
      term.length === 0 ||
      dish.name.toLowerCase().includes(term) ||
      dish.ingredients.some((ingredient) => ingredient.name.toLowerCase().includes(term));
    return matchesTag && matchesTerm;
  });

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 28, minHeight: '100%' }}>
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 20 }}>
        <DishSearchBar value={search} onChange={setSearch} />
        <TagFilter tags={tags.data} value={tag} onChange={setTag} />
      </div>

      <DishResultGrid dishes={visibleDishes} onToggleSubscription={handleToggleSubscription} />
    </div>
  );
}
