<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\ReturnsPhaseOneSkeleton;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    use ReturnsPhaseOneSkeleton;

    public function index(): JsonResponse
    {
        return $this->phaseOneSkeleton('Admin audit logs');
    }
}
