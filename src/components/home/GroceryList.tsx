import { Button, Card, Checkbox } from 'antd';
import { BagCheckIcon } from '@/components/icons';
import { unitLabel } from '@/services/catalogService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { GroceryItem } from '@/types';

interface GroceryListProps {
  items: GroceryItem[];
  onTogglePurchased: (item: GroceryItem, purchased: boolean) => void;
  onValidatePurchase: () => void;
}

export function GroceryList({ items, onTogglePurchased, onValidatePurchase }: GroceryListProps) {
  const { accent, neutrals } = useAppTheme();
  const purchasedCount = items.filter((item) => item.purchased).length;
  const progress = items.length === 0 ? 0 : Math.round((purchasedCount / items.length) * 100);

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 24, display: 'flex', flexDirection: 'column', gap: 14 } }}
    >
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 18, fontWeight: 600, color: neutrals.textPrimary }}>
          A acheter
        </span>
        <Button type="primary" icon={<BagCheckIcon size={14} />} onClick={onValidatePurchase}>
          Valider
        </Button>
      </div>

      <div style={{ height: 6, borderRadius: 999, background: neutrals.surfaceMuted, overflow: 'hidden' }}>
        <div style={{ height: '100%', width: `${progress}%`, background: accent.base }} />
      </div>
      <span style={{ fontSize: 12, color: neutrals.textSecondary }}>
        {purchasedCount} sur {items.length} ingredients acquis
      </span>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
        {items.map((item) => (
          <div key={item.id} style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <Checkbox
              checked={item.purchased}
              onChange={(event) => onTogglePurchased(item, event.target.checked)}
            />
            <span
              style={{
                flex: 1,
                fontSize: 14,
                color: item.purchased ? neutrals.textDisabled : neutrals.textPrimary,
                textDecoration: item.purchased ? 'line-through' : 'none',
              }}
            >
              {item.name}
            </span>
            <span
              style={{ fontSize: 13, color: item.purchased ? neutrals.textDisabled : neutrals.textSecondary }}
            >
              {item.value} {unitLabel(item.unitId)}
            </span>
          </div>
        ))}
      </div>
    </Card>
  );
}
