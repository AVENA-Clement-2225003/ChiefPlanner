import { Button, Input, InputNumber, Select } from 'antd';
import { CloseIcon, PlusIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { IngredientLine, Unit } from '@/types';

const VALUE_WIDTH = 90;
const UNIT_WIDTH = 130;

interface IngredientListProps {
  ingredients: IngredientLine[];
  units: Unit[];
  /** Number of people the quantities are written for. */
  portions: number;
  readOnly: boolean;
  onChange: (ingredients: IngredientLine[]) => void;
}

export function IngredientList({ ingredients, units, portions, readOnly, onChange }: IngredientListProps) {
  const { neutrals } = useAppTheme();

  const updateLine = (id: string, patch: Partial<IngredientLine>) => {
    onChange(ingredients.map((line) => (line.id === id ? { ...line, ...patch } : line)));
  };

  const columnLabel = {
    fontSize: 11,
    fontWeight: 600,
    letterSpacing: '0.04em',
    textTransform: 'uppercase' as const,
    color: neutrals.textSecondary,
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 17, fontWeight: 600, color: neutrals.textPrimary }}>
        Ingrédients
      </span>
      <span style={{ fontSize: 12, color: neutrals.textSecondary }}>
        Quantités pour {portions} personne{portions > 1 ? "s" : ""} : elles sont ramenées à une personne à l’enregistrement.
      </span>

      <div style={{ display: 'flex', gap: 10 }}>
        <span style={{ ...columnLabel, flex: 1 }}>Ingredient</span>
        <span style={{ ...columnLabel, width: VALUE_WIDTH }}>Valeur</span>
        <span style={{ ...columnLabel, width: UNIT_WIDTH }}>Unite</span>
        <span style={{ width: 32 }} />
      </div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
        {ingredients.map((line) => (
          <div key={line.id} style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
            <Input
              value={line.name}
              disabled={readOnly}
              placeholder="Nom de l'ingredient"
              onChange={(event) => updateLine(line.id, { name: event.target.value })}
              style={{ flex: 1 }}
            />
            <InputNumber
              value={line.value}
              min={0}
              disabled={readOnly}
              onChange={(value) => updateLine(line.id, { value: value ?? 0 })}
              style={{ width: VALUE_WIDTH }}
            />
            <Select
              value={line.unitId}
              disabled={readOnly}
              onChange={(unitId) => updateLine(line.id, { unitId })}
              options={units.map((unit) => ({ value: unit.id, label: unit.label }))}
              style={{ width: UNIT_WIDTH }}
            />
            <Button
              type="text"
              disabled={readOnly}
              aria-label={`Retirer ${line.name}`}
              icon={<CloseIcon size={16} />}
              onClick={() => onChange(ingredients.filter((item) => item.id !== line.id))}
            />
          </div>
        ))}
      </div>

      <Button
        type="link"
        disabled={readOnly}
        icon={<PlusIcon size={14} />}
        style={{ alignSelf: 'flex-start', padding: 0 }}
        onClick={() =>
          onChange([
            ...ingredients,
            { id: `ing-${Date.now()}`, name: '', value: 1, unitId: units[0]?.id ?? 'unit' },
          ])
        }
      >
        Ajouter un ingredient
      </Button>
    </div>
  );
}
