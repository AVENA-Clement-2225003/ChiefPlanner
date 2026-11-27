import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { Tag } from '@/types';

export type TagFilterValue = string | 'all';

interface TagFilterProps {
  tags: Tag[];
  value: TagFilterValue;
  onChange: (value: TagFilterValue) => void;
}

export function TagFilter({ tags, value, onChange }: TagFilterProps) {
  const { accent, neutrals } = useAppTheme();

  const options: { value: TagFilterValue; label: string }[] = [
    { value: 'all', label: 'Tous' },
    ...tags.map((tag) => ({ value: tag.id, label: tag.label })),
  ];

  return (
    <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap', justifyContent: 'center' }}>
      {options.map((option) => {
        const selected = option.value === value;
        return (
          <button
            key={option.value}
            type="button"
            aria-pressed={selected}
            onClick={() => onChange(option.value)}
            style={{
              padding: '8px 18px',
              borderRadius: 999,
              cursor: 'pointer',
              fontFamily: FONTS.body,
              fontSize: 13,
              fontWeight: 600,
              background: selected ? accent.base : 'transparent',
              border: selected ? `1px solid ${accent.base}` : `1px solid ${neutrals.border}`,
              color: selected ? '#FFFFFF' : neutrals.textSecondary,
            }}
          >
            {option.label}
          </button>
        );
      })}
    </div>
  );
}
