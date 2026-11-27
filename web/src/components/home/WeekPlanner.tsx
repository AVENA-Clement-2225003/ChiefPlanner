import { Button, Card } from 'antd';
import { ChevronLeftIcon, RefreshIcon } from '@/components/icons';
import { useAppTheme } from '@/theme/ThemeProvider';
import { FONTS, RADII } from '@/theme/tokens';
import type { MealSlot, PlannedDay, PlannedMeal } from '@/types';

const SLOT_ROWS: { slot: MealSlot; label: string }[] = [
  { slot: 'lunch', label: 'Dejeuner' },
  { slot: 'dinner', label: 'Diner' },
];

const SLOT_HEIGHT = 48;

interface MealSlotCellProps {
  meal: PlannedMeal | null;
  highlighted: boolean;
  onClick: () => void;
}

function MealSlotCell({ meal, highlighted, onClick }: MealSlotCellProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const accentSurface = resolvedMode === 'dark' ? accent.tintDark : accent.tint;

  const filledStyle = highlighted
    ? { background: neutrals.surface, border: `1px solid ${accent.base}` }
    : { background: accentSurface, border: '1px solid transparent' };

  return (
    <button
      type="button"
      onClick={onClick}
      style={{
        height: SLOT_HEIGHT,
        width: highlighted ? 'calc(100% - 8px)' : '100%',
        borderRadius: 8,
        padding: '0 8px',
        cursor: 'pointer',
        textAlign: 'left',
        display: 'flex',
        alignItems: 'center',
        overflow: 'hidden',
        fontFamily: FONTS.body,
        fontSize: 12,
        fontWeight: 600,
        lineHeight: 1.3,
        color: meal ? neutrals.textPrimary : neutrals.textDisabled,
        ...(meal
          ? filledStyle
          : { background: 'transparent', border: `1px dashed ${neutrals.border}`, justifyContent: 'center' }),
      }}
    >
      <span
        style={{
          display: '-webkit-box',
          WebkitLineClamp: 2,
          WebkitBoxOrient: 'vertical',
          overflow: 'hidden',
        }}
      >
        {meal ? meal.dishName : '+ Ajouter'}
      </span>
    </button>
  );
}

interface WeekPlannerProps {
  days: PlannedDay[];
  onPreviousWeek: () => void;
  onRegenerate: () => void;
  onSelectSlot: (day: PlannedDay, slot: MealSlot) => void;
  regenerating?: boolean;
}

export function WeekPlanner({
  days,
  onPreviousWeek,
  onRegenerate,
  onSelectSlot,
  regenerating = false,
}: WeekPlannerProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const accentText = resolvedMode === 'dark' ? accent.light : accent.base;
  const accentSurface = resolvedMode === 'dark' ? accent.tintDark : accent.tint;

  return (
    <Card
      style={{ borderRadius: RADII.card, borderColor: neutrals.border }}
      styles={{ body: { padding: 24, display: 'flex', flexDirection: 'column', gap: 16 } }}
    >
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <span style={{ fontFamily: FONTS.heading, fontSize: 18, fontWeight: 600, color: neutrals.textPrimary }}>
          Planning de la semaine
        </span>
        <div style={{ display: 'flex', gap: 10 }}>
          <Button
            aria-label="Semaine precedente"
            icon={<ChevronLeftIcon size={16} />}
            onClick={onPreviousWeek}
            style={{ width: 36, height: 36 }}
          />
          <Button
            type="primary"
            aria-label="Regenerer la semaine"
            icon={<RefreshIcon size={16} />}
            loading={regenerating}
            onClick={onRegenerate}
            style={{ width: 36, height: 36 }}
          />
        </div>
      </div>

      <div style={{ display: 'flex', gap: 10 }}>
        <div
          style={{
            width: 70,
            flexShrink: 0,
            display: 'flex',
            flexDirection: 'column',
            gap: 4,
            // Matches the day column rhythm: 20px header + 4px gap before the first slot.
            paddingTop: 24,
          }}
        >
          {SLOT_ROWS.map((row) => (
            <span
              key={row.slot}
              style={{
                height: SLOT_HEIGHT,
                display: 'flex',
                alignItems: 'center',
                fontSize: 11,
                fontWeight: 600,
                color: neutrals.textSecondary,
              }}
            >
              {row.label}
            </span>
          ))}
        </div>

        {days.map((day) => (
          <div
            key={day.date}
            style={{
              flex: 1,
              minWidth: 0,
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: 4,
              borderRadius: RADII.inner,
              padding: day.isToday ? '2px 0 6px' : 0,
              background: day.isToday ? accentSurface : 'transparent',
            }}
          >
            <span
              style={{
                height: 20,
                fontSize: 12,
                fontWeight: day.isToday ? 700 : 600,
                color: day.isToday ? accentText : neutrals.textSecondary,
              }}
            >
              {day.shortLabel}
            </span>
            {SLOT_ROWS.map((row) => (
              <MealSlotCell
                key={row.slot}
                meal={day[row.slot]}
                highlighted={day.isToday}
                onClick={() => onSelectSlot(day, row.slot)}
              />
            ))}
          </div>
        ))}
      </div>
    </Card>
  );
}
