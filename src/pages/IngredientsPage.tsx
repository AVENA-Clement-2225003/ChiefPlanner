import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';

/** Kept in the navigation to match the design canvas; the screen is not designed yet. */
export function IngredientsPage() {
  const { neutrals } = useAppTheme();

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 28, fontWeight: 600, color: neutrals.textPrimary }}>
        Ingredients
      </span>
      <span style={{ fontSize: 14, color: neutrals.textSecondary }}>
        Ecran a concevoir : aucune maquette n&apos;existe encore pour cette page.
      </span>
    </div>
  );
}
