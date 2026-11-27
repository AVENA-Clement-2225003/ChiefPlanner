import { useEffect, useState } from 'react';
import { Outlet } from 'react-router-dom';
import { Alert, App as AntdApp, Layout, Spin } from 'antd';
import { useAuth, useCurrentUser } from '@/auth/AuthProvider';
import { AccountModal } from '@/components/layout/AccountModal/AccountModal';
import { NavBar } from '@/components/layout/NavBar';
import { useAsyncData } from '@/hooks/useAsyncData';
import { useOnlineStatus } from '@/hooks/useOnlineStatus';
import { OFFLINE_MESSAGE, onOfflineWrite } from '@/services/apiClient';
import { loadCatalog } from '@/services/catalogService';
import { useAppTheme } from '@/theme/ThemeProvider';

export function AppLayout() {
  const [accountOpen, setAccountOpen] = useState(false);
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();
  const user = useCurrentUser();
  const { logout } = useAuth();
  const catalog = useAsyncData(loadCatalog, []);
  const online = useOnlineStatus();

  useEffect(() => {
    onOfflineWrite(() => message.warning(OFFLINE_MESSAGE));
    return () => onOfflineWrite(null);
  }, [message]);

  const handleLogout = async () => {
    await logout();
    message.success('Vous êtes déconnecté');
  };

  return (
    <Layout style={{ minHeight: '100vh', background: neutrals.background }}>
      <NavBar user={user} onOpenAccount={() => setAccountOpen(true)} onLogout={handleLogout} />
      {!online && (
        <Alert
          banner
          type="warning"
          message="Vous êtes hors ligne : vous pouvez consulter votre semaine, vos recettes et votre liste de courses, mais pas les modifier."
        />
      )}
      <Layout.Content style={{ padding: 40 }}>
        {catalog.data ? <Outlet /> : <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />}
      </Layout.Content>
      <AccountModal open={accountOpen} user={user} onClose={() => setAccountOpen(false)} />
    </Layout>
  );
}
