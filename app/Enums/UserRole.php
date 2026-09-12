<?php

namespace App\Enums;

enum UserRole: string
{
    case OwnerAdmin = 'owner_admin';
    case Admin = 'admin';
    case Viewer = 'viewer';

    /**
     * Whether this role may create, update, or delete records. Every role
     * may read. `owner_admin` and `admin` are equivalent for CRUD today —
     * the company only distinguishes "the owner" for context, not permissions
     * (docs/business/discovery.md §L.2 #7).
     */
    public function canManage(): bool
    {
        return $this !== self::Viewer;
    }
}
