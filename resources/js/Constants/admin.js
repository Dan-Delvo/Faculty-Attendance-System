/* ============================================================
   Admin Constants
   Admin-specific data constants + re-exports from the shared
   sizes, colors, and strings modules.

   Admin pages should import from here. Other pages can import
   directly from '@/Constants/sizes', '@/Constants/colors', or
   '@/Constants/strings' as needed.
   ============================================================ */

// ── Re-export shared constants ────────────────────────────────
export {
    BRAND_PRIMARY,
    BRAND_SECONDARY,
    BRAND_LIGHT,
    CHART_BAR_ACTIVE_COLOR,
    CHART_BAR_BASE_COLOR,
    CHANGE_REQUEST_STATUS_STYLES,
    SCHEDULE_STATUS_STYLES,
    SCHEDULE_TYPE_STYLES,
    DAY_AVATAR_COLORS,
} from '@/Constants/colors';

export {
    DASHBOARD_ITEMS_PER_PAGE,
} from '@/Constants/sizes';

export {
    ADMIN_STRINGS as STRINGS,
    SHARED_STRINGS,
} from '@/Constants/strings';

// ── Admin-specific data ───────────────────────────────────────

// Days of the week
export const DAYS = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
];

// Semester options
export const SEMESTERS = [
    { value: 1, label: '1st Semester' },
    { value: 2, label: '2nd Semester' },
    { value: 3, label: 'Summer' },
];

// Schedule statuses and types
export const SCHEDULE_STATUSES = ['draft', 'active', 'archived'];
export const SCHEDULE_TYPES    = ['fixed', 'flexible'];
