import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { isUnauthorized } from '@/services/apiClient';
import { getCurrentUser, logout as endSession } from '@/services/userService';
import type { CurrentUser } from '@/types';

type AuthState =
  | { status: 'loading' }
  | { status: 'anonymous' }
  | { status: 'error'; message: string }
  | { status: 'authenticated'; user: CurrentUser };

interface AuthContextValue {
  state: AuthState;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<AuthState>({ status: 'loading' });

  useEffect(() => {
    getCurrentUser()
      .then((user) => setState({ status: 'authenticated', user }))
      .catch((error: unknown) =>
        setState(isUnauthorized(error) ? { status: 'anonymous' } : { status: 'error', message: 'Le serveur ne répond pas.' }),
      );
  }, []);

  const logout = useCallback(async () => {
    const endSessionUrl = await endSession();
    setState({ status: 'anonymous' });
    if (endSessionUrl) {
      window.location.assign(endSessionUrl);
    }
  }, []);

  const value = useMemo(() => ({ state, logout }), [state, logout]);
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth doit être utilisé dans un AuthProvider');
  }
  return context;
}

/** The signed-in user. Only call it below the auth gate, where a user is guaranteed. */
export function useCurrentUser(): CurrentUser {
  const { state } = useAuth();
  if (state.status !== 'authenticated') {
    throw new Error('Aucun utilisateur connecté');
  }
  return state.user;
}
