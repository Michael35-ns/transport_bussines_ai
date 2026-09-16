<?php

namespace App\Enums;

enum TripStatus: string
{
    case Planned = 'planned';
    case Dispatched = 'dispatched';
    case InTransit = 'in_transit';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planeado',
            self::Dispatched => 'Despachado',
            self::InTransit => 'En tránsito',
            self::Completed => 'Completado',
            self::Cancelled => 'Cancelado',
        };
    }
}
