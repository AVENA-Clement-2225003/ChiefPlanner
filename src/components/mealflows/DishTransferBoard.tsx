import { useState, type DragEvent, type ReactNode } from 'react';
import { Card } from 'antd';
import {
  DISH_DRAG_FORMAT,
  DraggableDishRow,
  type BoardColumn,
} from '@/components/mealflows/DraggableDishRow';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { Dish } from '@/types';

interface DropColumnProps {
  title: string;
  count: number;
  accepts: BoardColumn;
  highlighted: boolean;
  isDropTarget: boolean;
  onDropDish: (dishId: string) => void;
  onDragStateChange: (target: BoardColumn | null) => void;
  children: ReactNode;
}

function DropColumn({
  title,
  count,
  accepts,
  highlighted,
  isDropTarget,
  onDropDish,
  onDragStateChange,
  children,
}: DropColumnProps) {
  const { accent, neutrals } = useAppTheme();

  const readPayload = (event: DragEvent<HTMLDivElement>): { dishId: string; column: BoardColumn } | null => {
    const raw = event.dataTransfer.getData(DISH_DRAG_FORMAT);
    return raw ? (JSON.parse(raw) as { dishId: string; column: BoardColumn }) : null;
  };

  return (
    <Card
      style={{
        borderRadius: RADII.card,
        borderColor: isDropTarget || highlighted ? accent.base : neutrals.border,
        borderStyle: isDropTarget ? 'dashed' : 'solid',
      }}
      styles={{ body: { padding: 20, display: 'flex', flexDirection: 'column', gap: 14 } }}
      onDragOver={(event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        onDragStateChange(accepts);
      }}
      onDragLeave={() => onDragStateChange(null)}
      onDrop={(event) => {
        event.preventDefault();
        onDragStateChange(null);
        const payload = readPayload(event);
        if (payload && payload.column !== accepts) {
          onDropDish(payload.dishId);
        }
      }}
    >
      <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', gap: 12 }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 17, fontWeight: 600, color: neutrals.textPrimary }}>
          {title}
        </span>
        <span style={{ fontSize: 12, color: neutrals.textSecondary }}>{count} plats</span>
      </div>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>{children}</div>
    </Card>
  );
}

interface DishTransferBoardProps {
  mealFlowName: string;
  availableDishes: Dish[];
  memberDishes: Dish[];
  onAdd: (dishId: string) => void;
  onRemove: (dishId: string) => void;
}

export function DishTransferBoard({
  mealFlowName,
  availableDishes,
  memberDishes,
  onAdd,
  onRemove,
}: DishTransferBoardProps) {
  const { neutrals } = useAppTheme();
  const [dropTarget, setDropTarget] = useState<BoardColumn | null>(null);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 24, alignItems: 'start' }}>
        <DropColumn
          title="Tous les plats"
          count={availableDishes.length}
          accepts="available"
          highlighted={false}
          isDropTarget={dropTarget === 'available'}
          onDropDish={onRemove}
          onDragStateChange={setDropTarget}
        >
          {availableDishes.map((dish) => (
            <DraggableDishRow key={dish.id} dish={dish} column="available" />
          ))}
        </DropColumn>

        <DropColumn
          title={mealFlowName}
          count={memberDishes.length}
          accepts="member"
          highlighted
          isDropTarget={dropTarget === 'member'}
          onDropDish={onAdd}
          onDragStateChange={setDropTarget}
        >
          {memberDishes.map((dish) => (
            <DraggableDishRow
              key={dish.id}
              dish={dish}
              column="member"
              onRemove={() => onRemove(dish.id)}
            />
          ))}
        </DropColumn>
      </div>

      <span style={{ textAlign: 'center', fontSize: 12, color: neutrals.textSecondary }}>
        Glissez un plat entre les colonnes pour l&apos;ajouter ou le retirer du mealflow.
      </span>
    </div>
  );
}
