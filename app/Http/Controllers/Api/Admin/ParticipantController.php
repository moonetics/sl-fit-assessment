<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\ReturnsPhaseOneSkeleton;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ParticipantController extends Controller
{
    use ReturnsPhaseOneSkeleton;

    public function index(): JsonResponse
    {
        return $this->phaseOneSkeleton('Admin participant list');
    }

    public function result(string $id): JsonResponse
    {
        return $this->phaseOneSkeleton("Admin participant result [{$id}]");
    }

    public function storeNote(string $id): JsonResponse
    {
        return $this->phaseOneSkeleton("Admin participant note [{$id}]");
    }

    public function updateFinalStatus(string $id): JsonResponse
    {
        return $this->phaseOneSkeleton("Admin participant final status [{$id}]");
    }
}
