import type { ComponentType, SVGProps } from 'react';
import { CalendarIcon, DatabaseIcon, DocumentIcon, PaletteIcon, ShieldIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { CurrentUser } from '@/types';

export type AccountSectionKey = 'planning' | 'preferences' | 'data' | 'administration' | 'legal';

interface SectionEntry {
  key: AccountSectionKey;
  label: string;
  Icon: ComponentType<SVGProps<SVGSVGElement> & { size?: number }>;
  adminOnly?: boolean;
  separatorBefore?: boolean;
}

const SECTIONS: SectionEntry[] = [
  { key: 'planning', label: 'Planning', Icon: CalendarIcon },
  { key: 'preferences', label: 'Apparence', Icon: PaletteIcon },
  { key: 'data', label: 'Mes données', Icon: DatabaseIcon },
  { key: 'administration', label: 'Administration', Icon: ShieldIcon, adminOnly: true, separatorBefore: true },
  { key: 'legal', label: 'Légal', Icon: DocumentIcon },
];

interface AccountModalNavProps {
  user: CurrentUser | null;
  activeSection: AccountSectionKey;
  onSelect: (section: AccountSectionKey) => void;
}

export function AccountModalNav({ user, activeSection, onSelect }: AccountModalNavProps) {
  const { neutrals, accent, resolvedMode } = useAppTheme();
  const accentText = resolvedMode === 'dark' ? accent.light : accent.base;
  const accentSurface = resolvedMode === 'dark' ? accent.tintDark : accent.tint;

  return (
    <div
      style={{
        width: 240,
        flexShrink: 0,
        borderLeft: `1px solid ${neutrals.border}`,
        padding: '24px 16px',
        display: 'flex',
        flexDirection: 'column',
        gap: 4,
      }}
    >
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 12px', marginBottom: 8 }}>
        <span
          style={{
            width: 32,
            height: 32,
            borderRadius: 999,
            background: accentSurface,
            color: accentText,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 12,
            fontWeight: 700,
          }}
        >
          {user?.initials ?? '--'}
        </span>
        <span style={{ fontSize: 13, fontWeight: 600, color: neutrals.textPrimary }}>
          {user?.displayName ?? 'Mon compte'}
        </span>
      </div>

      {SECTIONS.filter((section) => !section.adminOnly || user?.isAdmin).map((section) => {
        const active = section.key === activeSection;
        return (
          <div key={section.key}>
            {section.separatorBefore && (
              <div style={{ height: 1, background: neutrals.border, margin: '8px 0' }} />
            )}
            <button
              type="button"
              onClick={() => onSelect(section.key)}
              style={{
                width: '100%',
                display: 'flex',
                alignItems: 'center',
                gap: 10,
                padding: '10px 12px',
                borderRadius: 8,
                border: 'none',
                cursor: 'pointer',
                background: active ? accentSurface : 'transparent',
                color: active ? accentText : neutrals.textPrimary,
                fontFamily: FONTS.body,
                fontSize: 14,
                fontWeight: active ? 600 : 500,
                textAlign: 'left',
              }}
            >
              <section.Icon size={16} style={{ color: active ? accentText : neutrals.textSecondary }} />
              {section.label}
            </button>
          </div>
        );
      })}
    </div>
  );
}
