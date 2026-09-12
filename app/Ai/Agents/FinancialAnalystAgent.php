<?php

// app/Ai/Agents/FinancialAnalystAgent.php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// Temperature removed: claude-sonnet-5 (the default model) rejects the
// `temperature` parameter with a 400 - replaced by adaptive thinking.
#[MaxTokens(1500)]
class FinancialAnalystAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Financial Analyst experto. Tu tarea es evaluar la viabilidad financiera, estimar costos de infraestructura/desarrollo y calcular el ROI.';
    }
}
