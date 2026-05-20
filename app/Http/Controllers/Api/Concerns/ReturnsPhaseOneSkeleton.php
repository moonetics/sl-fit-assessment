<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;

trait ReturnsPhaseOneSkeleton
{
    protected function phaseOneSkeleton(string $feature): JsonResponse
    {
        return response()->json([
            'message' => "{$feature} endpoint is registered for Phase 1 and will be implemented in a later phase.",
        ], 501);
    }
}
