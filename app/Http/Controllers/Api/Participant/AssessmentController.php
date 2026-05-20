<?php

namespace App\Http\Controllers\Api\Participant;

use App\Http\Controllers\Api\Concerns\ReturnsPhaseOneSkeleton;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AssessmentController extends Controller
{
    use ReturnsPhaseOneSkeleton;

    public function start(): JsonResponse
    {
        return $this->phaseOneSkeleton('Assessment start');
    }

    public function current(): JsonResponse
    {
        return $this->phaseOneSkeleton('Current assessment');
    }

    public function submit(): JsonResponse
    {
        return $this->phaseOneSkeleton('Assessment submit');
    }

    public function completion(): JsonResponse
    {
        return $this->phaseOneSkeleton('Assessment completion');
    }
}
