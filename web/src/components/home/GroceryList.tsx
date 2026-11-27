import { Button, Card, Checkbox } from 'antd';
import { BagCheckIcon } from '@/components/icons';
import { unitLabel } from '@/services/catalogService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { Aisle, GroceryItem, GroceryPack } from '@/types';

const OTHER_AISLE: Aisle = { id: 'none', label: 'Autres' };

interface GroceryListProps {
  items: GroceryItem[];
  aisles: Aisle[];
  estimatedTotal: number;
  unpricedItems: number;
  currency: string;
  onTogglePurchased: (item: GroceryItem, purchased: boolean) => void;
  onValidatePurchase: () => void;
}

function describePacks(packs: GroceryPack[]): string {
  return packs.map((pack) => `${pack.count} × ${pack.size} ${unitLabel(pack.unitId)}`).join(' + ');
}

/** Items grouped by the aisle of the user's store, in the order of the fixed aisle list. */
function groupByAisle(items: GroceryItem[], aisles: Aisle[]): { aisle: Aisle; items: GroceryItem[] }[] {
  const known = new Set(aisles.map((aisle) => aisle.id));
  const groups = [...aisles, OTHER_AISLE].map((aisle) => ({
    aisle,
    items: items.filter((item) =>
      aisle.id === OTHER_AISLE.id ? item.aisleId === null || !known.has(item.aisleId) : item.aisleId === aisle.id,
    ),
  }));
  return groups.filter((group) => group.items.length > 0);
}

export function GroceryList({
  items,
  aisles,
  estimatedTotal,
  unpricedItems,
  currency,
  onTogglePurchased,
  onValidatePurchase,
}: GroceryListProps) {
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
          À acheter
        </span>
        <Button type="primary" icon={<BagCheckIcon size={14} />} onClick={onValidatePurchase}>
          Valider
        </Button>
      </div>

      <div style={{ height: 6, borderRadius: 999, background: neutrals.surfaceMuted, overflow: 'hidden' }}>
        <div style={{ height: '100%', width: `${progress}%`, background: accent.base }} />
      </div>
      <span style={{ fontSize: 12, color: neutrals.textSecondary }}>
        {purchasedCount} sur {items.length} ingrédients acquis
        {estimatedTotal > 0 && (
          <>
            {' '}
            · estimé {estimatedTotal} {currency}
            {unpricedItems > 0 && ` (${unpricedItems} sans prix)`}
          </>
        )}
      </span>

      {groupByAisle(items, aisles).map((group) => (
        <div key={group.aisle.id} style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          <span
            style={{
              fontSize: 11,
              fontWeight: 600,
              letterSpacing: '0.05em',
              textTransform: 'uppercase',
              color: neutrals.textSecondary,
            }}
          >
            {group.aisle.label}
          </span>
          {group.items.map((item) => (
            <div key={item.id} style={{ display: 'flex', alignItems: 'flex-start', gap: 10 }}>
              <Checkbox
                checked={item.purchased}
                onChange={(event) => onTogglePurchased(item, event.target.checked)}
              />
              <span style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
                <span
                  style={{
                    fontSize: 14,
                    color: item.purchased ? neutrals.textDisabled : neutrals.textPrimary,
                    textDecoration: item.purchased ? 'line-through' : 'none',
                  }}
                >
                  {item.name}
                </span>
                {item.packs && (
                  <span style={{ fontSize: 12, color: neutrals.textSecondary }}>
                    {describePacks(item.packs.lines)}
                    {item.packs.leftover.quantity > 0 &&
                      ` · reste ${item.packs.leftover.quantity} ${unitLabel(item.packs.leftover.unitId)}`}
                  </span>
                )}
              </span>
              <span
                style={{ fontSize: 13, color: item.purchased ? neutrals.textDisabled : neutrals.textSecondary }}
              >
                {item.value} {unitLabel(item.unitId)}
              </span>
            </div>
          ))}
        </div>
      ))}

      {items.length === 0 && (
        <span style={{ fontSize: 14, color: neutrals.textSecondary }}>
          Planifiez des repas pour voir la liste de courses.
        </span>
      )}
    </Card>
  );
}
