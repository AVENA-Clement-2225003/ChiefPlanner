import { useState } from 'react';
import { Modal } from 'antd';
import { CloseIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { CurrentUser } from '@/types';
import { AccountModalNav, type AccountSectionKey } from './AccountModalNav';
import { PreferencesSection } from './PreferencesSection';

const UNDESIGNED_SECTIONS: Record<Exclude<AccountSectionKey, 'preferences'>, string> = {
  notifications: 'Notifications',
  data: 'Donnees',
  administration: 'Administration',
  legal: 'Legal',
};

interface AccountModalProps {
  open: boolean;
  user: CurrentUser | null;
  onClose: () => void;
}

export function AccountModal({ open, user, onClose }: AccountModalProps) {
  const [activeSection, setActiveSection] = useState<AccountSectionKey>('preferences');
  const { neutrals } = useAppTheme();

  return (
    <Modal
      open={open}
      onCancel={onClose}
      footer={null}
      closable={false}
      centered
      width={920}
      styles={{
        container: { padding: 0, borderRadius: 20, overflow: 'hidden', background: neutrals.surface },
        body: { padding: 0 },
      }}
    >
      <div style={{ display: 'flex', height: 600, position: 'relative' }}>
        <div style={{ flex: 1, padding: 36, overflowY: 'auto' }}>
          {activeSection === 'preferences' ? (
            <PreferencesSection />
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
              <span
                style={{ fontFamily: FONTS.heading, fontSize: 22, fontWeight: 600, color: neutrals.textPrimary }}
              >
                {UNDESIGNED_SECTIONS[activeSection]}
              </span>
              <span style={{ fontSize: 14, color: neutrals.textSecondary }}>
                Section a concevoir : aucune maquette n&apos;existe encore pour cet ecran.
              </span>
            </div>
          )}
        </div>

        <AccountModalNav user={user} activeSection={activeSection} onSelect={setActiveSection} />

        <button
          type="button"
          aria-label="Fermer"
          onClick={onClose}
          style={{
            position: 'absolute',
            top: 24,
            right: 264,
            border: 'none',
            background: 'transparent',
            color: neutrals.textSecondary,
            cursor: 'pointer',
            padding: 0,
            lineHeight: 0,
          }}
        >
          <CloseIcon size={16} />
        </button>
      </div>
    </Modal>
  );
}
