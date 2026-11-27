import { useEffect, useState } from 'react';
import { App as AntdApp, Button, InputNumber, Select, Spin, Switch } from 'antd';
import { useAsyncData } from '@/hooks/useAsyncData';
import { apiErrorMessage } from '@/services/apiClient';
import { listMealFlows } from '@/services/mealFlowService';
import { getPreferences, savePreferences } from '@/services/preferencesService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS } from '@/theme/tokens';
import type { MealSlot, PlanningPreferences, SlotPreference } from '@/types';

const DAY_LABELS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
const SLOT_LABELS: Record<MealSlot, string> = { lunch: 'Midi', dinner: 'Soir' };
const SLOTS: MealSlot[] = ['lunch', 'dinner'];

/** The week's settings: which Slots get a meal, for how many people, and from which MealFlow. */
export function PlanningSection() {
  const { neutrals } = useAppTheme();
  const { message } = AntdApp.useApp();
  const preferences = useAsyncData(getPreferences, []);
  const mealFlows = useAsyncData(listMealFlows, []);
  const [draft, setDraft] = useState<PlanningPreferences | null>(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (preferences.data) {
      setDraft(preferences.data);
    }
  }, [preferences.data]);

  if (!draft || !mealFlows.data) {
    return <Spin style={{ display: 'block', margin: '40px auto' }} />;
  }

  const flowOptions = mealFlows.data.map((flow) => ({ value: flow.id, label: flow.name }));
  const slotOf = (day: number, slot: MealSlot) => draft.slots.find((candidate) => candidate.day === day && candidate.slot === slot);

  const patchSlot = (day: number, slot: MealSlot, changes: Partial<SlotPreference>) =>
    setDraft({
      ...draft,
      slots: draft.slots.map((candidate) =>
        candidate.day === day && candidate.slot === slot ? { ...candidate, ...changes } : candidate,
      ),
    });

  const handleSave = async () => {
    setSaving(true);
    try {
      setDraft(await savePreferences(draft));
      message.success('Planning enregistré');
    } catch (error) {
      message.error(apiErrorMessage(error, "Le planning n'a pas pu être enregistré."));
    } finally {
      setSaving(false);
    }
  };

  const flowChoices = [
    { value: 'none', label: 'Tous mes plats' },
    ...(draft.mealFlowAId ? [{ value: 'a', label: 'MealFlow A' }] : []),
    ...(draft.mealFlowBId ? [{ value: 'b', label: 'MealFlow B' }] : []),
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
      <span style={{ fontFamily: FONTS.heading, fontSize: 22, fontWeight: 600, color: neutrals.textPrimary }}>
        Planning
      </span>

      <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap' }}>
        <label style={{ display: 'flex', flexDirection: 'column', gap: 6, fontSize: 13, color: neutrals.textSecondary }}>
          MealFlow A
          <Select
            allowClear
            placeholder="Aucun"
            style={{ width: 220 }}
            value={draft.mealFlowAId ?? undefined}
            options={flowOptions.filter((option) => option.value !== draft.mealFlowBId)}
            onChange={(value) => setDraft({ ...draft, mealFlowAId: value ?? null })}
          />
        </label>
        <label style={{ display: 'flex', flexDirection: 'column', gap: 6, fontSize: 13, color: neutrals.textSecondary }}>
          MealFlow B
          <Select
            allowClear
            placeholder="Aucun"
            style={{ width: 220 }}
            value={draft.mealFlowBId ?? undefined}
            options={flowOptions.filter((option) => option.value !== draft.mealFlowAId)}
            onChange={(value) => setDraft({ ...draft, mealFlowBId: value ?? null })}
          />
        </label>
      </div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
        {DAY_LABELS.map((label, day) => (
          <div key={label} style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
            <span style={{ width: 84, fontSize: 14, fontWeight: 600, color: neutrals.textPrimary }}>{label}</span>
            {SLOTS.map((slot) => {
              const preference = slotOf(day, slot);
              if (!preference) {
                return null;
              }
              return (
                <div key={slot} style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <Switch
                    size="small"
                    checked={preference.enabled}
                    aria-label={`${label} ${SLOT_LABELS[slot]}`}
                    onChange={(enabled) => patchSlot(day, slot, { enabled })}
                  />
                  <span style={{ width: 34, fontSize: 12, color: neutrals.textSecondary }}>{SLOT_LABELS[slot]}</span>
                  <InputNumber
                    size="small"
                    min={1}
                    max={100}
                    style={{ width: 56 }}
                    disabled={!preference.enabled}
                    value={preference.servings}
                    aria-label={`Personnes ${label} ${SLOT_LABELS[slot]}`}
                    onChange={(value) => patchSlot(day, slot, { servings: value ?? 1 })}
                  />
                  <Select
                    size="small"
                    style={{ width: 130 }}
                    disabled={!preference.enabled}
                    value={preference.flow ?? 'none'}
                    options={flowChoices}
                    onChange={(value) => patchSlot(day, slot, { flow: value === 'none' ? null : (value as 'a' | 'b') })}
                  />
                </div>
              );
            })}
          </div>
        ))}
      </div>

      <Button type="primary" loading={saving} onClick={handleSave} style={{ alignSelf: 'flex-start' }}>
        Enregistrer
      </Button>
    </div>
  );
}
