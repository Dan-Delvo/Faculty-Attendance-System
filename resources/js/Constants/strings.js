/* ============================================================
   String Constants
   Shared across all pages (Admin, Faculty, etc.)
   ============================================================ */

// ── Admin ─────────────────────────────────────────────────────
export const ADMIN_STRINGS = {
    // Login
    portalTitle:    'Admin Portal',
    portalSubtitle: 'Secure access for Authorized Administrators.',
    signInButton:   'Sign in to Admin',

    // Forgot Password
    forgotPasswordTitle:    'Reset Admin Password',
    forgotPasswordSubtitle: "Enter your admin email and we'll send a password reset link.",
    backToAdminLogin:       'Back to Admin Login',

    // Reset Password
    resetPasswordTitle:    'Set New Password',
    resetPasswordSubtitle: 'Choose a strong password for your admin account.',

    // Dashboard
    dashboardPageTitle:   'Admin Dashboard',
    dashboardDescription: 'Monitor faculty attendance, manage schedules, and keep track of campus activity in real time.',

    // Schedules
    schedulesPageTitle:   'Schedule Management',
    schedulesDescription: 'Manage official faculty schedules. Add, edit, or remove schedule entries.',

    // Schedule Change Requests
    changeRequestsPageTitle:   'Schedule Change Requests',
    changeRequestsDescription: 'Review and process faculty schedule change requests. Approved requests update the official schedule.',
};

// ── Faculty ───────────────────────────────────────────────────
export const FACULTY_STRINGS = {
    // Login
    portalTitle:    'Faculty Portal',
    portalSubtitle: 'Secure access for Part-Time Educators.',

    // Dashboard
    dashboardPageTitle:   'Faculty Dashboard',

    // Attendance
    attendancePageTitle:   'Attendance Records',
    attendanceDescription: 'View your daily time-in and time-out records.',

    // Schedule
    schedulePageTitle:   'My Schedule',
    scheduleDescription: 'View your assigned teaching schedule for the current semester.',

    // Schedule Change Requests
    changeRequestsPageTitle:   'Schedule Change Requests',
    changeRequestsDescription: 'Submit and track your schedule change requests.',
};

// ── Shared / Generic ──────────────────────────────────────────
export const SHARED_STRINGS = {
    // Common actions
    save:      'Save',
    cancel:    'Cancel',
    delete:    'Delete',
    edit:      'Edit',
    view:      'View',
    search:    'Search',
    approve:   'Approve',
    reject:    'Reject',
    submit:    'Submit',
    close:     'Close',
    previous:  'Previous',
    next:      'Next',

    // Common states
    loading:   'Loading…',
    saving:    'Saving…',
    deleting:  'Deleting…',
    noResults: 'No results found.',

    // Auth
    rememberMe:      'Remember me',
    forgotPassword:  'Forgot password?',
    signIn:          'Sign in',
    resetPassword:   'Reset Password',
    sendResetLink:   'Send Reset Link',
    backToLogin:     'Back to Login',
};

// ── Backward-compat alias (used by admin pages via admin.js) ──
/** @deprecated Import ADMIN_STRINGS or FACULTY_STRINGS directly instead. */
export const STRINGS = ADMIN_STRINGS;
