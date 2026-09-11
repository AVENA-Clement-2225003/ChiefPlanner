import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

// The repository also hosts a Laravel app whose `public/` directory must not be
// served as Vite's static asset folder, hence `publicDir: false`.
export default defineConfig({
  plugins: [react()],
  publicDir: false,
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5173,
  },
  build: {
    outDir: 'dist',
  },
});
