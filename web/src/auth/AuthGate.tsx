import type { ReactNode } from 'react';
import { Alert, Button, Spin } from 'antd';
import { useAuth } from '@/auth/AuthProvider';
import { Logo } from '@/components/layout/Logo';
import { LOGIN_URL } from '@/services/userService';
import { useAppTheme } from '@/theme/ThemeProvider';

const AUTH_ERRORS: Record<string, string> = {
  forbidden: "Votre compte n'a pas accès à ChiefPlanner. Demandez à un administrateur de vous ajouter au groupe.",
  denied: 'La connexion a été interrompue. Réessayez.',
  failed: "La connexion n'a pas pu être vérifiée. Réessayez.",
};

/** Renders the app only for a signed-in user; otherwise the sign-in screen. */
export function AuthGate({ children }: { children: ReactNode }) {
  const { state } = useAuth();
  const { neutrals } = useAppTheme();

  if (state.status === 'authenticated') {
    return <>{children}</>;
  }

  const authError = AUTH_ERRORS[new URLSearchParams(window.location.search).get('authError') ?? ''];

  return (
    <div
      style={{
        minHeight: '100vh',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 16,
        background: neutrals.background,
        padding: 24,
      }}
    >
      <Logo size={48} />
      {state.status === 'loading' && <Spin />}
      {state.status === 'error' && <Alert type="error" message={state.message} showIcon />}
      {state.status === 'anonymous' && (
        <>
          {authError && <Alert type="warning" message={authError} showIcon style={{ maxWidth: 420 }} />}
          <Button type="primary" size="large" href={LOGIN_URL}>
            Se connecter
          </Button>
        </>
      )}
    </div>
  );
}
