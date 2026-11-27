import { NavLink } from 'react-router-dom';
import { Layout } from 'antd';
import { LogoutIcon } from '@/components/icons';
import { ThemeToggle } from '@/components/layout/ThemeToggle';
import { ROUTES } from '@/routes';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { CurrentUser } from '@/types';

const NAV_LINKS = [
  { to: ROUTES.home, label: 'Accueil' },
  { to: ROUTES.dishes, label: 'Plats' },
  { to: ROUTES.store, label: 'Mon magasin' },
  { to: ROUTES.mealFlows, label: 'MealFlows' },
  { to: ROUTES.allDishes, label: 'Tous les plats' },
];

const ADMIN_LINK = { to: ROUTES.admin, label: 'Administration' };

interface NavBarProps {
  user: CurrentUser | null;
  onOpenAccount: () => void;
  onLogout: () => void;
}

export function NavBar({ user, onOpenAccount, onLogout }: NavBarProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const activeColor = resolvedMode === 'dark' ? accent.light : accent.base;
  const avatarBackground = resolvedMode === 'dark' ? accent.tintDark : accent.tint;

  return (
    <Layout.Header
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        borderBottom: `1px solid ${neutrals.border}`,
        background: neutrals.surface,
      }}
    >
      <span style={{ fontFamily: FONTS.heading, fontSize: 20, fontWeight: 700, color: neutrals.textPrimary }}>
        Chief Planner
      </span>

      <nav style={{ display: 'flex', alignItems: 'center', gap: 20 }}>
        {(user?.isAdmin ? [...NAV_LINKS, ADMIN_LINK] : NAV_LINKS).map((link) => (
          <NavLink
            key={link.to}
            to={link.to}
            end={link.to === ROUTES.home}
            style={({ isActive }) => ({
              fontSize: 14,
              fontWeight: isActive ? 600 : 500,
              color: isActive ? activeColor : neutrals.textSecondary,
              textDecoration: 'none',
            })}
          >
            {link.label}
          </NavLink>
        ))}

        <ThemeToggle />

        <button
          type="button"
          aria-label="Se deconnecter"
          onClick={onLogout}
          style={{
            width: 36,
            height: 36,
            borderRadius: 8,
            border: `1px solid ${neutrals.border}`,
            background: 'transparent',
            color: neutrals.textSecondary,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            cursor: 'pointer',
          }}
        >
          <LogoutIcon size={17} />
        </button>

        <button
          type="button"
          aria-label="Ouvrir mon compte"
          onClick={onOpenAccount}
          style={{
            width: 36,
            height: 36,
            borderRadius: 999,
            border: 'none',
            background: avatarBackground,
            color: activeColor,
            fontFamily: FONTS.body,
            fontSize: 13,
            fontWeight: 700,
            cursor: 'pointer',
          }}
        >
          {user?.initials ?? '--'}
        </button>
      </nav>
    </Layout.Header>
  );
}
