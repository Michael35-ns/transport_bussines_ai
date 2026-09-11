<?php
// app/Ai/Agents/DataArchitectAgent.php
namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;

#[MaxTokens(1500)]
#[Temperature(0.5)]
class DataArchitectAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Data Architect experto. Tu responsabilidad es diseñar la estructura de datos óptima y proponer diagramas entidad-relación (DER).';
    }
}

