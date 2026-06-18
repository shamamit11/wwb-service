<?php

namespace App\AI\DTO;

use App\Modules\Shared\Data\DataTransferObject;
use Throwable;

final readonly class AgentErrorData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public string $message,
        public string $type,
        public int|string|null $code = null,
        public array $context = [],
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public static function fromThrowable(Throwable $throwable, array $context = []): self
    {
        $code = $throwable->getCode();

        return new self(
            message: $throwable->getMessage(),
            type: $throwable::class,
            code: is_int($code) || is_string($code) ? $code : null,
            context: $context,
        );
    }
}
