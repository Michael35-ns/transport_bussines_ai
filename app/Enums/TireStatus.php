<?php

namespace App\Enums;

enum TireStatus: string
{
    case Mounted = 'mounted';
    case Removed = 'removed';
    case Disposed = 'disposed';
}
