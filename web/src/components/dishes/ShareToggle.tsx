import { Switch } from 'antd';
import { useAppTheme } from '@/theme/ThemeProvider';
import { RADII } from '@/theme/tokens';

interface ShareToggleProps {
  shared: boolean;
  onChange: (shared: boolean) => void;
}

/** Publishing a dish makes it searchable by other users on "Tous les plats". */
export function ShareToggle({ shared, onChange }: ShareToggleProps) {
  const { neutrals } = useAppTheme();

  return (
    <div
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: 16,
        padding: '16px 20px',
        background: neutrals.background,
        border: `1px solid ${neutrals.border}`,
        borderRadius: 12,
      }}
    >
      <span style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
        <span style={{ fontSize: 14, fontWeight: 600, color: neutrals.textPrimary }}>Partager ce plat</span>
        <span style={{ fontSize: 12, color: neutrals.textSecondary }}>
          Visible dans les recherches des autres utilisateurs (Tous les plats)
        </span>
      </span>
      <Switch checked={shared} onChange={onChange} style={{ borderRadius: RADII.pill }} />
    </div>
  );
}
