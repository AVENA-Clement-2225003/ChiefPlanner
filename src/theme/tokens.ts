export type ThemeMode = 'system' | 'light' | 'dark';
export type ResolvedMode = 'light' | 'dark';
export type AccentKey = 'violet' | 'red' | 'orange' | 'blue' | 'turquoise';

export interface AccentPalette {
  /** Base accent, used for primary fills and active states. */
  base: string;
  /** Darker step for hover / pressed states in light mode. */
  hover: string;
  /** Very light wash, readable on a light background. */
  tint: string;
  /** Deep wash, readable on a dark background. */
  tintDark: string;
  /** Lightened accent, used for text and strokes on dark surfaces. */
  light: string;
}

export interface NeutralPalette {
  background: string;
  surface: string;
  surfaceMuted: string;
  border: string;
  textPrimary: string;
  textSecondary: string;
  textDisabled: string;
}

export const ACCENTS: Record<AccentKey, AccentPalette> = {
  violet: { base: '#7C5CFC', hover: '#5A3FD1', tint: '#EDE9FE', tintDark: '#2E2350', light: '#B8A6FC' },
  red: { base: '#E24C4C', hover: '#C93A3A', tint: '#FBE7E7', tintDark: '#3A2020', light: '#F09A9A' },
  orange: { base: '#F0883E', hover: '#D66F26', tint: '#FDECD9', tintDark: '#3A2A18', light: '#F7B884' },
  blue: { base: '#2A8FE0', hover: '#1E72B8', tint: '#E3F1FC', tintDark: '#163247', light: '#86C6F2' },
  turquoise: { base: '#14B8A6', hover: '#0F9488', tint: '#DAF5F1', tintDark: '#10302C', light: '#6FD9CC' },
};

export const ACCENT_LABELS: Record<AccentKey, string> = {
  violet: 'Violet',
  red: 'Rouge',
  orange: 'Orange',
  blue: 'Bleu',
  turquoise: 'Turquoise',
};

export const ACCENT_ORDER: AccentKey[] = ['violet', 'red', 'orange', 'blue', 'turquoise'];

export const NEUTRALS: Record<ResolvedMode, NeutralPalette> = {
  light: {
    background: '#F7F8FA',
    surface: '#FFFFFF',
    surfaceMuted: '#EEF0F3',
    border: '#E2E5EA',
    textPrimary: '#1A1D23',
    textSecondary: '#5B6270',
    textDisabled: '#A6ACB6',
  },
  dark: {
    background: '#14161A',
    surface: '#1E2127',
    surfaceMuted: '#2C2F36',
    border: '#2C2F36',
    textPrimary: '#F2F3F5',
    textSecondary: '#9AA1AC',
    textDisabled: '#565C66',
  },
};

/** Status colours stay fixed: they must not follow the chosen accent. */
export const STATUS = {
  light: { success: '#3F9142', successTint: '#E4F3E6', error: '#D64545', errorTint: '#FBEAEA' },
  dark: { success: '#5FBF71', successTint: '#1C3020', error: '#D64545', errorTint: '#3A2020' },
} as const;

export const FONTS = {
  heading: "'Space Grotesk', system-ui, sans-serif",
  body: "'Public Sans', system-ui, sans-serif",
};

export const RADII = {
  control: 8,
  card: 16,
  tile: 14,
  inner: 10,
  pill: 999,
};

export const DEFAULT_ACCENT: AccentKey = 'blue';
export const DEFAULT_MODE: ThemeMode = 'system';
