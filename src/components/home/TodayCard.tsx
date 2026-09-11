import { Button } from 'antd';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { PlannedDay, PlannedMeal } from '@/types';

function MealChip({ slotLabel, meal }: { slotLabel: string; meal: PlannedMeal | null }) {
  const { neutrals } = useAppTheme();

  return (
    <div
      style={{
        background: neutrals.surface,
        borderRadius: RADII.inner,
        padding: '10px 16px',
        display: 'flex',
        flexDirection: 'column',
        gap: 2,
      }}
    >
      <span
        style={{
          fontSize: 11,
          fontWeight: 600,
          letterSpacing: '0.05em',
          textTransform: 'uppercase',
          color: neutrals.textSecondary,
        }}
      >
        {slotLabel}
      </span>
      <span style={{ fontSize: 14, fontWeight: 600, color: meal ? neutrals.textPrimary : neutrals.textDisabled }}>
        {meal?.dishName ?? 'Aucun plat'}
      </span>
    </div>
  );
}

interface TodayCardProps {
  day: PlannedDay | null;
  onReplace: () => void;
  onOpenRecipe: () => void;
}

export function TodayCard({ day, onReplace, onOpenRecipe }: TodayCardProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();

  if (!day) {
    return null;
  }

  return (
    <div
      style={{
        background: resolvedMode === 'dark' ? accent.tintDark : accent.tint,
        border: `1px solid ${accent.base}`,
        borderRadius: RADII.card,
        padding: 24,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: 24,
      }}
    >
      <div style={{ display: 'flex', flexDirection: 'column', gap: 12, flex: 1 }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 20, fontWeight: 600, color: neutrals.textPrimary }}>
          Aujourd&apos;hui - {day.dayLabel}
        </span>
        <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap' }}>
          <MealChip slotLabel="Dejeuner" meal={day.lunch} />
          <MealChip slotLabel="Diner" meal={day.dinner} />
        </div>
      </div>

      <div style={{ display: 'flex', gap: 10, flexShrink: 0 }}>
        <Button onClick={onReplace}>Remplacer</Button>
        <Button type="primary" onClick={onOpenRecipe}>
          Voir la recette
        </Button>
      </div>
    </div>
  );
}
