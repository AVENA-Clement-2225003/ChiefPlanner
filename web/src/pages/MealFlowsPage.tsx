import { useState } from 'react';
import { App as AntdApp, Button, Input, Modal, Spin } from 'antd';
import { DishTransferBoard } from '@/components/mealflows/DishTransferBoard';
import { MealFlowSidebar } from '@/components/mealflows/MealFlowSidebar';
import { PlusIcon } from '@/components/icons';
import { useAsyncData } from '@/hooks/useAsyncData';
import { listDishes } from '@/services/dishService';
import { addDishToMealFlow, createMealFlow, listMealFlows, removeDishFromMealFlow } from '@/services/mealFlowService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';

export function MealFlowsPage() {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();

  const mealFlows = useAsyncData(listMealFlows, []);
  const dishes = useAsyncData(listDishes, []);

  const [selectedMealFlowId, setSelectedMealFlowId] = useState<string | null>(null);
  const [creationOpen, setCreationOpen] = useState(false);
  const [newMealFlowName, setNewMealFlowName] = useState('');

  const flows = mealFlows.data ?? [];
  const selectedMealFlow = flows.find((flow) => flow.id === selectedMealFlowId) ?? flows[0] ?? null;

  const usableDishes = (dishes.data ?? []).filter((dish) => dish.ownedByCurrentUser || dish.subscribed);
  const memberDishes = usableDishes.filter((dish) => selectedMealFlow?.dishIds.includes(dish.id));
  const availableDishes = usableDishes.filter((dish) => !selectedMealFlow?.dishIds.includes(dish.id));

  const reload = () => {
    mealFlows.reload();
    dishes.reload();
  };

  const handleAdd = async (dishId: string) => {
    if (!selectedMealFlow) {
      return;
    }
    await addDishToMealFlow(selectedMealFlow.id, dishId);
    reload();
  };

  const handleRemove = async (dishId: string) => {
    if (!selectedMealFlow) {
      return;
    }
    await removeDishFromMealFlow(selectedMealFlow.id, dishId);
    reload();
  };

  const handleCreate = async () => {
    const name = newMealFlowName.trim();
    if (!name) {
      return;
    }
    const created = await createMealFlow(name);
    setNewMealFlowName('');
    setCreationOpen(false);
    setSelectedMealFlowId(created.id);
    reload();
    message.success('MealFlow cree');
  };

  if (!mealFlows.data || !dishes.data) {
    return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 28, fontWeight: 600, color: neutrals.textPrimary }}>
          MealFlows
        </span>
        <Button type="primary" icon={<PlusIcon size={14} />} onClick={() => setCreationOpen(true)}>
          Nouveau mealflow
        </Button>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '280px 1fr', gap: 24, alignItems: 'start' }}>
        <MealFlowSidebar
          mealFlows={flows}
          selectedMealFlowId={selectedMealFlow?.id ?? null}
          onSelect={(mealFlow) => setSelectedMealFlowId(mealFlow.id)}
          onCreate={() => setCreationOpen(true)}
        />

        {selectedMealFlow ? (
          <DishTransferBoard
            mealFlowName={selectedMealFlow.name}
            availableDishes={availableDishes}
            memberDishes={memberDishes}
            onAdd={handleAdd}
            onRemove={handleRemove}
          />
        ) : (
          <span style={{ color: neutrals.textSecondary }}>Creez un premier mealflow pour commencer.</span>
        )}
      </div>

      <Modal
        open={creationOpen}
        title="Nouveau mealflow"
        okText="Creer"
        cancelText="Annuler"
        okButtonProps={{ disabled: newMealFlowName.trim().length === 0 }}
        onCancel={() => setCreationOpen(false)}
        onOk={handleCreate}
        width={420}
      >
        <Input
          autoFocus
          value={newMealFlowName}
          placeholder="Ex : Semaine vegetarienne"
          onChange={(event) => setNewMealFlowName(event.target.value)}
          onPressEnter={handleCreate}
        />
      </Modal>
    </div>
  );
}
