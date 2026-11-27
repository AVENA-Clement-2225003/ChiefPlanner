import { useState } from 'react';
import { Button, Card, Input } from 'antd';
import { CloseIcon, PlusIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { ExtraItem } from '@/types';

interface ExtraListProps {
  items: ExtraItem[];
  onAdd: (label: string) => void;
  onRemove: (item: ExtraItem) => void;
}

/** Shopping items that are not part of a dish: cereals, toilet paper... */
export function ExtraList({ items, onAdd, onRemove }: ExtraListProps) {
  const { neutrals } = useAppTheme();
  const [label, setLabel] = useState('');

  const submit = () => {
    const trimmed = label.trim();
    if (trimmed.length === 0) {
      return;
    }
    onAdd(trimmed);
    setLabel('');
  };

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 24, display: 'flex', flexDirection: 'column', gap: 14 } }}
    >
      <span style={{ fontFamily: FONTS.heading, fontSize: 18, fontWeight: 600, color: neutrals.textPrimary }}>
        Extra
      </span>

      <div style={{ display: 'flex', gap: 8 }}>
        <Input
          value={label}
          placeholder="Céréales, papier toilette…"
          onChange={(event) => setLabel(event.target.value)}
          onPressEnter={submit}
        />
        <Button aria-label="Ajouter à la liste extra" icon={<PlusIcon size={14} />} onClick={submit} />
      </div>

      {items.length === 0 ? (
        <span style={{ fontSize: 14, color: neutrals.textSecondary }}>Aucun</span>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
          {items.map((item) => (
            <div
              key={item.id}
              style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 14, color: neutrals.textPrimary }}
            >
              <span
                style={{ width: 5, height: 5, borderRadius: 999, background: neutrals.textSecondary }}
                aria-hidden
              />
              <span style={{ flex: 1 }}>{item.label}</span>
              <Button
                type="text"
                size="small"
                aria-label={`Retirer ${item.label}`}
                icon={<CloseIcon size={14} />}
                onClick={() => onRemove(item)}
              />
            </div>
          ))}
        </div>
      )}
    </Card>
  );
}
