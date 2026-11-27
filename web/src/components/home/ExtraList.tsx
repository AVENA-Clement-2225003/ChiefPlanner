import { Button, Card } from 'antd';
import { PencilIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { ExtraItem } from '@/types';

interface ExtraListProps {
  items: ExtraItem[];
  onEdit: () => void;
}

export function ExtraList({ items, onEdit }: ExtraListProps) {
  const { neutrals } = useAppTheme();

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 24, display: 'flex', flexDirection: 'column', gap: 14 } }}
    >
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 18, fontWeight: 600, color: neutrals.textPrimary }}>
          Extra
        </span>
        <Button
          aria-label="Modifier la liste extra"
          icon={<PencilIcon size={15} />}
          onClick={onEdit}
          style={{ width: 32, height: 32 }}
        />
      </div>

      {items.length === 0 ? (
        <span style={{ fontSize: 14, color: neutrals.textSecondary }}>Aucun</span>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {items.map((item) => (
            <div
              key={item.id}
              style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 14, color: neutrals.textPrimary }}
            >
              <span
                style={{ width: 5, height: 5, borderRadius: 999, background: neutrals.textSecondary }}
                aria-hidden
              />
              {item.label}
            </div>
          ))}
        </div>
      )}
    </Card>
  );
}
