<?php

// app/Ai/Agents/QaAgent.php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// Temperature removed: claude-sonnet-5 (the default model) rejects the
// `temperature` parameter with a 400 - replaced by adaptive thinking.
#[MaxTokens(2000)]
class QaAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un ingeniero de QA experto. Diseñas planes de pruebas unitarias y de integración utilizando Pest o PHPUnit para garantizar la estabilidad.';
    }
}
