import { MonitorIcon, MoonIcon, SunIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { ACCENTS, ACCENT_LABELS, ACCENT_ORDER, FONTS, type ThemeMode } from '@/theme/tokens';

const MODE_OPTIONS: { mode: ThemeMode; label: string; Icon: typeof SunIcon }[] = [
  { mode: 'system', label: 'Systeme', Icon: MonitorIcon },
  { mode: 'light', label: 'Clair', Icon: SunIcon },
  { mode: 'dark', label: 'Sombre', Icon: MoonIcon },
];

function SectionLabel({ children }: { children: string }) {
  const { neutrals } = useAppTheme();
  return (
    <span
      style={{
        fontSize: 13,
        fontWeight: 600,
        letterSpacing: '0.04em',
        textTransform: 'uppercase',
        color: neutrals.textSecondary,
      }}
    >
      {children}
    </span>
  );
}

export function PreferencesSection() {
  const { mode, setMode, accentKey, setAccentKey, neutrals, accent, resolvedMode } = useAppTheme();

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 32 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 22, fontWeight: 600, color: neutrals.textPrimary }}>
        Preferences
      </span>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
        <SectionLabel>Apparence</SectionLabel>
        <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap' }}>
          {MODE_OPTIONS.map((option) => {
            const selected = mode === option.mode;
            const iconColor = selected
              ? resolvedMode === 'dark'
                ? accent.light
                : accent.base
              : neutrals.textSecondary;
            return (
              <button
                key={option.mode}
                type="button"
                onClick={() => setMode(option.mode)}
                style={{
                  flex: '1 1 0',
                  minWidth: 150,
                  maxWidth: 190,
                  padding: 16,
                  borderRadius: 12,
                  cursor: 'pointer',
                  border: selected ? `2px solid ${accent.base}` : `1px solid ${neutrals.border}`,
                  background: selected
                    ? resolvedMode === 'dark'
                      ? accent.tintDark
                      : accent.tint
                    : 'transparent',
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  gap: 10,
                }}
              >
                <option.Icon size={24} style={{ color: iconColor }} />
                <span
                  style={{
                    fontFamily: FONTS.body,
                    fontSize: 13,
                    fontWeight: 600,
                    color: selected ? neutrals.textPrimary : neutrals.textSecondary,
                  }}
                >
                  {option.label}
                </span>
              </button>
            );
          })}
        </div>
      </div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
        <SectionLabel>Couleur d&apos;accent</SectionLabel>
        <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
          {ACCENT_ORDER.map((key) => {
            const selected = key === accentKey;
            const size = selected ? 40 : 34;
            return (
              <button
                key={key}
                type="button"
                aria-label={ACCENT_LABELS[key]}
                title={ACCENT_LABELS[key]}
                onClick={() => setAccentKey(key)}
                style={{
                  width: size,
                  height: size,
                  borderRadius: 999,
                  border: 'none',
                  cursor: 'pointer',
                  background: ACCENTS[key].base,
                  boxShadow: selected
                    ? `0 0 0 2px ${neutrals.surface}, 0 0 0 4px ${ACCENTS[key].base}`
                    : 'none',
                }}
              />
            );
          })}
        </div>
      </div>
    </div>
  );
}
