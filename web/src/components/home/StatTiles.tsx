import { Card } from 'antd';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { HomeSummary } from '@/types';
import { formatVariation, isGoodVariation } from './variation';

interface StatTileProps {
  label: string;
  value: string;
  caption: string;
  captionColor?: string;
  compactValue?: boolean;
}

function StatTile({ label, value, caption, captionColor, compactValue = false }: StatTileProps) {
  const { neutrals } = useAppTheme();

  return (
    <Card
      style={{ flex: 1, borderRadius: RADII.tile, borderColor: neutrals.border }}
      styles={{ body: { padding: '18px 20px', display: 'flex', flexDirection: 'column', gap: 6 } }}
    >
      <span
        style={{
          fontSize: 12,
          fontWeight: 600,
          letterSpacing: '0.05em',
          textTransform: 'uppercase',
          color: neutrals.textSecondary,
        }}
      >
        {label}
      </span>
      <span
        style={{
          fontFamily: FONTS.heading,
          fontSize: compactValue ? 22 : 28,
          fontWeight: 700,
          color: neutrals.textPrimary,
          lineHeight: 1.2,
        }}
      >
        {value}
      </span>
      <span style={{ fontSize: 12, color: captionColor ?? neutrals.textSecondary }}>{caption}</span>
    </Card>
  );
}

export function StatTiles({ summary }: { summary: HomeSummary }) {
  const { status } = useAppTheme();
  const variation = summary.spending.variationPercent;

  return (
    <div style={{ display: 'flex', gap: 16 }}>
      <StatTile
        label="Repas planifies"
        value={`${summary.plannedMeals.filled} / ${summary.plannedMeals.total}`}
        caption="creneaux remplis cette semaine"
      />
      <StatTile
        label="Budget courses"
        value={`${summary.spending.total} ${summary.spending.currency}`}
        caption={`${formatVariation(variation)} vs mois dernier`}
        captionColor={isGoodVariation(variation) === null ? undefined : isGoodVariation(variation) ? status.success : status.error}
      />
      <StatTile
        label="MealFlow actif"
        value={summary.activeMealFlow?.name ?? 'Aucun'}
        caption={
          summary.activeMealFlow
            ? `${summary.activeMealFlow.dishCount} plats dans ce mealflow`
            : 'aucun mealflow selectionne'
        }
        compactValue
      />
      <StatTile
        label="Temps de preparation"
        value={`${summary.averagePrepTimeMinutes} min`}
        caption="en moyenne cette semaine"
      />
    </div>
  );
}
