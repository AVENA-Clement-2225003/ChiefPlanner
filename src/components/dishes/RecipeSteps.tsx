import { Button, Input } from 'antd';
import { CloseIcon, PlusIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { RecipeStep } from '@/types';

interface RecipeStepsProps {
  steps: RecipeStep[];
  onChange: (steps: RecipeStep[]) => void;
}

export function RecipeSteps({ steps, onChange }: RecipeStepsProps) {
  const { accent, neutrals } = useAppTheme();

  const renumber = (list: RecipeStep[]): RecipeStep[] =>
    list.map((step, index) => ({ ...step, position: index + 1 }));

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 17, fontWeight: 600, color: neutrals.textPrimary }}>
        Etapes de la recette
      </span>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
        {steps.map((step) => (
          <div key={step.id} style={{ display: 'flex', gap: 12, alignItems: 'flex-start' }}>
            <span
              style={{
                width: 26,
                height: 26,
                flexShrink: 0,
                marginTop: 6,
                borderRadius: 999,
                background: accent.base,
                color: '#FFFFFF',
                fontSize: 12,
                fontWeight: 700,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
              }}
            >
              {step.position}
            </span>
            <Input.TextArea
              value={step.instruction}
              autoSize={{ minRows: 1, maxRows: 4 }}
              placeholder="Decrivez l'etape"
              onChange={(event) =>
                onChange(
                  steps.map((item) =>
                    item.id === step.id ? { ...item, instruction: event.target.value } : item,
                  ),
                )
              }
              style={{ flex: 1 }}
            />
            <Button
              type="text"
              aria-label={`Retirer l'etape ${step.position}`}
              icon={<CloseIcon size={16} />}
              onClick={() => onChange(renumber(steps.filter((item) => item.id !== step.id)))}
            />
          </div>
        ))}
      </div>

      <Button
        type="link"
        icon={<PlusIcon size={14} />}
        style={{ alignSelf: 'flex-start', padding: 0 }}
        onClick={() =>
          onChange(
            renumber([...steps, { id: `step-${Date.now()}`, position: steps.length + 1, instruction: '' }]),
          )
        }
      >
        Ajouter une etape
      </Button>
    </div>
  );
}
