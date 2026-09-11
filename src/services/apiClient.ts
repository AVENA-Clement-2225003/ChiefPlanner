const DEFAULT_LATENCY_MS = 180;

/**
 * Stands in for the HTTP layer until the Laravel API is wired up: every service
 * call goes through here so swapping the mock stores for `fetch` stays local.
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
