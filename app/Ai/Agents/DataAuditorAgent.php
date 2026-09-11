<?php
// app/Ai/Agents/DataAuditorAgent.php
namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;

#[MaxTokens(1500)]
#[Temperature(0.3)]
class DataAuditorAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Data Auditor experto. Tu rol es verificar la integridad del flujo final, auditar transacciones simuladas y asegurar normativas de privacidad.';
    }
}
