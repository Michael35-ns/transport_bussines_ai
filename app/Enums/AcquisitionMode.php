<?php

namespace App\Enums;

enum AcquisitionMode: string
{
    case Owned = 'owned';
    case Financed = 'financed';

    public function label(): string
    {
        return match ($this) {
            self::Owned => 'Propio',
            self::Financed => 'Financiado',
        };
    }
}
