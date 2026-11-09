<?php

use App\Http\Controllers\Admin\PollController as AdminPollController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\PollController as PublicPollController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPollController::class, 'index'])->name('polls.index');
Route::get('/polls/{poll}', [PublicPollController::class, 'show'])->name('polls.show');
// Remmen per IP-adres: tegen code raden en tegen het misbruiken van het
// stemformulier om bevestigingsmails naar willekeurige adressen te sturen.
Route::post('/polls/{poll}/verify-access-code', [PublicPollController::class, 'verifyAccessCode'])
    ->middleware('throttle:10,1')->name('polls.verify-access-code');
Route::post('/polls/{poll}/vote', [PublicPollController::class, 'vote'])
    ->middleware('throttle:10,1')->name('polls.vote');
Route::get('/polls/confirm/{token}', [PublicPollController::class, 'confirm'])
    ->middleware('throttle:30,1')->name('polls.confirm');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Zelf registreren staat uit (9 nov 2026): accounts maakt een admin aan via
// Beheer > Gebruikers. Een open registratie gaf vreemden een poll op ons domein
// en een manier om via onze mailserver mail te laten versturen.

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function (): void {
    Route::resource('users', UserController::class);

    Route::get('/polls', [AdminPollController::class, 'index'])->name('polls.index');
    Route::post('/polls/quick-create', [AdminPollController::class, 'quickCreate'])->name('polls.quick-create');
    Route::get('/polls/create', [AdminPollController::class, 'create'])->name('polls.create');
    Route::post('/polls', [AdminPollController::class, 'store'])->name('polls.store');
    Route::get('/polls/{poll}', [AdminPollController::class, 'show'])->name('polls.show');
    Route::get('/polls/{poll}/send-email', [AdminPollController::class, 'sendEmailForm'])->name('polls.send-email-form');
    Route::post('/polls/{poll}/send-email', [AdminPollController::class, 'sendEmail'])->name('polls.send-email');
    Route::get('/polls/{poll}/edit', [AdminPollController::class, 'edit'])->name('polls.edit');
    Route::put('/polls/{poll}', [AdminPollController::class, 'update'])->name('polls.update');
    Route::delete('/polls/{poll}', [AdminPollController::class, 'destroy'])->name('polls.destroy');
    Route::post('/polls/{poll}/toggle-active', [AdminPollController::class, 'toggleActive'])->name('polls.toggle-active');
    Route::post('/polls/{poll}/archive', [AdminPollController::class, 'archive'])->name('polls.archive');
    Route::get('/polls/{poll}/download-pdf', [AdminPollController::class, 'downloadPdf'])->name('polls.download-pdf');
    Route::get('/polls/{poll}/export-csv', [AdminPollController::class, 'exportCsv'])->name('polls.export-csv');
    Route::delete('/polls/{poll}/votes/{vote}', [AdminPollController::class, 'deleteVote'])->name('polls.votes.destroy');
    
    // Poll Questions
    Route::get('/polls/{poll}/questions/create', [AdminPollController::class, 'createQuestion'])->name('polls.questions.create');
    Route::post('/polls/{poll}/questions', [AdminPollController::class, 'storeQuestion'])->name('polls.questions.store');
    Route::get('/polls/{poll}/questions/{question}/edit', [AdminPollController::class, 'editQuestion'])->name('polls.questions.edit');
    Route::put('/polls/{poll}/questions/{question}', [AdminPollController::class, 'updateQuestion'])->name('polls.questions.update');
    Route::delete('/polls/{poll}/questions/{question}', [AdminPollController::class, 'destroyQuestion'])->name('polls.questions.destroy');
    Route::post('/polls/{poll}/questions/reorder', [AdminPollController::class, 'reorderQuestions'])->name('polls.questions.reorder');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/archive', [ReportController::class, 'archive'])->name('reports.archive');
    Route::post('/reports/{poll}/reactivate', [ReportController::class, 'reactivate'])->name('reports.reactivate');
});
