<?php

namespace App\Enums;

enum AdminPermission: string
{
    case VIEW_DASHBOARD = 'view dashboard';
    case CREATE_FACULTY = 'create faculty';
    case EDIT_FACULTY = 'edit faculty';
    case DELETE_FACULTY = 'delete faculty';
    case VIEW_DEPARTMENTS = 'view departments';
    case CREATE_DEPARTMENTS = 'create departments';
    case EDIT_DEPARTMENTS = 'edit departments';
    case DELETE_DEPARTMENTS = 'delete departments';
    case VIEW_SCHEDULES = 'view schedules';
    case CREATE_SCHEDULES = 'create schedules';
    case EDIT_SCHEDULES = 'edit schedules';
    case DELETE_SCHEDULES = 'delete schedules';
    case VIEW_ATTENDANCE = 'view attendance';
    case CREATE_ATTENDANCE = 'create attendance';
    case EDIT_ATTENDANCE = 'edit attendance';
    case DELETE_ATTENDANCE = 'delete attendance';
    case GENERATE_DTR = 'generate dtr';
    case VIEW_LEAVES = 'view leaves';
    case CREATE_LEAVES = 'create leaves';
    case EDIT_LEAVES = 'edit leaves';
    case DELETE_LEAVES = 'delete leaves';
    case VIEW_REPORTS = 'view reports';
    case VIEW_SETTINGS = 'view settings';
    case CREATE_SETTINGS = 'create settings';
    case EDIT_SETTINGS = 'edit settings';
    case DELETE_SETTINGS = 'delete settings';
    case VIEW_USERS = 'view users';
    case CREATE_USERS = 'create users';
    case EDIT_USERS = 'edit users';
    case DELETE_USERS = 'delete users';
    case IMPORT_BIOMETRIC_LOGS = 'import biometric logs';
    case VIEW_HOLIDAYS = 'view holidays';
    case CREATE_HOLIDAYS = 'create holidays';
    case EDIT_HOLIDAYS = 'edit holidays';
    case DELETE_HOLIDAYS = 'delete holidays';
    case VIEW_REQUESTS = 'view requests';
    case APPROVE_REQUESTS = 'approve requests';
    case REJECT_REQUESTS = 'reject requests';
    case MANAGE_ROLES = 'manage roles';
    case MANAGE_PERMISSIONS = 'manage permissions';
    case VIEW_LOGS = 'view logs';
    case VIEW_SYSTEM_SETTINGS = 'view system settings';
    case EDIT_SYSTEM_SETTINGS = 'edit system settings';
}