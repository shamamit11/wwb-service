<?php

namespace App\Modules\Shared\Data;

abstract readonly class DataTransferObject
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
