import currentUserMock from '@/mocks/currentUser.json';
import type { CurrentUser } from '@/types';
import { respondWith } from './apiClient';

const currentUser: CurrentUser = structuredClone(currentUserMock);

export function getCurrentUser(): Promise<CurrentUser> {
  return respondWith(currentUser);
}

export function logout(): Promise<void> {
  return respondWith(undefined);
}
