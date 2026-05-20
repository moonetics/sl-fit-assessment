<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\ReturnsPhaseOneSkeleton;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CodeController extends Controller
{
    use ReturnsPhaseOneSkeleton;

    public function index(): JsonResponse
    {
        return $this->phaseOneSkeleton('Admin code list');
    }

    public function store(): JsonResponse
    {
        return $this->phaseOneSkeleton('Admin code generation');
    }

    public function reset(string $id): JsonResponse
    {
        return $this->phaseOneSkeleton("Admin code reset [{$id}]");
    }

    public function lock(string $id): JsonResponse
    {
        return $this->phaseOneSkeleton("Admin code lock [{$id}]");
    }

    public function unlock(string $id): JsonResponse
    {
        return $this->phaseOneSkeleton("Admin code unlock [{$id}]");
    }
}
