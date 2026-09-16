<?php

namespace App\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Mensual',
            self::Quarterly => 'Trimestral',
            self::Annual => 'Anual',
        };
    }
}
