import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { MealFlow } from '@/types';

interface MealFlowSelectorProps {
  mealFlows: MealFlow[];
  selectedIds: string[];
  onToggle: (mealFlowId: string) => void;
  onCreate: () => void;
}

/** A dish can belong to several mealflows: the chips toggle that membership. */
export function MealFlowSelector({ mealFlows, selectedIds, onToggle, onCreate }: MealFlowSelectorProps) {
  const { accent, neutrals } = useAppTheme();

  const chipBase = {
    padding: '8px 16px',
    borderRadius: 999,
    fontFamily: FONTS.body,
    fontSize: 13,
    fontWeight: 600,
    cursor: 'pointer',
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 17, fontWeight: 600, color: neutrals.textPrimary }}>
        MealFlows
      </span>

      <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
        {mealFlows.map((flow) => {
          const selected = selectedIds.includes(flow.id);
          return (
            <button
              key={flow.id}
              type="button"
              aria-pressed={selected}
              onClick={() => onToggle(flow.id)}
              style={{
                ...chipBase,
                background: selected ? accent.base : 'transparent',
                border: selected ? `1px solid ${accent.base}` : `1px solid ${neutrals.border}`,
                color: selected ? '#FFFFFF' : neutrals.textSecondary,
              }}
            >
              {flow.name}
            </button>
          );
        })}

        <button
          type="button"
          onClick={onCreate}
          style={{
            ...chipBase,
            background: 'transparent',
            border: `1px dashed ${neutrals.border}`,
            color: neutrals.textSecondary,
          }}
        >
          + Nouveau mealflow
        </button>
      </div>
    </div>
  );
}
