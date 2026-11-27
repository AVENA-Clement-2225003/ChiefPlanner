import { useEffect, useMemo, useState, type CSSProperties } from 'react';
import { App as AntdApp, Button, Select, Spin } from 'antd';
import { useAsyncData } from '@/hooks/useAsyncData';
import { apiErrorMessage } from '@/services/apiClient';
import { listMealFlows } from '@/services/mealFlowService';
import { getPreferences, savePreferences } from '@/services/preferencesService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { ACCENTS, FONTS } from '@/theme/tokens';
import type { MealFlow, MealSlot, PlanningPreferences, SlotPreference } from '@/types';

const DAYS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
const SLOTS: { slot: MealSlot; label: string }[] = [
  { slot: 'lunch', label: 'Midi' },
  { slot: 'dinner', label: 'Soir' },
];
const MAX_SERVINGS = 100;
const WEEKDAYS = [0, 1, 2, 3, 4];
const WEEKEND = [5, 6];

type FlowKey = 'a' | 'b';
type Preset = { label: string; enabled: (slot: SlotPreference) => boolean };

const PRESETS: Preset[] = [
  { label: 'Tous les repas', enabled: () => true },
  { label: 'Soirs seulement', enabled: (slot) => slot.slot === 'dinner' },
  { label: 'Lundi à vendredi', enabled: (slot) => WEEKDAYS.includes(slot.day) },
  { label: 'Week-end', enabled: (slot) => WEEKEND.includes(slot.day) },
];

const tileButton: CSSProperties = {
  border: 'none',
  background: 'transparent',
  padding: 0,
  cursor: 'pointer',
  fontFamily: FONTS.body,
};

interface Palette {
  surface: string;
  border: string;
  textPrimary: string;
  textSecondary: string;
  accentText: string;
  accentSurface: string;
  accentBase: string;
  flowColors: Record<FlowKey, string>;
}

interface SlotTileProps {
  preference: SlotPreference;
  flows: Partial<Record<FlowKey, MealFlow>>;
  palette: Palette;
  label: string;
  onChange: (changes: Partial<SlotPreference>) => void;
}

/** One Slot of the week: click to plan it or not, then adjust the number of people and the MealFlow. */
function SlotTile({ preference, flows, palette, label, onChange }: SlotTileProps) {
  const flowKeys = (['a', 'b'] as FlowKey[]).filter((key) => flows[key]);
  const { enabled, servings } = preference;

  if (!enabled) {
    return (
      <button
        type="button"
        aria-label={`${label} : pas de repas, planifier`}
        onClick={() => onChange({ enabled: true })}
        style={{
          ...tileButton,
          minHeight: 84,
          borderRadius: 12,
          border: `1px dashed ${palette.border}`,
          color: palette.textSecondary,
          fontSize: 12,
          lineHeight: 1.3,
        }}
      >
        Pas de repas
      </button>
    );
  }

  const stepper: CSSProperties = {
    ...tileButton,
    width: 22,
    height: 22,
    borderRadius: 8,
    background: palette.surface,
    color: palette.textPrimary,
    fontSize: 15,
    fontWeight: 700,
    lineHeight: '22px',
  };

  return (
    <div
      style={{
        borderRadius: 12,
        background: palette.accentSurface,
        border: `1px solid ${palette.accentBase}`,
        padding: '6px 4px 8px',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        gap: 6,
      }}
    >
      <button
        type="button"
        aria-label={`${label} : ne pas planifier de repas`}
        onClick={() => onChange({ enabled: false })}
        style={{ ...tileButton, display: 'flex', flexDirection: 'column', alignItems: 'center', lineHeight: 1 }}
      >
        <span style={{ fontFamily: FONTS.heading, fontSize: 24, fontWeight: 700, color: palette.accentText }}>
          {servings}
        </span>
        <span style={{ fontSize: 11, color: palette.textSecondary, marginTop: 2 }}>
          {servings > 1 ? 'personnes' : 'personne'}
        </span>
      </button>

      <div style={{ display: 'flex', gap: 4 }}>
        <button
          type="button"
          aria-label={`${label} : une personne de moins`}
          disabled={servings <= 1}
          onClick={() => onChange({ servings: servings - 1 })}
          style={{ ...stepper, opacity: servings <= 1 ? 0.4 : 1 }}
        >
          −
        </button>
        <button
          type="button"
          aria-label={`${label} : une personne de plus`}
          disabled={servings >= MAX_SERVINGS}
          onClick={() => onChange({ servings: servings + 1 })}
          style={stepper}
        >
          +
        </button>
      </div>

      {flowKeys.length > 0 && (
        <div role="group" aria-label={`${label} : MealFlow`} style={{ display: 'flex', gap: 2, flexWrap: 'wrap', justifyContent: 'center' }}>
          {([null, ...flowKeys] as (FlowKey | null)[]).map((key) => {
            const selected = preference.flow === key;
            const color = key ? palette.flowColors[key] : palette.textSecondary;
            return (
              <button
                key={key ?? 'all'}
                type="button"
                aria-pressed={selected}
                title={key ? flows[key]?.name : 'Tous mes plats'}
                onClick={() => onChange({ flow: key })}
                style={{
                  ...tileButton,
                  minWidth: 20,
                  height: 20,
                  borderRadius: 6,
                  fontSize: 11,
                  fontWeight: 700,
                  background: selected ? color : 'transparent',
                  color: selected ? '#FFFFFF' : color,
                  border: `1px solid ${selected ? color : palette.border}`,
                }}
              >
                {key ? key.toUpperCase() : '∅'}
              </button>
            );
          })}
        </div>
      )}
    </div>
  );
}

/** The week's settings: which Slots get a meal, for how many people, and from which MealFlow. */
export function PlanningSection() {
  const { neutrals, accent, resolvedMode } = useAppTheme();
  const { message } = AntdApp.useApp();
  const preferences = useAsyncData(getPreferences, []);
  const mealFlows = useAsyncData(listMealFlows, []);
  const [saved, setSaved] = useState<PlanningPreferences | null>(null);
  const [draft, setDraft] = useState<PlanningPreferences | null>(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (preferences.data) {
      setSaved(preferences.data);
      setDraft(preferences.data);
    }
  }, [preferences.data]);

  const isDark = resolvedMode === 'dark';
  // The second MealFlow takes a colour that differs from the accent, whatever the accent is.
  const secondFlow = accent.base === ACCENTS.orange.base ? ACCENTS.turquoise : ACCENTS.orange;
  const secondFlowColor = isDark ? secondFlow.base : secondFlow.hover;
  const palette: Palette = {
    surface: neutrals.surface,
    border: neutrals.border,
    textPrimary: neutrals.textPrimary,
    textSecondary: neutrals.textSecondary,
    accentText: isDark ? accent.light : accent.base,
    accentSurface: isDark ? accent.tintDark : accent.tint,
    accentBase: accent.base,
    flowColors: { a: accent.base, b: secondFlowColor },
  };

  const flowsByKey = useMemo(() => {
    const byId = new Map((mealFlows.data ?? []).map((flow) => [flow.id, flow]));
    return {
      a: draft?.mealFlowAId ? byId.get(draft.mealFlowAId) : undefined,
      b: draft?.mealFlowBId ? byId.get(draft.mealFlowBId) : undefined,
    };
  }, [mealFlows.data, draft?.mealFlowAId, draft?.mealFlowBId]);

  if (!draft || !saved || !mealFlows.data) {
    return <Spin style={{ display: 'block', margin: '40px auto' }} />;
  }

  const dirty = JSON.stringify(draft) !== JSON.stringify(saved);
  const flowOptions = mealFlows.data.map((flow) => ({ value: flow.id, label: flow.name }));
  const slotOf = (day: number, slot: MealSlot) =>
    draft.slots.find((candidate) => candidate.day === day && candidate.slot === slot);

  const patchSlot = (day: number, slot: MealSlot, changes: Partial<SlotPreference>) =>
    setDraft({
      ...draft,
      slots: draft.slots.map((candidate) =>
        candidate.day === day && candidate.slot === slot ? { ...candidate, ...changes } : candidate,
      ),
    });

  /** Choosing a MealFlow for a letter also drops it from slots that used the letter's previous flow. */
  const setFlow = (key: FlowKey, mealFlowId: string | null) =>
    setDraft({
      ...draft,
      [key === 'a' ? 'mealFlowAId' : 'mealFlowBId']: mealFlowId,
      slots: mealFlowId ? draft.slots : draft.slots.map((slot) => (slot.flow === key ? { ...slot, flow: null } : slot)),
    });

  const applyPreset = (preset: Preset) =>
    setDraft({ ...draft, slots: draft.slots.map((slot) => ({ ...slot, enabled: preset.enabled(slot) })) });

  const handleSave = async () => {
    setSaving(true);
    try {
      const result = await savePreferences(draft);
      setSaved(result);
      setDraft(result);
      message.success('Planning enregistré');
    } catch (error) {
      message.error(apiErrorMessage(error, "Le planning n'a pas pu être enregistré."));
    } finally {
      setSaving(false);
    }
  };

  const plannedCount = draft.slots.filter((slot) => slot.enabled).length;
  const flowPicker = (key: FlowKey, other: string | null) => (
    <label style={{ display: 'flex', alignItems: 'center', gap: 8, flex: '1 1 200px', minWidth: 0 }}>
      <span
        aria-hidden
        style={{
          width: 22,
          height: 22,
          flexShrink: 0,
          borderRadius: 7,
          background: palette.flowColors[key],
          color: '#FFFFFF',
          fontSize: 12,
          fontWeight: 700,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
        }}
      >
        {key.toUpperCase()}
      </span>
      <Select
        allowClear
        placeholder="Aucun MealFlow"
        aria-label={`MealFlow ${key.toUpperCase()}`}
        style={{ flex: 1, minWidth: 0 }}
        value={(key === 'a' ? draft.mealFlowAId : draft.mealFlowBId) ?? undefined}
        options={flowOptions.filter((option) => option.value !== other)}
        onChange={(value) => setFlow(key, value ?? null)}
      />
    </label>
  );

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 22, fontWeight: 600, color: palette.textPrimary }}>
          Planning
        </span>
        <span style={{ fontSize: 13, color: palette.textSecondary }}>
          Choisissez les repas à planifier chaque semaine et pour combien de personnes.
        </span>
      </div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
        <span style={{ fontSize: 13, fontWeight: 600, color: palette.textPrimary }}>Plats utilisés</span>
        <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
          {flowPicker('a', draft.mealFlowBId)}
          {flowPicker('b', draft.mealFlowAId)}
        </div>
        <span style={{ fontSize: 12, color: palette.textSecondary }}>
          {mealFlows.data.length === 0
            ? 'Créez un MealFlow depuis la page MealFlows pour en choisir un par repas.'
            : 'Dans la grille, A et B choisissent le MealFlow d’un repas ; ∅ prend tous vos plats.'}
        </span>
      </div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
        <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', gap: 12 }}>
          <span style={{ fontSize: 13, fontWeight: 600, color: palette.textPrimary }}>Repas de la semaine</span>
          <span style={{ fontSize: 12, color: palette.textSecondary }}>{plannedCount} sur 14 planifiés</span>
        </div>

        <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
          {PRESETS.map((preset) => (
            <Button key={preset.label} size="small" shape="round" onClick={() => applyPreset(preset)}>
              {preset.label}
            </Button>
          ))}
        </div>

        <div
          style={{
            display: 'grid',
            gridTemplateColumns: '38px repeat(7, minmax(0, 1fr))',
            gap: 6,
            alignItems: 'stretch',
          }}
        >
          <span />
          {DAYS.map((day) => (
            <span
              key={day}
              style={{ textAlign: 'center', fontSize: 12, fontWeight: 600, color: palette.textSecondary }}
            >
              {day}
            </span>
          ))}

          {SLOTS.map(({ slot, label }) => (
            <SlotRow key={slot} label={label} palette={palette}>
              {DAYS.map((day, index) => {
                const preference = slotOf(index, slot);
                return preference ? (
                  <SlotTile
                    key={day}
                    preference={preference}
                    flows={flowsByKey}
                    palette={palette}
                    label={`${day} ${label.toLowerCase()}`}
                    onChange={(changes) => patchSlot(index, slot, changes)}
                  />
                ) : null;
              })}
            </SlotRow>
          ))}
        </div>
      </div>

      <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
        <Button type="primary" loading={saving} disabled={!dirty} onClick={handleSave}>
          Enregistrer
        </Button>
        {dirty && <span style={{ fontSize: 12, color: palette.textSecondary }}>Modifications non enregistrées</span>}
      </div>
    </div>
  );
}

/** A labelled row of the week grid: the label cell, then one tile per day (they share the grid of the parent). */
function SlotRow({ label, palette, children }: { label: string; palette: Palette; children: React.ReactNode }) {
  return (
    <>
      <span
        style={{
          alignSelf: 'center',
          fontSize: 12,
          fontWeight: 600,
          color: palette.textSecondary,
        }}
      >
        {label}
      </span>
      {children}
    </>
  );
}
