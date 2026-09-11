import { useCallback, useEffect, useState } from 'react';

interface AsyncState<T> {
  data: T | null;
  loading: boolean;
  error: Error | null;
  reload: () => void;
  setData: (data: T) => void;
}

/** Minimal request state holder: services return promises, screens need loading/error. */
export function useAsyncData<T>(loader: () => Promise<T>, deps: unknown[] = []): AsyncState<T> {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);
  const [reloadToken, setReloadToken] = useState(0);

  // The loader is rebuilt on every render by callers; `deps` is the real trigger.
  // eslint-disable-next-line react-hooks/exhaustive-deps
  const stableLoader = useCallback(loader, deps);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError(null);

    stableLoader()
      .then((result) => {
        if (!cancelled) {
          setData(result);
        }
      })
      .catch((caught: unknown) => {
        if (!cancelled) {
          setError(caught instanceof Error ? caught : new Error(String(caught)));
        }
      })
      .finally(() => {
        if (!cancelled) {
          setLoading(false);
        }
      });

    return () => {
      cancelled = true;
    };
  }, [stableLoader, reloadToken]);

  const reload = useCallback(() => setReloadToken((token) => token + 1), []);

  return { data, loading, error, reload, setData };
}
