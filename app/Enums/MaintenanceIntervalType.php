<?php

namespace App\Enums;

/**
 * Only `Km` is used today (oil-change interval); `Days` and `EngineHours` are
 * reserved for preventive tasks the company may define later.
 */
enum MaintenanceIntervalType: string
{
    case Km = 'km';
    case Days = 'days';
    case EngineHours = 'engine_hours';
}
