/* ============================================================
   Admin Constants
   Centralised sizes, colors, data, badge styles, and strings
   used exclusively across Admin pages.
   ============================================================ */

// ── Brand colors ──────────────────────────────────────────────
export const BRAND_PRIMARY   = '#7a1315';
export const BRAND_SECONDARY = '#a81a1e';
export const BRAND_LIGHT     = '#cc2127';

// ── Chart colors (used in Recharts, not Tailwind) ─────────────
export const CHART_BAR_ACTIVE_COLOR = '#7a1315';
export const CHART_BAR_BASE_COLOR   = '#d1d5db';

// ── Sizes ─────────────────────────────────────────────────────
export const DASHBOARD_ITEMS_PER_PAGE = 5;

// ── Days of the week ──────────────────────────────────────────
export const DAYS = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
];

// ── Semester options ──────────────────────────────────────────
export const SEMESTERS = [
    { value: 1, label: '1st Semester' },
    { value: 2, label: '2nd Semester' },
    { value: 3, label: 'Summer' },
];

// ── Schedule statuses and types ───────────────────────────────
export const SCHEDULE_STATUSES = ['draft', 'active', 'archived'];
export const SCHEDULE_TYPES    = ['fixed', 'flexible'];

// ── Badge styles: schedule change request status ──────────────
export const CHANGE_REQUEST_STATUS_STYLES = {
    pending:  'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/30',
    approved: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/30',
    rejected: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30',
};

// ── Badge styles: schedule status ─────────────────────────────
export const SCHEDULE_STATUS_STYLES = {
    active:   'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 ring-emerald-600/20',
    draft:    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 ring-amber-600/20',
    archived: 'bg-gray-100 text-gray-600 dark:bg-gray-700/40 dark:text-gray-400 ring-gray-600/20',
};

// ── Badge styles: schedule type ───────────────────────────────
export const SCHEDULE_TYPE_STYLES = {
    fixed:    'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 ring-sky-600/20',
    flexible: 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300 ring-violet-600/20',
};

// ── Day avatar gradient colors ────────────────────────────────
export const DAY_AVATAR_COLORS = {
    Monday:    'from-sky-500 to-sky-600',
    Tuesday:   'from-violet-500 to-violet-600',
    Wednesday: 'from-blue-500 to-blue-600',
    Thursday:  'from-teal-500 to-teal-600',
    Friday:    'from-orange-500 to-orange-600',
    Saturday:  'from-pink-500 to-pink-600',
    Sunday:    'from-rose-500 to-rose-600',
};

// ── UI strings ────────────────────────────────────────────────
export const STRINGS = {
    // Login
    portalTitle:    'Admin Portal',
    portalSubtitle: 'Secure access for Authorized Administrators.',

    // Dashboard
    dashboardPageTitle:   'Admin Dashboard',
    dashboardDescription: 'Monitor faculty attendance, manage schedules, and keep track of campus activity in real time.',

    // Schedules
    schedulesPageTitle:   'Schedule Management',
    schedulesDescription: 'Manage official faculty schedules. Add, edit, or remove schedule entries.',

    // Schedule change requests
    changeRequestsPageTitle:   'Schedule Change Requests',
    changeRequestsDescription: 'Review and process faculty schedule change requests. Approved requests update the official schedule.',
};
