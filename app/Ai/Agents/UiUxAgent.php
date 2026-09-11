<?php

// app/Ai/Agents/UiUxAgent.php
namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;

#[MaxTokens(1500)]
#[Temperature(0.6)]
class UiUxAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un diseñador UI/UX experto. Creas la experiencia de usuario, especificas flujos conceptuales de navegación y directrices de UI basados en Tailwind CSS.';
    }
}
