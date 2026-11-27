import { Card } from 'antd';
import { PlusIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { RADII } from '@/theme/tokens';
import type { MealFlow } from '@/types';

interface MealFlowSidebarProps {
  mealFlows: MealFlow[];
  selectedMealFlowId: string | null;
  onSelect: (mealFlow: MealFlow) => void;
  onCreate: () => void;
}

export function MealFlowSidebar({
  mealFlows,
  selectedMealFlowId,
  onSelect,
  onCreate,
}: MealFlowSidebarProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const accentSurface = resolvedMode === 'dark' ? accent.tintDark : accent.tint;

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 12, display: 'flex', flexDirection: 'column', gap: 6 } }}
    >
      {mealFlows.map((mealFlow) => {
        const selected = mealFlow.id === selectedMealFlowId;
        return (
          <button
            key={mealFlow.id}
            type="button"
            onClick={() => onSelect(mealFlow)}
            style={{
              display: 'flex',
              flexDirection: 'column',
              gap: 2,
              padding: 12,
              borderRadius: RADII.inner,
              cursor: 'pointer',
              textAlign: 'left',
              background: selected ? accentSurface : 'transparent',
              border: selected ? `1px solid ${accent.base}` : '1px solid transparent',
            }}
          >
            <span style={{ fontSize: 14, fontWeight: 600, color: neutrals.textPrimary }}>{mealFlow.name}</span>
            <span style={{ fontSize: 12, color: neutrals.textSecondary }}>{mealFlow.dishIds.length} plats</span>
          </button>
        );
      })}

      <button
        type="button"
        onClick={onCreate}
        style={{
          display: 'flex',
          alignItems: 'center',
          gap: 8,
          padding: 12,
          borderRadius: RADII.inner,
          cursor: 'pointer',
          background: 'transparent',
          border: `1px dashed ${neutrals.border}`,
          color: neutrals.textSecondary,
          fontSize: 13,
          fontWeight: 600,
        }}
      >
        <PlusIcon size={14} />
        Nouveau mealflow
      </button>
    </Card>
  );
}
