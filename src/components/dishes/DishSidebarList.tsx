import { Card } from 'antd';
import { ImagePlaceholderIcon } from '@/components/icons';
import { categoryLabel } from '@/services/catalogService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { RADII } from '@/theme/tokens';
import type { Dish } from '@/types';

interface DishSidebarListProps {
  dishes: Dish[];
  selectedDishId: string | null;
  onSelect: (dish: Dish) => void;
}

export function DishSidebarList({ dishes, selectedDishId, onSelect }: DishSidebarListProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const accentSurface = resolvedMode === 'dark' ? accent.tintDark : accent.tint;
  const accentIcon = resolvedMode === 'dark' ? accent.light : accent.base;

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 12, display: 'flex', flexDirection: 'column', gap: 6 } }}
    >
      {dishes.map((dish) => {
        const selected = dish.id === selectedDishId;
        return (
          <button
            key={dish.id}
            type="button"
            onClick={() => onSelect(dish)}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: 10,
              padding: 10,
              borderRadius: RADII.inner,
              cursor: 'pointer',
              textAlign: 'left',
              background: selected ? accentSurface : 'transparent',
              border: selected ? `1px solid ${accent.base}` : '1px solid transparent',
            }}
          >
            <span
              style={{
                width: 44,
                height: 44,
                borderRadius: RADII.inner,
                flexShrink: 0,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                background: selected ? neutrals.surface : neutrals.surfaceMuted,
                color: selected ? accentIcon : neutrals.textDisabled,
              }}
            >
              <ImagePlaceholderIcon size={20} />
            </span>
            <span style={{ flex: 1, minWidth: 0 }}>
              <span
                style={{
                  display: 'block',
                  fontSize: 14,
                  fontWeight: 600,
                  color: neutrals.textPrimary,
                  overflow: 'hidden',
                  textOverflow: 'ellipsis',
                  whiteSpace: 'nowrap',
                }}
              >
                {dish.name}
              </span>
              <span style={{ display: 'block', fontSize: 12, color: neutrals.textSecondary }}>
                {categoryLabel(dish.category)} - {dish.prepTimeMinutes} min
              </span>
            </span>
          </button>
        );
      })}
    </Card>
  );
}
