<?php

namespace App\Enums;

enum Permission: string
{
    // ── Dashboard ──────────────────────────────────────────────────────────
    case ViewDashboard          = 'view dashboard';

    // ── Faculty ────────────────────────────────────────────────────────────
    case ViewFaculty            = 'view faculty';
    case CreateFaculty          = 'create faculty';
    case EditFaculty            = 'edit faculty';
    case DeleteFaculty          = 'delete faculty';

    // ── Departments ────────────────────────────────────────────────────────
    case ViewDepartments        = 'view departments';
    case CreateDepartments      = 'create departments';
    case EditDepartments        = 'edit departments';
    case DeleteDepartments      = 'delete departments';

    // ── Schedules ──────────────────────────────────────────────────────────
    case ViewSchedules          = 'view schedules';
    case CreateSchedules        = 'create schedules';
    case EditSchedules          = 'edit schedules';
    case DeleteSchedules        = 'delete schedules';

    // ── Attendance ─────────────────────────────────────────────────────────
    case ViewAttendance         = 'view attendance';
    case CreateAttendance       = 'create attendance';
    case EditAttendance         = 'edit attendance';
    case DeleteAttendance       = 'delete attendance';

    // ── DTR ────────────────────────────────────────────────────────────────
    case GenerateDtr            = 'generate dtr';

    // ── Leaves ─────────────────────────────────────────────────────────────
    case ViewLeaves             = 'view leaves';
    case CreateLeaves           = 'create leaves';
    case EditLeaves             = 'edit leaves';
    case DeleteLeaves           = 'delete leaves';

    // ── Reports ────────────────────────────────────────────────────────────
    case ViewReports            = 'view reports';

    // ── Settings ───────────────────────────────────────────────────────────
    case ViewSettings           = 'view settings';
    case CreateSettings         = 'create settings';
    case EditSettings           = 'edit settings';
    case DeleteSettings         = 'delete settings';

    // ── Users ──────────────────────────────────────────────────────────────
    case ViewUsers              = 'view users';
    case CreateUsers            = 'create users';
    case EditUsers              = 'edit users';
    case DeleteUsers            = 'delete users';

    // ── Biometric ──────────────────────────────────────────────────────────
    case ImportBiometricLogs    = 'import biometric logs';

    // ── Holidays ───────────────────────────────────────────────────────────
    case ViewHolidays           = 'view holidays';
    case CreateHolidays         = 'create holidays';
    case EditHolidays           = 'edit holidays';
    case DeleteHolidays         = 'delete holidays';

    // ── Requests (Admin) ───────────────────────────────────────────────────
    case ViewRequests           = 'view requests';
    case ApproveRequests        = 'approve requests';
    case RejectRequests         = 'reject requests';

    // ── Requests (Faculty / web guard) ─────────────────────────────────────
    case ViewOwnRequests        = 'view own requests';
    case CreateOwnRequests      = 'create own requests';
    case EditOwnRequests        = 'edit own requests';
    case DeleteOwnRequests      = 'delete own requests';

    // ── Roles & Permissions ────────────────────────────────────────────────
    case ManageRoles            = 'manage roles';
    case ManagePermissions      = 'manage permissions';

    // ── Logs ───────────────────────────────────────────────────────────────
    case ViewLogs               = 'view logs';

    // ── System Settings ────────────────────────────────────────────────────
    case ViewSystemSettings     = 'view system settings';
    case EditSystemSettings     = 'edit system settings';

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Returns all admin-guard permission values. */
    public static function adminPermissions(): array
    {
        return [
            self::ViewDashboard,
            self::ViewFaculty,
            self::CreateFaculty,
            self::EditFaculty,
            self::DeleteFaculty,
            self::ViewDepartments,
            self::CreateDepartments,
            self::EditDepartments,
            self::DeleteDepartments,
            self::ViewSchedules,
            self::CreateSchedules,
            self::EditSchedules,
            self::DeleteSchedules,
            self::ViewAttendance,
            self::CreateAttendance,
            self::EditAttendance,
            self::DeleteAttendance,
            self::GenerateDtr,
            self::ViewLeaves,
            self::CreateLeaves,
            self::EditLeaves,
            self::DeleteLeaves,
            self::ViewReports,
            self::ViewSettings,
            self::CreateSettings,
            self::EditSettings,
            self::DeleteSettings,
            self::ViewUsers,
            self::CreateUsers,
            self::EditUsers,
            self::DeleteUsers,
            self::ImportBiometricLogs,
            self::ViewHolidays,
            self::CreateHolidays,
            self::EditHolidays,
            self::DeleteHolidays,
            self::ViewRequests,
            self::ApproveRequests,
            self::RejectRequests,
            self::ManageRoles,
            self::ManagePermissions,
            self::ViewLogs,
            self::ViewSystemSettings,
            self::EditSystemSettings,
        ];
    }

    /** Returns all web-guard (faculty) permission values. */
    public static function webPermissions(): array
    {
        return [
            self::ViewDashboard,
            self::ViewAttendance,
            self::GenerateDtr,
            self::ViewOwnRequests,
            self::CreateOwnRequests,
            self::EditOwnRequests,
            self::DeleteOwnRequests,
        ];
    }

    /** Returns all HR staff permission values. */
    public static function hrStaffPermissions(): array
    {
        return [
            self::ViewDashboard,
            self::ViewFaculty,
            self::CreateFaculty,
            self::EditFaculty,
            self::DeleteFaculty,
            self::ViewAttendance,
            self::CreateAttendance,
            self::EditAttendance,
            self::DeleteAttendance,
            self::GenerateDtr,
            self::ViewLeaves,
            self::CreateLeaves,
            self::EditLeaves,
            self::DeleteLeaves,
            self::ViewReports,
            self::ImportBiometricLogs,
        ];
    }
}
