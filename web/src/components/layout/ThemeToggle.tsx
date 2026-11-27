import { useAppTheme } from '@/theme/ThemeProvider';
import { MoonIcon, SunIcon } from '@/components/icons';

/** Quick light/dark switch; the tri-state (systeme) lives in the account modal. */
export function ThemeToggle() {
  const { resolvedMode, neutrals, setMode } = useAppTheme();
  const isDark = resolvedMode === 'dark';

  return (
    <button
      type="button"
      aria-label={isDark ? 'Passer en mode clair' : 'Passer en mode sombre'}
      onClick={() => setMode(isDark ? 'light' : 'dark')}
      style={{
        width: 56,
        height: 28,
        borderRadius: 999,
        border: 'none',
        padding: 2,
        cursor: 'pointer',
        background: neutrals.surfaceMuted,
        display: 'flex',
        alignItems: 'center',
        justifyContent: isDark ? 'flex-end' : 'flex-start',
      }}
    >
      <span
        style={{
          width: 24,
          height: 24,
          borderRadius: 999,
          background: isDark ? neutrals.background : '#FFFFFF',
          boxShadow: isDark ? '0 1px 2px rgba(0,0,0,0.4)' : '0 1px 2px rgba(26,29,35,0.16)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          color: isDark ? neutrals.textSecondary : '#F0883E',
        }}
      >
        {isDark ? <MoonIcon size={14} /> : <SunIcon size={14} />}
      </span>
    </button>
  );
}
