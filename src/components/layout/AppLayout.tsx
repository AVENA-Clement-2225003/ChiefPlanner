import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import { App as AntdApp, Layout } from 'antd';
import { AccountModal } from '@/components/layout/AccountModal/AccountModal';
import { NavBar } from '@/components/layout/NavBar';
import { useAsyncData } from '@/hooks/useAsyncData';
import { getCurrentUser, logout } from '@/services/userService';
import { useAppTheme } from '@/theme/ThemeProvider';

export function AppLayout() {
  const [accountOpen, setAccountOpen] = useState(false);
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();
  const { data: user } = useAsyncData(getCurrentUser, []);

  const handleLogout = async () => {
    await logout();
    message.success('Vous etes deconnecte');
  };

  return (
    <Layout style={{ minHeight: '100vh', background: neutrals.background }}>
      <NavBar user={user} onOpenAccount={() => setAccountOpen(true)} onLogout={handleLogout} />
      <Layout.Content style={{ padding: 40 }}>
        <Outlet />
      </Layout.Content>
      <AccountModal open={accountOpen} user={user} onClose={() => setAccountOpen(false)} />
    </Layout>
  );
}
