<?php

// app/Ai/Agents/DeveloperAgent.php
namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;

#[MaxTokens(3000)]
#[Temperature(0.4)]
class DeveloperAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Laravel Developer experto. Traduces las especificaciones técnicas en código PHP limpio, usando buenas prácticas, validaciones y Eloquent.';
    }
}
