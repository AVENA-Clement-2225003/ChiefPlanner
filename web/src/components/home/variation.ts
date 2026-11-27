/** "+12%" / "-5%" for a month-over-month change, or a dash when there is no previous month to compare with. */
export function formatVariation(variation: number | null): string {
  if (variation === null) {
    return '—';
  }
  return `${variation > 0 ? '+' : ''}${variation}%`;
}

/** Spending going down is good news. With nothing to compare, the colour stays neutral. */
export function isGoodVariation(variation: number | null): boolean | null {
  return variation === null ? null : variation <= 0;
}
