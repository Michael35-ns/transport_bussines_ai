<?php

namespace App\Enums;

enum CostTypeScope: string
{
    case TruckFixed = 'truck_fixed';
    case Overhead = 'overhead';
    case Expense = 'expense';
}
