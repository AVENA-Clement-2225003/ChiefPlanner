import axios from 'axios';

const DEFAULT_LATENCY_MS = 180;

/**
 * Stands in for the HTTP layer for the features not wired to the Laravel API yet: every service
 * call goes through here so swapping the mock stores for `http` stays local.
 */
export async function respondWith<T>(payload: T, latencyMs: number = DEFAULT_LATENCY_MS): Promise<T> {
  await new Promise((resolve) => {
    setTimeout(resolve, latencyMs);
  });
  return structuredClone(payload);
}

export class NotFoundError extends Error {
  constructor(resource: string, id: string) {
    super(`${resource} ${id} introuvable`);
    this.name = 'NotFoundError';
  }
}

/** Real HTTP client: same-origin `/api`, session cookie, CSRF token echoed from the XSRF-TOKEN cookie (ADR 0001). */
export const http = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: { Accept: 'application/json' },
});

export function apiErrorMessage(error: unknown, fallback: string): string {
  if (axios.isAxiosError(error)) {
    return (error.response?.data as { message?: string } | undefined)?.message ?? fallback;
  }
  return fallback;
}

export function isUnauthorized(error: unknown): boolean {
  return axios.isAxiosError(error) && error.response?.status === 401;
}
