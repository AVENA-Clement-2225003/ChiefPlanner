import type { SVGProps } from 'react';

type IconProps = SVGProps<SVGSVGElement> & { size?: number };

/** Stroke icons lifted from the design canvas so the UI keeps one icon style. */
function Icon({ size = 16, children, ...props }: IconProps) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth={2}
      strokeLinecap="round"
      strokeLinejoin="round"
      {...props}
    >
      {children}
    </svg>
  );
}

export function SunIcon(props: IconProps) {
  return (
    <Icon strokeWidth={2} {...props}>
      <circle cx="12" cy="12" r="4" />
      <path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5L19 19M5 19l1.5-1.5M17.5 6.5L19 5" />
    </Icon>
  );
}

export function MoonIcon({ size = 16, ...props }: IconProps) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor" {...props}>
      <path d="M12 3a7 7 0 1 0 8.5 8.5A7 7 0 0 1 12 3z" />
    </svg>
  );
}

export function MonitorIcon(props: IconProps) {
  return (
    <Icon strokeWidth={1.5} {...props}>
      <rect x="2" y="4" width="20" height="13" rx="2" />
      <path d="M8 20h8M12 17v3" />
    </Icon>
  );
}

export function LogoutIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
      <polyline points="16 17 21 12 16 7" />
      <line x1="21" y1="12" x2="9" y2="12" />
    </Icon>
  );
}

export function ChevronLeftIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M15 6l-6 6 6 6" />
    </Icon>
  );
}

export function ChevronRightIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M9 6l6 6-6 6" />
    </Icon>
  );
}

export function RefreshIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M3 12a9 9 0 1 1 2.6 6.4" />
      <path d="M3 21v-6h6" />
    </Icon>
  );
}

export function BagCheckIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M4 8h16l-1 11H5z" />
      <path d="M8 8V6a4 4 0 0 1 8 0v2" />
      <path d="M8.5 12l2 2 4-4" />
    </Icon>
  );
}

export function PencilIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M12 2l3 3-10 10-4 1 1-4z" />
    </Icon>
  );
}

export function ImagePlaceholderIcon(props: IconProps) {
  return (
    <Icon strokeWidth={1.5} {...props}>
      <rect x="3" y="4" width="18" height="16" rx="2" />
      <circle cx="8.5" cy="9.5" r="1.5" />
      <path d="M21 15l-5-5-9 9" />
    </Icon>
  );
}

export function ClockIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <circle cx="12" cy="12" r="9" />
      <path d="M12 7v5l3 3" />
    </Icon>
  );
}

export function PlusIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M12 5v14M5 12h14" />
    </Icon>
  );
}

export function CloseIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M6 6l12 12M18 6L6 18" />
    </Icon>
  );
}

export function CheckIcon(props: IconProps) {
  return (
    <Icon strokeWidth={3} {...props}>
      <path d="M4 12l5 5 11-11" />
    </Icon>
  );
}

export function SearchIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <circle cx="11" cy="11" r="7" />
      <path d="M21 21l-4.3-4.3" />
    </Icon>
  );
}

export function SlidersIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M4 6h10M4 12h16M4 18h10" />
      <circle cx="17" cy="6" r="1.6" fill="currentColor" />
      <circle cx="8" cy="12" r="1.6" fill="currentColor" />
      <circle cx="17" cy="18" r="1.6" fill="currentColor" />
    </Icon>
  );
}

export function BellIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M12 3l8 4v2H4V7z" />
      <path d="M4 21V9M20 21V9M8 21v-8M12 21v-8M16 21v-8" />
    </Icon>
  );
}

export function DatabaseIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <ellipse cx="12" cy="6" rx="8" ry="3" />
      <path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6" />
      <path d="M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6" />
    </Icon>
  );
}

export function ShieldIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z" />
    </Icon>
  );
}

export function DocumentIcon(props: IconProps) {
  return (
    <Icon {...props}>
      <rect x="5" y="2" width="14" height="20" rx="2" />
      <path d="M8 8h8M8 12h8M8 16h5" />
    </Icon>
  );
}

export function GripIcon({ size = 14, ...props }: IconProps) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor" {...props}>
      <circle cx="5" cy="4" r="1.6" />
      <circle cx="5" cy="10" r="1.6" />
      <circle cx="5" cy="16" r="1.6" />
      <circle cx="10" cy="4" r="1.6" />
      <circle cx="10" cy="10" r="1.6" />
      <circle cx="10" cy="16" r="1.6" />
    </svg>
  );
}
