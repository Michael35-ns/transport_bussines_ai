<?php

// app/Ai/Agents/LaravelArchitectAgent.php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// Temperature removed: claude-sonnet-5 (the default model) rejects the
// `temperature` parameter with a 400 - replaced by adaptive thinking.
#[MaxTokens(2000)]
class LaravelArchitectAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Laravel Architect experto. Diseñas la arquitectura técnica en Laravel, definiendo patrones de diseño, uso de Jobs, Events y Service Providers.';
    }
}
