import type { ThemeConfig } from 'antd';
import { theme as antdTheme } from 'antd';
import { FONTS, NEUTRALS, RADII, STATUS, type AccentPalette, type ResolvedMode } from './tokens';

export function buildAntdTheme(mode: ResolvedMode, accent: AccentPalette): ThemeConfig {
  const neutrals = NEUTRALS[mode];
  const status = STATUS[mode];

  return {
    algorithm: mode === 'dark' ? antdTheme.darkAlgorithm : antdTheme.defaultAlgorithm,
    token: {
      colorPrimary: accent.base,
      colorPrimaryHover: accent.hover,
      colorInfo: accent.base,
      colorSuccess: status.success,
      colorError: status.error,
      colorBgLayout: neutrals.background,
      colorBgContainer: neutrals.surface,
      colorBgElevated: neutrals.surface,
      colorBorder: neutrals.border,
      colorBorderSecondary: neutrals.border,
      colorText: neutrals.textPrimary,
      colorTextSecondary: neutrals.textSecondary,
      colorTextTertiary: neutrals.textSecondary,
      colorTextPlaceholder: neutrals.textDisabled,
      fontFamily: FONTS.body,
      fontSize: 14,
      borderRadius: RADII.control,
      borderRadiusLG: RADII.card,
      controlHeight: 40,
    },
    components: {
      Layout: {
        headerBg: neutrals.surface,
        headerHeight: 72,
        headerPadding: '0 40px',
        bodyBg: neutrals.background,
      },
      Card: {
        borderRadiusLG: RADII.card,
        colorBorderSecondary: neutrals.border,
      },
      Button: {
        primaryShadow: 'none',
        defaultShadow: 'none',
        fontWeight: 600,
      },
      Modal: {
        borderRadiusLG: 20,
      },
      Segmented: {
        itemSelectedBg: accent.base,
        itemSelectedColor: '#FFFFFF',
      },
      Progress: {
        defaultColor: accent.base,
      },
      Tag: {
        borderRadiusSM: RADII.pill,
      },
    },
  };
}
