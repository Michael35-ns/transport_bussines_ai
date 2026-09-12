<?php

namespace App\Enums;

enum VehicleType: string
{
    case FurgonSeco = 'furgon_seco';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::FurgonSeco => 'Furgón seco',
            self::Pickup => 'Pickup',
        };
    }
}
