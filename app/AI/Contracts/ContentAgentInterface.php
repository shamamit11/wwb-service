<?php

namespace App\AI\Contracts;

use App\AI\DTO\AgentInput;
use App\AI\DTO\AgentResult;

/**
 * @template TInput of AgentInput
 */
interface ContentAgentInterface
{
    public function name(): string;

    /**
     * @param  TInput  $input
     */
    public function run(AgentInput $input): AgentResult;
}
