<?php

namespace App\Enums;

enum WebPermission: string
{
    case VIEW_DASHBOARD = 'view dashboard';
    case VIEW_ATTENDANCE = 'view attendance';
    case GENERATE_DTR = 'generate dtr';
    case VIEW_OWN_REQUESTS = 'view own requests';
    case CREATE_OWN_REQUESTS = 'create own requests';
    case EDIT_OWN_REQUESTS = 'edit own requests';
    case DELETE_OWN_REQUESTS = 'delete own requests';
}