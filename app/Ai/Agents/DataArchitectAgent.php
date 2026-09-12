<?php

// app/Ai/Agents/DataArchitectAgent.php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// Temperature removed: claude-sonnet-5 (the default model) rejects the
// `temperature` parameter with a 400 - replaced by adaptive thinking.
#[MaxTokens(1500)]
class DataArchitectAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Data Architect experto. Tu responsabilidad es diseñar la estructura de datos óptima y proponer diagramas entidad-relación (DER).';
    }
}
