<?php

// app/Ai/Agents/DeveloperAgent.php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// Temperature removed: claude-sonnet-5 (the default model) rejects the
// `temperature` parameter with a 400 - replaced by adaptive thinking.
#[MaxTokens(3000)]
class DeveloperAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un Laravel Developer experto. Traduces las especificaciones técnicas en código PHP limpio, usando buenas prácticas, validaciones y Eloquent.';
    }
}
