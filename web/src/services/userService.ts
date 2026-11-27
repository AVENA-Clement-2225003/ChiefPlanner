import type { CurrentUser } from '@/types';
import { http } from './apiClient';

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
  const { data } = await http.get<MeResponse>('/me');
  return { id: String(data.id), displayName: data.name, initials: initialsOf(data.name), isAdmin: data.isAdmin };
}

const OFFLINE_CACHE = 'api-read';

/** The offline copy holds the user's own data: it must not outlive their session on a shared device. */
async function forgetOfflineData(): Promise<void> {
  if ('caches' in window) {
    await caches.delete(OFFLINE_CACHE);
  }
}

/** Ends the local session, then returns where to send the browser to end the Authentik session too. */
export async function logout(): Promise<string | null> {
  const { data } = await http.post<{ endSessionUrl: string | null }>('/auth/logout');
  await forgetOfflineData();
  return data.endSessionUrl;
}

/** "Delete my account": erases the user's data (RGPD) and ends the session. */
export async function deleteAccount(): Promise<void> {
  await http.delete('/me');
  await forgetOfflineData();
}
