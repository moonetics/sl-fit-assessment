<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function show(Participant $participant): View
    {
        $participant->load(['accessCode', 'result', 'notes.admin', 'interviews.admin']);

        return view('admin.reports.show', [
            'participant' => $participant,
        ]);
    }

    public function markdown(Participant $participant): Response
    {
        $participant->load(['accessCode', 'result']);
        $result = $participant->result;

        $markdown = view('admin.reports.markdown', [
            'participant' => $participant,
            'result' => $result,
        ])->render();

        return response($markdown, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
        ]);
    }
}
