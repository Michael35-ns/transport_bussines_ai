<?php

// app/Ai/Agents/DataAuditorAgent.php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// Temperature removed: claude-sonnet-5 (the default model) rejects the
// `temperature` parameter with a 400 - replaced by adaptive thinking.
#[MaxTokens(1500)]
class DataAuditorAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Data Auditor experto. Tu rol es verificar la integridad del flujo final, auditar transacciones simuladas y asegurar normativas de privacidad.';
    }
}
