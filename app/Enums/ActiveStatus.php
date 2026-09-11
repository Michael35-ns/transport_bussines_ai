<?php

namespace App\Enums;

/**
 * Shared active/inactive status for master data (trucks, drivers, …).
 */
enum ActiveStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
