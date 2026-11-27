import { useEffect, useState } from 'react';
import { Alert, Button, Card, Checkbox, Input, InputNumber, Popconfirm, Select } from 'antd';
import { ImagePlaceholderIcon } from '@/components/icons';
import { FieldLabel } from '@/components/dishes/FieldLabel';
import { IngredientList } from '@/components/dishes/IngredientList';
import { MealFlowSelector } from '@/components/dishes/MealFlowSelector';
import { RecipeSteps } from '@/components/dishes/RecipeSteps';
import { ShareToggle } from '@/components/dishes/ShareToggle';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { Difficulty, Dish, MealFlow, MealSlot, Tag, Unit } from '@/types';

const DIFFICULTY_OPTIONS: { value: Difficulty; label: string }[] = [
  { value: 'easy', label: 'Facile' },
  { value: 'medium', label: 'Moyen' },
  { value: 'hard', label: 'Difficile' },
];

const SLOT_OPTIONS: { value: MealSlot; label: string }[] = [
  { value: 'lunch', label: 'Midi' },
  { value: 'dinner', label: 'Soir' },
];

interface DishEditorProps {
  dish: Dish;
  units: Unit[];
  tags: Tag[];
  mealFlows: MealFlow[];
  saving: boolean;
  /** Ingredient quantities that are not whole for one person, reported by the last save. */
  warnings: string[];
  readOnly: boolean;
  onSave: (dish: Dish) => void;
  onDelete: (dish: Dish) => void;
  onCreateMealFlow: () => void;
}

export function DishEditor({
  dish,
  units,
  tags,
  mealFlows,
  saving,
  warnings,
  readOnly,
  onSave,
  onDelete,
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
  const isNew = dish.id === 'new';
  const canSave = !readOnly && draft.name.trim().length > 0 && draft.slots.length > 0 && draft.ingredients.length > 0;

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 28, display: 'flex', flexDirection: 'column', gap: 28 } }}
    >
      {readOnly && (
        <Alert
          type="info"
          showIcon
          message="Ce plat vient de la communauté : vous pouvez le consulter, pas le modifier."
        />
      )}
      {warnings.length > 0 && (
        <Alert
          type="warning"
          showIcon
          message="Quantités non entières pour une personne"
          description={warnings.join(' ')}
        />
      )}

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
            disabled={readOnly}
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
            mode="multiple"
            value={draft.tagIds}
            variant="filled"
            disabled={readOnly}
            placeholder="Étiquettes (asiatique, viande, végé…)"
            onChange={(tagIds) => patch({ tagIds })}
            options={tags.map((tag) => ({ value: tag.id, label: tag.label }))}
            style={{ minWidth: 260, marginTop: 4 }}
          />
        </div>
      </div>

      {!readOnly && <ShareToggle shared={draft.shared} onChange={(shared) => patch({ shared })} />}

      <div style={{ display: 'flex', gap: 24, flexWrap: 'wrap' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          <FieldLabel>Pour combien de personnes</FieldLabel>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <Button
              aria-label="Retirer une personne"
              style={{ width: 32, height: 32 }}
              disabled={readOnly}
              onClick={() => patch({ portions: Math.max(1, draft.portions - 1) })}
            >
              -
            </Button>
            <span style={{ fontSize: 16, fontWeight: 600, width: 20, textAlign: 'center' }}>{draft.portions}</span>
            <Button
              aria-label="Ajouter une personne"
              style={{ width: 32, height: 32 }}
              disabled={readOnly}
              onClick={() => patch({ portions: draft.portions + 1 })}
            >
              +
            </Button>
          </div>
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          <FieldLabel>Moments</FieldLabel>
          <Checkbox.Group
            value={draft.slots}
            disabled={readOnly}
            options={SLOT_OPTIONS}
            onChange={(slots) => patch({ slots: slots as MealSlot[] })}
          />
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          <FieldLabel>Temps de préparation</FieldLabel>
          <InputNumber
            value={draft.prepTimeMinutes}
            min={0}
            suffix="min"
            disabled={readOnly}
            onChange={(value) => patch({ prepTimeMinutes: value ?? 0 })}
            style={{ width: 140 }}
          />
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          <FieldLabel>Difficulté</FieldLabel>
          <Select
            value={draft.difficulty}
            disabled={readOnly}
            onChange={(difficulty) => patch({ difficulty })}
            options={DIFFICULTY_OPTIONS}
            style={{ width: 140 }}
          />
        </div>
      </div>

      <IngredientList
        ingredients={draft.ingredients}
        units={units}
        portions={draft.portions}
        readOnly={readOnly}
        onChange={(ingredients) => patch({ ingredients })}
      />

      <RecipeSteps steps={draft.steps} onChange={(steps) => patch({ steps })} />

      {!readOnly && (
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
      )}

      {!readOnly && (
        <div
          style={{
            display: 'flex',
            justifyContent: 'space-between',
            gap: 12,
            paddingTop: 16,
            borderTop: `1px solid ${neutrals.border}`,
          }}
        >
          {isNew ? (
            <span />
          ) : (
            <Popconfirm
              title="Supprimer ce plat ?"
              description="Il disparaît aussi chez les personnes qui l'ont ajouté à leurs plats."
              okText="Supprimer"
              cancelText="Garder"
              onConfirm={() => onDelete(dish)}
            >
              <Button danger type="text">
                Supprimer
              </Button>
            </Popconfirm>
          )}
          <div style={{ display: 'flex', gap: 12 }}>
            <Button type="text" onClick={() => setDraft(dish)}>
              Annuler
            </Button>
            <Button type="primary" loading={saving} disabled={!canSave} onClick={() => onSave(draft)}>
              Enregistrer le plat
            </Button>
          </div>
        </div>
      )}
    </Card>
  );
}
