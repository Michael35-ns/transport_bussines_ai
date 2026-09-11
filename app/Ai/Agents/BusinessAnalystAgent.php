<?php

namespace App\Ai\Agents; // O App\Agents según tu ruta

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;

#[MaxTokens(1500)]
#[Temperature(0.5)]
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
