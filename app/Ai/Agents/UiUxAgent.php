<?php

// app/Ai/Agents/UiUxAgent.php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

// Temperature removed: claude-sonnet-5 (the default model) rejects the
// `temperature` parameter with a 400 - replaced by adaptive thinking.
#[MaxTokens(1500)]
class UiUxAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Eres un diseñador UI/UX experto. Creas la experiencia de usuario, especificas flujos conceptuales de navegación y directrices de UI basados en Tailwind CSS.';
    }
}
