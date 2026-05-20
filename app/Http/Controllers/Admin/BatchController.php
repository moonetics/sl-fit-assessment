<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CodeBatch;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(): View
    {
        return view('admin.batches.index', [
            'batches' => CodeBatch::query()
                ->withCount('accessCodes')
                ->with(['creator', 'accessCodes' => fn ($query) => $query->orderBy('created_at')])
                ->latest()
                ->paginate(20),
        ]);
    }
}
