<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\ReturnsPhaseOneSkeleton;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ReturnsPhaseOneSkeleton;

    public function login(): JsonResponse
    {
        return $this->phaseOneSkeleton('Admin login');
    }
}
