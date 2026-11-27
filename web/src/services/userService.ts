import currentUserMock from '@/mocks/currentUser.json';
import type { CurrentUser } from '@/types';
import { http, respondWith } from './apiClient';

const USE_MOCK_AUTH = import.meta.env.VITE_MOCK_AUTH === 'true';

export const LOGIN_URL = '/api/auth/login';

interface MeResponse {
  id: number;
  name: string;
  email: string;
  isAdmin: boolean;
}

export function initialsOf(name: string): string {
  const letters = name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]);
  return letters.join('').toUpperCase() || '--';
}

export async function getCurrentUser(): Promise<CurrentUser> {
  if (USE_MOCK_AUTH) {
    return respondWith(currentUserMock);
  }
  const { data } = await http.get<MeResponse>('/me');
  return { id: String(data.id), displayName: data.name, initials: initialsOf(data.name), isAdmin: data.isAdmin };
}

/** Ends the local session, then returns where to send the browser to end the Authentik session too. */
export async function logout(): Promise<string | null> {
  if (USE_MOCK_AUTH) {
    return respondWith(null);
  }
  const { data } = await http.post<{ endSessionUrl: string | null }>('/auth/logout');
  return data.endSessionUrl;
}
