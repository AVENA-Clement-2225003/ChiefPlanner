import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { App } from '@/App';
import { AuthGate } from '@/auth/AuthGate';
import { AuthProvider } from '@/auth/AuthProvider';
import { ThemeProvider } from '@/theme/ThemeProvider';

const container = document.getElementById('root');
if (!container) {
  throw new Error('Root container introuvable');
}

createRoot(container).render(
  <StrictMode>
    <BrowserRouter>
      <ThemeProvider>
        <AuthProvider>
          <AuthGate>
            <App />
          </AuthGate>
        </AuthProvider>
      </ThemeProvider>
    </BrowserRouter>
  </StrictMode>,
);
