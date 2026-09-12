<?php

namespace App\Ai\Agents; // O App\Agents según tu ruta

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// Temperature removed: claude-sonnet-5 (the default model) rejects the
// `temperature` parameter with a 400 - it's been replaced by adaptive
// thinking on the current model generation.
#[MaxTokens(1500)]
class BusinessAnalystAgent implements Agent
{
    use Promptable; // <-- Este Trait provee los métodos prompt, stream, queue...

    /**
     * Define las instrucciones del sistema para este agente.
     */
    public function instructions(): string
    {
        return 'Eres un Business Analyst experto. Tu objetivo es analizar los requisitos del negocio, definir objetivos claros del proyecto y documentar los casos de uso.';
    }
}
