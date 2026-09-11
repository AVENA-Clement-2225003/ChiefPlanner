import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { App as AntdApp, ConfigProvider } from 'antd';
import frFR from 'antd/locale/fr_FR';
import { buildAntdTheme } from './antdTheme';
import {
  ACCENTS,
  DEFAULT_ACCENT,
  DEFAULT_MODE,
  NEUTRALS,
  STATUS,
  type AccentKey,
  type AccentPalette,
  type NeutralPalette,
  type ResolvedMode,
  type ThemeMode,
} from './tokens';

interface ThemeContextValue {
  mode: ThemeMode;
  resolvedMode: ResolvedMode;
  accentKey: AccentKey;
  accent: AccentPalette;
  neutrals: NeutralPalette;
  status: (typeof STATUS)[ResolvedMode];
  setMode: (mode: ThemeMode) => void;
  setAccentKey: (accent: AccentKey) => void;
}

const STORAGE_KEY_MODE = 'chiefplanner.theme.mode';
const STORAGE_KEY_ACCENT = 'chiefplanner.theme.accent';

const ThemeContext = createContext<ThemeContextValue | null>(null);

function readStored<T extends string>(key: string, allowed: readonly T[], fallback: T): T {
  const stored = localStorage.getItem(key);
  return stored && (allowed as readonly string[]).includes(stored) ? (stored as T) : fallback;
}

function getSystemMode(): ResolvedMode {
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function ThemeProvider({ children }: { children: ReactNode }) {
  const [mode, setModeState] = useState<ThemeMode>(() =>
    readStored(STORAGE_KEY_MODE, ['system', 'light', 'dark'] as const, DEFAULT_MODE),
  );
  const [accentKey, setAccentKeyState] = useState<AccentKey>(() =>
    readStored(STORAGE_KEY_ACCENT, ['violet', 'red', 'orange', 'blue', 'turquoise'] as const, DEFAULT_ACCENT),
  );
  const [systemMode, setSystemMode] = useState<ResolvedMode>(getSystemMode);

  useEffect(() => {
    const query = window.matchMedia('(prefers-color-scheme: dark)');
    const listener = (event: MediaQueryListEvent) => setSystemMode(event.matches ? 'dark' : 'light');
    query.addEventListener('change', listener);
    return () => query.removeEventListener('change', listener);
  }, []);

  const setMode = useCallback((next: ThemeMode) => {
    setModeState(next);
    localStorage.setItem(STORAGE_KEY_MODE, next);
  }, []);

  const setAccentKey = useCallback((next: AccentKey) => {
    setAccentKeyState(next);
    localStorage.setItem(STORAGE_KEY_ACCENT, next);
  }, []);

  const resolvedMode: ResolvedMode = mode === 'system' ? systemMode : mode;
  const accent = ACCENTS[accentKey];
  const neutrals = NEUTRALS[resolvedMode];

  useEffect(() => {
    document.documentElement.style.colorScheme = resolvedMode;
    document.body.style.margin = '0';
    document.body.style.background = neutrals.background;
  }, [resolvedMode, neutrals.background]);

  const value = useMemo<ThemeContextValue>(
    () => ({
      mode,
      resolvedMode,
      accentKey,
      accent,
      neutrals,
      status: STATUS[resolvedMode],
      setMode,
      setAccentKey,
    }),
    [mode, resolvedMode, accentKey, accent, neutrals, setMode, setAccentKey],
  );

  return (
    <ThemeContext.Provider value={value}>
      <ConfigProvider locale={frFR} theme={buildAntdTheme(resolvedMode, accent)}>
        <AntdApp>{children}</AntdApp>
      </ConfigProvider>
    </ThemeContext.Provider>
  );
}

export function useAppTheme(): ThemeContextValue {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useAppTheme must be used inside ThemeProvider');
  }
  return context;
}
