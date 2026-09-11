<?php

namespace App\Enums;

enum TripStatus: string
{
    case Planned = 'planned';
    case Dispatched = 'dispatched';
    case InTransit = 'in_transit';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
