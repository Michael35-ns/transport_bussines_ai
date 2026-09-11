<?php

namespace App\Enums;

enum UserRole: string
{
    case OwnerAdmin = 'owner_admin';
    case Admin = 'admin';
    case Viewer = 'viewer';
}
