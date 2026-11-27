import { describe, expect, it } from 'vitest';
import { dayIndexOf, isoDate, toWeekPlan } from './weekService';

describe('dayIndexOf', () => {
  it('counts days from Monday', () => {
    expect(dayIndexOf('2026-11-23')).toBe(0); // Monday
    expect(dayIndexOf('2026-11-29')).toBe(6); // Sunday
  });
});

describe('isoDate', () => {
  it('keeps the local calendar day', () => {
    expect(isoDate(new Date(2026, 10, 29, 23, 30))).toBe('2026-11-29');
  });
});

describe('toWeekPlan', () => {
  const wednesday = new Date(2026, 10, 25, 12);

  it('lays the week out from Monday and marks today', () => {
    const plan = toWeekPlan({ meals: [], hasPrevious: false }, null, wednesday);

    expect(plan.weekStart).toBe('2026-11-23');
    expect(plan.days.map((day) => day.shortLabel)).toEqual(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']);
    expect(plan.days.filter((day) => day.isToday).map((day) => day.date)).toEqual(['2026-11-25']);
  });

  it('puts each meal in its day and slot, and flags a meal whose dish is gone', () => {
    const plan = toWeekPlan(
      {
        hasPrevious: true,
        meals: [
          { day: 1, slot: 'dinner', dishId: 7, dishName: 'Curry', servings: 3, needsReplacement: false },
          { day: 2, slot: 'lunch', dishId: null, dishName: null, servings: 2, needsReplacement: true },
        ],
      },
      '4',
      wednesday,
    );

    expect(plan.hasPrevious).toBe(true);
    expect(plan.activeMealFlowId).toBe('4');
    expect(plan.days[1].dinner).toEqual({ dishId: '7', dishName: 'Curry', servings: 3, needsReplacement: false });
    expect(plan.days[1].lunch).toBeNull();
    expect(plan.days[2].lunch?.needsReplacement).toBe(true);
  });
});
