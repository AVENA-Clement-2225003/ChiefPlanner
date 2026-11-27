import { useId } from 'react';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';

// The mark: a chef's toque whose band carries seven dots, one per day of the week.
// The same geometry is drawn by `scripts/generate-icons.mjs` for the app icons: change both together.
const TOQUE_BUMPS = [
  { cx: 22, cy: 27, r: 10.5 },
  { cx: 32, cy: 21, r: 12.5 },
  { cx: 42, cy: 27, r: 10.5 },
];
const BAND = { x: 18, y: 33, width: 28, height: 19, radius: 4 };
const DAY_DOTS = Array.from({ length: 7 }, (_, day) => ({ cx: 21 + (day * 22) / 6, cy: 43, r: 1.5 }));

interface LogoMarkProps {
  size?: number;
  color: string;
  /** Colour of the day dots: they are cut out of the toque, so this is the background it sits on. */
  cutout?: string;
}

export function LogoMark({ size = 32, color, cutout }: LogoMarkProps) {
  const maskId = useId();

  return (
    <svg width={size} height={size} viewBox="8 6 48 48" role="img" aria-label="Chief Planner">
      <mask id={maskId}>
        <rect x="0" y="0" width="64" height="64" fill="black" />
        {TOQUE_BUMPS.map((bump) => (
          <circle key={bump.cx} cx={bump.cx} cy={bump.cy} r={bump.r} fill="white" />
        ))}
        <rect x={BAND.x} y={BAND.y} width={BAND.width} height={BAND.height} rx={BAND.radius} fill="white" />
        {!cutout && DAY_DOTS.map((dot) => <circle key={dot.cx} {...dot} fill="black" />)}
      </mask>
      <rect x="0" y="0" width="64" height="64" fill={color} mask={`url(#${maskId})`} />
      {cutout && DAY_DOTS.map((dot) => <circle key={dot.cx} {...dot} fill={cutout} />)}
    </svg>
  );
}

/** The mark followed by the name, in the accent of the current theme. */
export function Logo({ size = 30 }: { size?: number }) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const markColor = resolvedMode === 'dark' ? accent.light : accent.base;

  return (
    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 10 }}>
      <LogoMark size={size} color={markColor} />
      <span
        style={{
          fontFamily: FONTS.heading,
          fontSize: size * 0.66,
          fontWeight: 700,
          letterSpacing: '-0.01em',
          color: neutrals.textPrimary,
        }}
      >
        Chief Planner
      </span>
    </span>
  );
}
