<?php

namespace App\AI\DTO;

use App\AI\Enums\AiRunStatus;
use App\Infrastructure\Ai\Data\AiUsageData;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class AgentResult extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|string|null  $rawResponse
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $agent,
        public AiRunStatus $status,
        public array|string|null $rawResponse = null,
        public ?AgentOutput $parsedResponse = null,
        public ?AiUsageData $usage = null,
        public ?AgentErrorData $error = null,
        public ?string $provider = null,
        public ?string $model = null,
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>|string|null  $rawResponse
     * @param  array<string, mixed>  $metadata
     */
    public static function success(
        string $agent,
        array|string|null $rawResponse,
        ?AgentOutput $parsedResponse = null,
        ?AiUsageData $usage = null,
        ?string $provider = null,
        ?string $model = null,
        array $metadata = [],
    ): self {
        return new self(
            agent: $agent,
            status: AiRunStatus::SUCCESS,
            rawResponse: $rawResponse,
            parsedResponse: $parsedResponse,
            usage: $usage,
            provider: $provider,
            model: $model,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>|string|null  $rawResponse
     * @param  array<string, mixed>  $metadata
     */
    public static function partial(
        string $agent,
        array|string|null $rawResponse,
        AgentErrorData $error,
        ?AgentOutput $parsedResponse = null,
        ?AiUsageData $usage = null,
        ?string $provider = null,
        ?string $model = null,
        array $metadata = [],
    ): self {
        return new self(
            agent: $agent,
            status: AiRunStatus::PARTIAL,
            rawResponse: $rawResponse,
            parsedResponse: $parsedResponse,
            usage: $usage,
            error: $error,
            provider: $provider,
            model: $model,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>|string|null  $rawResponse
     * @param  array<string, mixed>  $metadata
     */
    public static function failed(
        string $agent,
        AgentErrorData $error,
        array|string|null $rawResponse = null,
        ?AiUsageData $usage = null,
        ?string $provider = null,
        ?string $model = null,
        array $metadata = [],
    ): self {
        return new self(
            agent: $agent,
            status: AiRunStatus::FAILED,
            rawResponse: $rawResponse,
            usage: $usage,
            error: $error,
            provider: $provider,
            model: $model,
            metadata: $metadata,
        );
    }

    public function isSuccessful(): bool
    {
        return $this->status === AiRunStatus::SUCCESS;
    }

    public function isFailure(): bool
    {
        return $this->status === AiRunStatus::FAILED;
    }

    public function isPartial(): bool
    {
        return $this->status === AiRunStatus::PARTIAL;
    }
}
