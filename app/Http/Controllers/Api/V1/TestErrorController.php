<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use RuntimeException;

class TestErrorController extends Controller
{
    public function __invoke(): never
    {
        throw new RuntimeException('Sensitive internal details should not leak.');
    }
}
