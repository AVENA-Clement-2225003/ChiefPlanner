import { CheckIcon, ImagePlaceholderIcon, PlusIcon } from '@/components/icons';
import { tagsCaption } from '@/services/catalogService';
import { useAppTheme } from '@/theme/ThemeProvider';
import { RADII } from '@/theme/tokens';
import type { Dish } from '@/types';

const CARD_HEIGHT = 82;
const THUMBNAIL_WIDTH = 110;

interface DishResultCardProps {
  dish: Dish;
  onToggleSubscription: (dish: Dish) => void;
}

/** Ownership drives the right-hand indicator: own dishes cannot be subscribed to. */
function OwnershipIndicator({ dish, onToggleSubscription }: DishResultCardProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();

  if (dish.ownedByCurrentUser) {
    return (
      <span
        style={{
          padding: '3px 8px',
          borderRadius: 999,
          background: neutrals.surfaceMuted,
          color: neutrals.textSecondary,
          fontSize: 10,
          fontWeight: 700,
          flexShrink: 0,
        }}
      >
        VOUS
      </span>
    );
  }

  const subscribed = dish.subscribed;

  return (
    <button
      type="button"
      aria-label={subscribed ? `Se desabonner de ${dish.name}` : `S'abonner a ${dish.name}`}
      onClick={() => onToggleSubscription(dish)}
      style={{
        width: 24,
        height: 24,
        flexShrink: 0,
        borderRadius: 999,
        cursor: 'pointer',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: subscribed
          ? resolvedMode === 'dark'
            ? accent.tintDark
            : accent.tint
          : accent.base,
        border: subscribed ? `1.5px solid ${accent.base}` : 'none',
        color: subscribed ? (resolvedMode === 'dark' ? accent.light : accent.base) : '#FFFFFF',
      }}
    >
      {subscribed ? <CheckIcon size={12} /> : <PlusIcon size={12} strokeWidth={3} />}
    </button>
  );
}

export function DishResultCard({ dish, onToggleSubscription }: DishResultCardProps) {
  const { accent, neutrals, resolvedMode } = useAppTheme();
  const thumbnailBackground = dish.ownedByCurrentUser
    ? resolvedMode === 'dark'
      ? accent.tintDark
      : accent.tint
    : neutrals.surfaceMuted;
  const thumbnailColor = dish.ownedByCurrentUser
    ? resolvedMode === 'dark'
      ? accent.light
      : accent.base
    : neutrals.textDisabled;

  return (
    <div
      style={{
        height: CARD_HEIGHT,
        display: 'flex',
        overflow: 'hidden',
        background: neutrals.surface,
        border: `1px solid ${neutrals.border}`,
        borderRadius: RADII.tile,
      }}
    >
      <span
        style={{
          width: THUMBNAIL_WIDTH,
          flexShrink: 0,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          background: thumbnailBackground,
          color: thumbnailColor,
        }}
      >
        <ImagePlaceholderIcon size={28} />
      </span>

      <div
        style={{
          flex: 1,
          minWidth: 0,
          padding: 14,
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'space-between',
          gap: 8,
        }}
      >
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 6 }}>
          <span
            style={{
              fontSize: 14,
              fontWeight: 600,
              color: neutrals.textPrimary,
              overflow: 'hidden',
              textOverflow: 'ellipsis',
              display: '-webkit-box',
              WebkitLineClamp: 2,
              WebkitBoxOrient: 'vertical',
            }}
          >
            {dish.name}
          </span>
          <OwnershipIndicator dish={dish} onToggleSubscription={onToggleSubscription} />
        </div>
        <span style={{ fontSize: 12, color: neutrals.textSecondary }}>
          {dish.prepTimeMinutes} min - {tagsCaption(dish.tagIds) || 'sans étiquette'}
        </span>
      </div>
    </div>
  );
}
