<?php

namespace App\Http\Controllers\Api\Participant;

use App\Http\Controllers\Api\Concerns\ReturnsPhaseOneSkeleton;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CodeValidationController extends Controller
{
    use ReturnsPhaseOneSkeleton;

    public function store(): JsonResponse
    {
        return $this->phaseOneSkeleton('Code validation');
    }
}
