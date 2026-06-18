<?php

namespace App\AI\Enums;

enum AiRunStatus: string
{
    case SUCCESS = 'success';
    case PARTIAL = 'partial';
    case FAILED = 'failed';
}
