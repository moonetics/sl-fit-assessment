<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\ReturnsPhaseOneSkeleton;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    use ReturnsPhaseOneSkeleton;

    public function results(): JsonResponse
    {
        return $this->phaseOneSkeleton('Admin results export');
    }
}
