import { useAppTheme } from '@/theme/ThemeProvider';

export function FieldLabel({ children }: { children: string }) {
  const { neutrals } = useAppTheme();

  return (
    <span
      style={{
        fontSize: 12,
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
