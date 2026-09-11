<?php
// app/Ai/Agents/QaAgent.php
namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;

#[MaxTokens(2000)]
#[Temperature(0.4)]
class QaAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un ingeniero de QA experto. Diseñas planes de pruebas unitarias y de integración utilizando Pest o PHPUnit para garantizar la estabilidad.';
    }
}
