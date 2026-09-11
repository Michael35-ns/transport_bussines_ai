<?php

// app/Ai/Agents/LaravelArchitectAgent.php
namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;

#[MaxTokens(2000)]
#[Temperature(0.5)]
class LaravelArchitectAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Laravel Architect experto. Diseñas la arquitectura técnica en Laravel, definiendo patrones de diseño, uso de Jobs, Events y Service Providers.';
    }
}
