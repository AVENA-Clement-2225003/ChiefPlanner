import { Button, InputNumber, List, Modal, Tag } from 'antd';
import { tagsCaption } from '@/services/catalogService';
import { useAppTheme } from '@/theme/ThemeProvider';
import type { Dish, MealSlot, PlannedDay } from '@/types';

const SLOT_LABELS: Record<MealSlot, string> = { lunch: 'midi', dinner: 'soir' };

interface MealPickerModalProps {
  open: boolean;
  day: PlannedDay | null;
  slot: MealSlot | null;
  dishes: Dish[];
  /** Number of people of the meal being edited, null when nothing is planned there yet. */
  servings: number | null;
  onServingsChange: (servings: number) => void;
  onClose: () => void;
  onSelect: (dish: Dish) => void;
  onClear: () => void;
}

export function MealPickerModal({
  open,
  day,
  slot,
  dishes,
  servings,
  onServingsChange,
  onClose,
  onSelect,
  onClear,
}: MealPickerModalProps) {
  const { neutrals } = useAppTheme();
  const currentMeal = day && slot ? day[slot] : null;
  const suitable = slot ? dishes.filter((dish) => dish.slots.includes(slot)) : dishes;

  return (
    <Modal
      open={open}
      onCancel={onClose}
      title={day && slot ? `${day.dayLabel} - ${SLOT_LABELS[slot]}` : 'Choisir un plat'}
      footer={
        currentMeal ? (
          <Button danger onClick={onClear}>
            Retirer ce repas
          </Button>
        ) : null
      }
      width={520}
    >
      {servings !== null && (
        <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
          <span style={{ color: neutrals.textSecondary }}>Nombre de personnes</span>
          <InputNumber min={1} max={100} value={servings} onChange={(value) => value && onServingsChange(value)} />
        </div>
      )}
      <List
        dataSource={suitable}
        locale={{ emptyText: 'Aucun plat adapté à ce moment dans vos plats.' }}
        style={{ maxHeight: 420, overflowY: 'auto' }}
        renderItem={(dish) => (
          <List.Item
            key={dish.id}
            style={{ cursor: 'pointer' }}
            onClick={() => onSelect(dish)}
            actions={[<Tag key="tags">{tagsCaption(dish.tagIds) || 'sans étiquette'}</Tag>]}
          >
            <List.Item.Meta
              title={<span style={{ color: neutrals.textPrimary }}>{dish.name}</span>}
              description={`${dish.prepTimeMinutes} min`}
            />
          </List.Item>
        )}
      />
    </Modal>
  );
}
