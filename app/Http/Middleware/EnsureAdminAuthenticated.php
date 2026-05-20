<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->session()->get('admin_id');

        if (! $adminId || ! Admin::query()->whereKey($adminId)->exists()) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
