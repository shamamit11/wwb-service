<?php

namespace App\Infrastructure\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

final class GenericTextAgent implements Agent
{
    use Promptable;

    public function __construct(
        private readonly string $instructions,
        private readonly ?string $providerName,
        private readonly ?string $modelName,
        private readonly int $timeoutSeconds,
    ) {}

    public function instructions(): string
    {
        return $this->instructions;
    }

    public function provider(): ?string
    {
        return $this->providerName;
    }

    public function model(): ?string
    {
        return $this->modelName;
    }

    public function timeout(): int
    {
        return $this->timeoutSeconds;
    }
}
