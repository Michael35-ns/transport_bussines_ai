<?php

namespace App\Enums;

enum CostTypeScope: string
{
    case TruckFixed = 'truck_fixed';
    case Overhead = 'overhead';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::TruckFixed => 'Costo fijo de camión',
            self::Overhead => 'Costo general',
            self::Expense => 'Gasto de camión',
        };
    }
}
