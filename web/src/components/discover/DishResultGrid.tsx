import { DishResultCard } from '@/components/discover/DishResultCard';
import { useAppTheme } from '@/theme/ThemeProvider';
import type { Dish } from '@/types';

interface DishResultGridProps {
  dishes: Dish[];
  onToggleSubscription: (dish: Dish) => void;
}

export function DishResultGrid({ dishes, onToggleSubscription }: DishResultGridProps) {
  const { neutrals } = useAppTheme();

  if (dishes.length === 0) {
    return (
      <span style={{ textAlign: 'center', color: neutrals.textSecondary }}>
        Aucun plat ne correspond a cette recherche.
      </span>
    );
  }

  return (
    <div
      style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(4, minmax(0, 1fr))',
        // Rows stay tight: grid would otherwise stretch them to fill the flex space.
        alignContent: 'start',
        gap: 20,
        flex: 1,
      }}
    >
      {dishes.map((dish) => (
        <DishResultCard key={dish.id} dish={dish} onToggleSubscription={onToggleSubscription} />
      ))}
    </div>
  );
}
