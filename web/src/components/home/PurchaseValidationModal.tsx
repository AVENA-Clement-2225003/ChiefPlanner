import { useState } from 'react';
import { InputNumber, Modal } from 'antd';

interface PurchaseValidationModalProps {
  open: boolean;
  currency: string;
  onCancel: () => void;
  onConfirm: (amount: number) => void;
}

/** Replaces the `prompt("Combien ont coute les courses ?")` of the current app. */
export function PurchaseValidationModal({ open, currency, onCancel, onConfirm }: PurchaseValidationModalProps) {
  const [amount, setAmount] = useState<number | null>(null);

  return (
    <Modal
      open={open}
      title="Valider les courses"
      okText="Enregistrer"
      cancelText="Annuler"
      okButtonProps={{ disabled: amount === null || amount <= 0 }}
      onCancel={onCancel}
      onOk={() => {
        if (amount !== null && amount > 0) {
          onConfirm(amount);
          setAmount(null);
        }
      }}
      width={420}
    >
      <p>Combien ont coute les courses ?</p>
      <InputNumber
        autoFocus
        value={amount}
        min={0}
        step={0.5}
        suffix={currency}
        style={{ width: '100%' }}
        onChange={setAmount}
      />
    </Modal>
  );
}
