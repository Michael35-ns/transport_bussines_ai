<?php

namespace App\Enums;

/**
 * Overhead-allocation basis. `WorkedDays` is the decided default (see
 * docs/decisions/0001-overhead-allocation-method.md).
 */
enum AllocationMethod: string
{
    case WorkedDays = 'worked_days';
    case Trips = 'trips';
    case Km = 'km';
}
