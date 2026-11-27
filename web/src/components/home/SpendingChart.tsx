import { Card } from 'antd';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { SpendingSummary } from '@/types';
import { formatVariation, isGoodVariation } from './variation';

const VIEWBOX_WIDTH = 1000;
const VIEWBOX_HEIGHT = 160;
const VERTICAL_PADDING = 20;

function buildPoints(amounts: number[]): string {
  if (amounts.length === 0) {
    return '';
  }
  const max = Math.max(...amounts);
  const min = Math.min(...amounts);
  const span = max - min || 1;
  const usableHeight = VIEWBOX_HEIGHT - VERTICAL_PADDING * 2;

  return amounts
    .map((amount, index) => {
      const x = amounts.length === 1 ? 0 : (index * VIEWBOX_WIDTH) / (amounts.length - 1);
      const y = VERTICAL_PADDING + usableHeight - ((amount - min) / span) * usableHeight;
      return `${x},${y}`;
    })
    .join(' ');
}

export function SpendingChart({ summary }: { summary: SpendingSummary }) {
  const { accent, neutrals, resolvedMode, status } = useAppTheme();
  const amounts = summary.points.map((point) => point.amount);
  const line = buildPoints(amounts);
  const area = line ? `${line} ${VIEWBOX_WIDTH},${VIEWBOX_HEIGHT} 0,${VIEWBOX_HEIGHT}` : '';
  const strokeColor = resolvedMode === 'dark' ? accent.light : accent.base;
  const fillColor = resolvedMode === 'dark' ? accent.tintDark : accent.tint;
  const variation = summary.variationPercent;

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 24, display: 'flex', flexDirection: 'column', gap: 16 } }}
    >
      <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between' }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 18, fontWeight: 600, color: neutrals.textPrimary }}>
          Depenses courses
        </span>
        <span style={{ fontSize: 13, color: neutrals.textSecondary }}>
          {summary.monthTotal} {summary.currency} ce mois -{' '}
          <span style={{ color: isGoodVariation(variation) === null ? neutrals.textSecondary : isGoodVariation(variation) ? status.success : status.error, fontWeight: 600 }}>
            {formatVariation(variation)}
          </span>
        </span>
      </div>

      <svg
        width="100%"
        height={VIEWBOX_HEIGHT}
        viewBox={`0 0 ${VIEWBOX_WIDTH} ${VIEWBOX_HEIGHT}`}
        preserveAspectRatio="none"
        role="img"
        aria-label="Historique des depenses de courses"
      >
        <polygon points={area} fill={fillColor} opacity={resolvedMode === 'dark' ? 0.9 : 0.6} />
        <polyline points={line} fill="none" stroke={strokeColor} strokeWidth={3} />
      </svg>

      <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 12, color: neutrals.textSecondary }}>
        {summary.points.map((point) => (
          <span key={point.label}>{point.label}</span>
        ))}
      </div>
    </Card>
  );
}
