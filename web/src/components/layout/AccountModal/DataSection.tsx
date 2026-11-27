import { App as AntdApp, Button, Popconfirm } from 'antd';
import { useAuth } from '@/auth/AuthProvider';
import { apiErrorMessage } from '@/services/apiClient';
import { deleteAccount } from '@/services/userService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';

/** What ChiefPlanner holds about the user, and the way to erase it (RGPD). */
export function DataSection() {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();
  const { logout } = useAuth();

  const handleDelete = async () => {
    try {
      await deleteAccount();
      await logout();
    } catch (error) {
      message.error(apiErrorMessage(error, "Le compte n'a pas pu être supprimé."));
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 22, fontWeight: 600, color: neutrals.textPrimary }}>
        Mes données
      </span>
      <span style={{ fontSize: 14, color: neutrals.textSecondary }}>
        Vos plats, MealFlows, plannings, réglages de magasin, extras et dépenses sont rattachés à votre compte. Vos
        plats partagés disparaissent aussi chez les personnes qui les avaient ajoutés. Votre identité reste gérée par
        Authentik.
      </span>
      <Popconfirm
        title="Supprimer définitivement mon compte ?"
        description="Cette action est irréversible : il n'y a pas de sauvegarde."
        okText="Supprimer"
        okButtonProps={{ danger: true }}
        cancelText="Annuler"
        onConfirm={handleDelete}
      >
        <Button danger style={{ alignSelf: 'flex-start' }}>
          Supprimer mon compte et mes données
        </Button>
      </Popconfirm>
    </div>
  );
}
