import { SearchIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';

interface DishSearchBarProps {
  value: string;
  onChange: (value: string) => void;
}

export function DishSearchBar({ value, onChange }: DishSearchBarProps) {
  const { neutrals } = useAppTheme();

  return (
    <div
      style={{
        width: 640,
        maxWidth: '100%',
        display: 'flex',
        alignItems: 'center',
        gap: 10,
        padding: '14px 20px',
        background: neutrals.surface,
        border: `1px solid ${neutrals.border}`,
        borderRadius: 999,
      }}
    >
      <SearchIcon size={18} style={{ color: neutrals.textSecondary, flexShrink: 0 }} />
      <input
        value={value}
        placeholder="Rechercher un plat, un ingredient..."
        onChange={(event) => onChange(event.target.value)}
        style={{
          flex: 1,
          border: 'none',
          outline: 'none',
          background: 'transparent',
          fontFamily: FONTS.body,
          fontSize: 15,
          color: neutrals.textPrimary,
        }}
      />
    </div>
  );
}
