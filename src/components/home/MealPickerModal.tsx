import { Button, List, Modal, Tag } from 'antd';
import { categoryLabel } from '@/services/catalogService';
import { useAppTheme } from '@/theme/ThemeProvider';
import type { Dish, MealSlot, PlannedDay } from '@/types';

const SLOT_LABELS: Record<MealSlot, string> = { lunch: 'dejeuner', dinner: 'diner' };

interface MealPickerModalProps {
  open: boolean;
  day: PlannedDay | null;
  slot: MealSlot | null;
  dishes: Dish[];
  onClose: () => void;
  onSelect: (dish: Dish) => void;
  onClear: () => void;
}

export function MealPickerModal({ open, day, slot, dishes, onClose, onSelect, onClear }: MealPickerModalProps) {
  const { neutrals } = useAppTheme();
  const currentMeal = day && slot ? day[slot] : null;

  return (
    <Modal
      open={open}
      onCancel={onClose}
      title={day && slot ? `${day.dayLabel} - ${SLOT_LABELS[slot]}` : 'Choisir un plat'}
      footer={
        currentMeal ? (
          <Button danger onClick={onClear}>
            Retirer le plat
          </Button>
        ) : null
      }
      width={520}
    >
      <List
        dataSource={dishes}
        style={{ maxHeight: 420, overflowY: 'auto' }}
        renderItem={(dish) => (
          <List.Item
            key={dish.id}
            style={{ cursor: 'pointer' }}
            onClick={() => onSelect(dish)}
            actions={[<Tag key="category">{categoryLabel(dish.category)}</Tag>]}
          >
            <List.Item.Meta
              title={<span style={{ color: neutrals.textPrimary }}>{dish.name}</span>}
              description={`${dish.portions} pers. - ${dish.prepTimeMinutes} min`}
            />
          </List.Item>
        )}
      />
    </Modal>
  );
}
