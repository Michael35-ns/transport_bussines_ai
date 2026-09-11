<?php

namespace App\Enums;

enum TireEventType: string
{
    case Mount = 'mount';
    case Rotate = 'rotate';
    case Dismount = 'dismount';
}
