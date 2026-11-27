import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { VitePWA } from 'vite-plugin-pwa';
import { fileURLToPath, URL } from 'node:url';

/**
 * Offline is read-only (ADR 0004): the pages that show the week, recipes and the shopping list keep their last
 * answer in a cache, served when the network fails. Nothing that writes is ever cached or replayed.
 */
const READ_ONLY_API = /\/api\/(week|shopping-list|dishes|meal-flows|catalog|preferences|store|spendings\/summary|me)(\/|$|\?)/;

export default defineConfig({
  plugins: [
    react(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.png'],
      manifest: {
        name: 'Chief Planner',
        short_name: 'ChiefPlanner',
        description: 'Planifiez vos repas de la semaine et votre liste de courses.',
        lang: 'fr',
        start_url: '/',
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#2A8FE0',
        icons: [
          { src: 'pwa-192.png', sizes: '192x192', type: 'image/png' },
          { src: 'pwa-512.png', sizes: '512x512', type: 'image/png' },
          { src: 'pwa-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
        ],
      },
      workbox: {
        navigateFallback: '/index.html',
        // The sign-in flow is a full-page navigation to the API: it must never be answered by the app shell.
        navigateFallbackDenylist: [/^\/api\//],
        cleanupOutdatedCaches: true,
        runtimeCaching: [
          {
            urlPattern: ({ url, request }) => request.method === 'GET' && READ_ONLY_API.test(url.pathname + url.search),
            handler: 'NetworkFirst',
            options: {
              cacheName: 'api-read',
              networkTimeoutSeconds: 4,
              expiration: { maxEntries: 60, maxAgeSeconds: 60 * 60 * 24 * 14 },
              cacheableResponse: { statuses: [200] },
            },
          },
        ],
      },
    }),
  ],
  publicDir: 'public',
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5173,
    proxy: { '/api': 'http://127.0.0.1:8080' },
  },
  build: {
    outDir: 'dist',
  },
});
