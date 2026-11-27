import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Modal } from 'antd';
import { CloseIcon } from '@/components/icons';
import { ROUTES } from '@/routes';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { CurrentUser } from '@/types';
import { AccountModalNav, type AccountSectionKey } from './AccountModalNav';
import { DataSection } from './DataSection';
import { PlanningSection } from './PlanningSection';
import { PreferencesSection } from './PreferencesSection';

interface AccountModalProps {
  open: boolean;
  user: CurrentUser | null;
  onClose: () => void;
}

function SectionTitle({ children }: { children: string }) {
  const { neutrals } = useAppTheme();
  return (
    <span style={{ fontFamily: FONTS.heading, fontSize: 22, fontWeight: 600, color: neutrals.textPrimary }}>
      {children}
    </span>
  );
}

export function AccountModal({ open, user, onClose }: AccountModalProps) {
  const [activeSection, setActiveSection] = useState<AccountSectionKey>('planning');
  const { neutrals } = useAppTheme();

  const renderSection = () => {
    switch (activeSection) {
      case 'planning':
        return <PlanningSection />;
      case 'preferences':
        return <PreferencesSection />;
      case 'data':
        return <DataSection />;
      case 'administration':
        return (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            <SectionTitle>Administration</SectionTitle>
            <Link to={ROUTES.admin} onClick={onClose}>
              Ouvrir le panneau d&apos;administration
            </Link>
          </div>
        );
      case 'legal':
        return (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            <SectionTitle>Légal</SectionTitle>
            <span style={{ fontSize: 14, color: neutrals.textSecondary }}>
              ChiefPlanner ne conserve que ce dont l&apos;application a besoin pour fonctionner. Vous pouvez effacer
              vos données depuis la section « Mes données ».
            </span>
          </div>
        );
    }
  };

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
      <div style={{ display: 'flex', height: 'min(680px, 90vh)', position: 'relative' }}>
        <div style={{ flex: 1, minWidth: 0, padding: '28px 32px', overflowY: 'auto', scrollbarWidth: 'thin' }}>
          {renderSection()}
        </div>

        <AccountModalNav user={user} activeSection={activeSection} onSelect={setActiveSection} />

        {/* Top-right corner of the whole window, above the navigation column. */}
        <button
          type="button"
          aria-label="Fermer"
          onClick={onClose}
          style={{
            position: 'absolute',
            top: 14,
            right: 14,
            zIndex: 1,
            border: 'none',
            background: 'transparent',
            color: neutrals.textSecondary,
            cursor: 'pointer',
            padding: 6,
            lineHeight: 0,
          }}
        >
          <CloseIcon size={16} />
        </button>
      </div>
    </Modal>
  );
}
