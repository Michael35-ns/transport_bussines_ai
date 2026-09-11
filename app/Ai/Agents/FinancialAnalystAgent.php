<?php
// app/Ai/Agents/FinancialAnalystAgent.php
namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;

#[MaxTokens(1500)]
#[Temperature(0.3)]
class FinancialAnalystAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Financial Analyst experto. Tu tarea es evaluar la viabilidad financiera, estimar costos de infraestructura/desarrollo y calcular el ROI.';
    }
}
