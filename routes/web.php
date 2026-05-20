<?php

use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BatchController as AdminBatchController;
use App\Http\Controllers\Admin\CodeController as AdminCodeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\InterviewController as AdminInterviewController;
use App\Http\Controllers\Admin\ParticipantController as AdminParticipantController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ScoringSettingsController as AdminScoringSettingsController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Controllers\ParticipantAssessmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::post('/code/validate', [ParticipantAssessmentController::class, 'validateCode'])
    ->name('code.validate');

Route::get('/assessment/instructions', [ParticipantAssessmentController::class, 'instructions'])
    ->name('assessment.instructions');
Route::post('/assessment/start', [ParticipantAssessmentController::class, 'start'])
    ->name('assessment.start');
Route::get('/assessment/questions/{order}', [ParticipantAssessmentController::class, 'showQuestion'])
    ->whereNumber('order')
    ->name('assessment.questions.show');
Route::post('/assessment/questions/{order}', [ParticipantAssessmentController::class, 'answerQuestion'])
    ->whereNumber('order')
    ->name('assessment.questions.answer');
Route::get('/assessment/review', [ParticipantAssessmentController::class, 'review'])
    ->name('assessment.review');
Route::post('/assessment/submit', [ParticipantAssessmentController::class, 'submit'])
    ->name('assessment.submit');
Route::get('/assessment/completion', [ParticipantAssessmentController::class, 'completion'])
    ->name('assessment.completion');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');

    Route::middleware(EnsureAdminAuthenticated::class)->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/questions', [AdminQuestionController::class, 'index'])->name('questions.index');
        Route::get('/batches', [AdminBatchController::class, 'index'])->name('batches.index');
        Route::post('/codes', [AdminCodeController::class, 'store'])->name('codes.store');
        Route::post('/codes/batch', [AdminCodeController::class, 'batchStore'])->name('codes.batch-store');
        Route::patch('/codes/{code}/reset', [AdminCodeController::class, 'reset'])->name('codes.reset');
        Route::patch('/codes/{code}/lock', [AdminCodeController::class, 'lock'])->name('codes.lock');
        Route::patch('/codes/{code}/unlock', [AdminCodeController::class, 'unlock'])->name('codes.unlock');
        Route::get('/participants/{participant}', [AdminParticipantController::class, 'show'])->name('participants.show');
        Route::post('/participants/{participant}/notes', [AdminParticipantController::class, 'storeNote'])->name('participants.notes.store');
        Route::post('/participants/{participant}/interviews', [AdminInterviewController::class, 'store'])->name('participants.interviews.store');
        Route::patch('/participants/{participant}/final-status', [AdminParticipantController::class, 'updateFinalStatus'])->name('participants.final-status.update');
        Route::get('/participants/{participant}/report', [AdminReportController::class, 'show'])->name('participants.report');
        Route::get('/participants/{participant}/report.md', [AdminReportController::class, 'markdown'])->name('participants.report.markdown');
        Route::get('/scoring-settings', [AdminScoringSettingsController::class, 'edit'])->name('scoring-settings.edit');
        Route::patch('/scoring-settings', [AdminScoringSettingsController::class, 'update'])->name('scoring-settings.update');
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/export/results.csv', [AdminExportController::class, 'results'])->name('export.results');
    });
});
