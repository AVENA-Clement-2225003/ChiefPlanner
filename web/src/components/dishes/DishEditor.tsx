import { useEffect, useState } from 'react';
import { Button, Card, Input, InputNumber, Select } from 'antd';
import { ImagePlaceholderIcon } from '@/components/icons';
import { FieldLabel } from '@/components/dishes/FieldLabel';
import { IngredientList } from '@/components/dishes/IngredientList';
import { MealFlowSelector } from '@/components/dishes/MealFlowSelector';
import { RecipeSteps } from '@/components/dishes/RecipeSteps';
import { ShareToggle } from '@/components/dishes/ShareToggle';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { Category, Difficulty, Dish, MealFlow, Unit } from '@/types';

const DIFFICULTY_OPTIONS: { value: Difficulty; label: string }[] = [
  { value: 'easy', label: 'Facile' },
  { value: 'medium', label: 'Moyen' },
  { value: 'hard', label: 'Difficile' },
];

interface DishEditorProps {
  dish: Dish;
  units: Unit[];
  categories: Category[];
  mealFlows: MealFlow[];
  saving: boolean;
  onSave: (dish: Dish) => void;
  onCreateMealFlow: () => void;
}

export function DishEditor({
  dish,
  units,
  categories,
  mealFlows,
  saving,
  onSave,
  onCreateMealFlow,
}: DishEditorProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const [draft, setDraft] = useState<Dish>(dish);

  useEffect(() => {
    setDraft(dish);
  }, [dish]);

  const patch = (changes: Partial<Dish>) => setDraft((current) => ({ ...current, ...changes }));
  const accentSurface = resolvedMode === 'dark' ? accent.tintDark : accent.tint;
  const accentIcon = resolvedMode === 'dark' ? accent.light : accent.base;

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 28, display: 'flex', flexDirection: 'column', gap: 28 } }}
    >
      <div style={{ display: 'flex', gap: 20 }}>
        <span
          style={{
            width: 120,
            height: 120,
            flexShrink: 0,
            borderRadius: 14,
            background: accentSurface,
            color: accentIcon,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
          }}
        >
          <ImagePlaceholderIcon size={36} />
        </span>

        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 10 }}>
          <FieldLabel>Nom du plat</FieldLabel>
          <Input
            value={draft.name}
            variant="borderless"
            onChange={(event) => patch({ name: event.target.value })}
            style={{
              fontFamily: FONTS.heading,
              fontSize: 24,
              fontWeight: 600,
              padding: '0 0 8px',
              borderRadius: 0,
              borderBottom: `1px solid ${neutrals.border}`,
            }}
          />
          <Select
            value={draft.category}
            variant="filled"
            onChange={(category) => patch({ category })}
            options={categories.map((category) => ({ value: category.id, label: category.label }))}
            style={{ width: 200, marginTop: 4 }}
          />
        </div>
      </div>

      <ShareToggle shared={draft.shared} onChange={(shared) => patch({ shared })} />

      <div style={{ display: 'flex', gap: 24, flexWrap: 'wrap' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          <FieldLabel>Portions</FieldLabel>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <Button
              aria-label="Retirer une portion"
              style={{ width: 32, height: 32 }}
              onClick={() => patch({ portions: Math.max(1, draft.portions - 1) })}
            >
              -
            </Button>
            <span style={{ fontSize: 16, fontWeight: 600, width: 20, textAlign: 'center' }}>
              {draft.portions}
            </span>
            <Button
              aria-label="Ajouter une portion"
              style={{ width: 32, height: 32 }}
              onClick={() => patch({ portions: draft.portions + 1 })}
            >
              +
            </Button>
          </div>
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          <FieldLabel>Temps de preparation</FieldLabel>
          <InputNumber
            value={draft.prepTimeMinutes}
            min={0}
            suffix="min"
            onChange={(value) => patch({ prepTimeMinutes: value ?? 0 })}
            style={{ width: 140 }}
          />
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          <FieldLabel>Difficulte</FieldLabel>
          <Select
            value={draft.difficulty}
            onChange={(difficulty) => patch({ difficulty })}
            options={DIFFICULTY_OPTIONS}
            style={{ width: 140 }}
          />
        </div>
      </div>

      <IngredientList
        ingredients={draft.ingredients}
        units={units}
        onChange={(ingredients) => patch({ ingredients })}
      />

      <RecipeSteps steps={draft.steps} onChange={(steps) => patch({ steps })} />

      <MealFlowSelector
        mealFlows={mealFlows}
        selectedIds={draft.mealFlowIds}
        onCreate={onCreateMealFlow}
        onToggle={(mealFlowId) =>
          patch({
            mealFlowIds: draft.mealFlowIds.includes(mealFlowId)
              ? draft.mealFlowIds.filter((id) => id !== mealFlowId)
              : [...draft.mealFlowIds, mealFlowId],
          })
        }
      />

      <div
        style={{
          display: 'flex',
          justifyContent: 'flex-end',
          gap: 12,
          paddingTop: 16,
          borderTop: `1px solid ${neutrals.border}`,
        }}
      >
        <Button type="text" onClick={() => setDraft(dish)}>
          Annuler
        </Button>
        <Button type="primary" loading={saving} onClick={() => onSave(draft)}>
          Enregistrer le plat
        </Button>
      </div>
    </Card>
  );
}
