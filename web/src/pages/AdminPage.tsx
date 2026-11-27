import { useState } from 'react';
import { App as AntdApp, Button, Table, Tag } from 'antd';
import { useCurrentUser } from '@/auth/AuthProvider';
import { useAsyncData } from '@/hooks/useAsyncData';
import { listUsers, setAdmin } from '@/services/adminService';
import { apiErrorMessage } from '@/services/apiClient';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { AdminUser } from '@/types';

export function AdminPage() {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();
  const currentUser = useCurrentUser();
  const { data: users, loading, setData } = useAsyncData(listUsers, []);
  const [pendingId, setPendingId] = useState<number | null>(null);

  const changeRole = async (target: AdminUser) => {
    setPendingId(target.id);
    try {
      const updated = await setAdmin(target.id, !target.isAdmin);
      setData((users ?? []).map((candidate) => (candidate.id === updated.id ? updated : candidate)));
    } catch (error) {
      message.error(apiErrorMessage(error, "Le rôle n'a pas pu être modifié."));
    } finally {
      setPendingId(null);
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 28, fontWeight: 600, color: neutrals.textPrimary }}>
        Administration
      </span>
      <Table<AdminUser>
        rowKey="id"
        loading={loading}
        dataSource={users ?? []}
        pagination={false}
        columns={[
          { title: 'Nom', dataIndex: 'name' },
          { title: 'Email', dataIndex: 'email' },
          {
            title: 'Rôle',
            dataIndex: 'isAdmin',
            render: (isAdmin: boolean) => (isAdmin ? <Tag color="blue">Administrateur</Tag> : <Tag>Utilisateur</Tag>),
          },
          {
            title: '',
            key: 'action',
            render: (_: unknown, user) => (
              <Button
                loading={pendingId === user.id}
                disabled={user.id === Number(currentUser.id)}
                onClick={() => void changeRole(user)}
              >
                {user.isAdmin ? 'Retirer admin' : 'Promouvoir admin'}
              </Button>
            ),
          },
        ]}
      />
    </div>
  );
}
