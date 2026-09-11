import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { Category, DishCategory } from '@/types';

export type CategoryFilterValue = DishCategory | 'all';

interface CategoryFilterProps {
  categories: Category[];
  value: CategoryFilterValue;
  onChange: (value: CategoryFilterValue) => void;
}

export function CategoryFilter({ categories, value, onChange }: CategoryFilterProps) {
  const { accent, neutrals } = useAppTheme();

  const options: { value: CategoryFilterValue; label: string }[] = [
    { value: 'all', label: 'Tous' },
    ...categories.map((category) => ({ value: category.id as CategoryFilterValue, label: category.label })),
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
