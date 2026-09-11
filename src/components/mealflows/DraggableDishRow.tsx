import type { DragEvent } from 'react';
import { Button } from 'antd';
import { CloseIcon, GripIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { RADII } from '@/theme/tokens';
import type { Dish } from '@/types';

export type BoardColumn = 'available' | 'member';

export const DISH_DRAG_FORMAT = 'application/x-chiefplanner-dish';

interface DraggableDishRowProps {
  dish: Dish;
  column: BoardColumn;
  onRemove?: () => void;
}

export function DraggableDishRow({ dish, column, onRemove }: DraggableDishRowProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const isMember = column === 'member';
  const accentSurface = resolvedMode === 'dark' ? accent.tintDark : accent.tint;

  const handleDragStart = (event: DragEvent<HTMLDivElement>) => {
    event.dataTransfer.setData(DISH_DRAG_FORMAT, JSON.stringify({ dishId: dish.id, column }));
    event.dataTransfer.effectAllowed = 'move';
  };

  return (
    <div
      draggable
      onDragStart={handleDragStart}
      style={{
        display: 'flex',
        alignItems: 'center',
        gap: 10,
        padding: 10,
        borderRadius: RADII.inner,
        cursor: 'grab',
        background: isMember ? accentSurface : 'transparent',
        border: isMember ? '1px solid transparent' : `1px solid ${neutrals.border}`,
      }}
    >
      <span style={{ color: resolvedMode === 'dark' ? '#4A5058' : '#C7CBD1', lineHeight: 0 }}>
        <GripIcon size={14} />
      </span>
      <span
        style={{
          width: 34,
          height: 34,
          flexShrink: 0,
          borderRadius: 8,
          background: isMember ? neutrals.surface : neutrals.surfaceMuted,
        }}
      />
      <span style={{ flex: 1, minWidth: 0, fontSize: 13, fontWeight: 600, color: neutrals.textPrimary }}>
        {dish.name}
      </span>
      {onRemove && (
        <Button
          type="text"
          size="small"
          aria-label={`Retirer ${dish.name} du mealflow`}
          icon={<CloseIcon size={14} />}
          onClick={onRemove}
        />
      )}
    </div>
  );
}
