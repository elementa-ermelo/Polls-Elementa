<?php

use App\Http\Controllers\Admin\PollController as AdminPollController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\PollController as PublicPollController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPollController::class, 'index'])->name('polls.index');
Route::get('/polls/{poll}', [PublicPollController::class, 'show'])->name('polls.show');
Route::post('/polls/{poll}/verify-access-code', [PublicPollController::class, 'verifyAccessCode'])->name('polls.verify-access-code');
Route::post('/polls/{poll}/vote', [PublicPollController::class, 'vote'])->name('polls.vote');
Route::get('/polls/confirm/{token}', [PublicPollController::class, 'confirm'])->name('polls.confirm');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

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
    Route::get('/polls/{poll}/edit', [AdminPollController::class, 'edit'])->name('polls.edit');
    Route::put('/polls/{poll}', [AdminPollController::class, 'update'])->name('polls.update');
    Route::delete('/polls/{poll}', [AdminPollController::class, 'destroy'])->name('polls.destroy');
    Route::post('/polls/{poll}/toggle-active', [AdminPollController::class, 'toggleActive'])->name('polls.toggle-active');
    Route::post('/polls/{poll}/archive', [AdminPollController::class, 'archive'])->name('polls.archive');
    Route::delete('/polls/{poll}/votes/{vote}', [AdminPollController::class, 'deleteVote'])->name('polls.votes.destroy');
    
    // Poll Questions
    Route::get('/polls/{poll}/questions/create', [AdminPollController::class, 'createQuestion'])->name('polls.questions.create');
    Route::post('/polls/{poll}/questions', [AdminPollController::class, 'storeQuestion'])->name('polls.questions.store');
    Route::get('/polls/{poll}/questions/{question}/edit', [AdminPollController::class, 'editQuestion'])->name('polls.questions.edit');
    Route::put('/polls/{poll}/questions/{question}', [AdminPollController::class, 'updateQuestion'])->name('polls.questions.update');
    Route::delete('/polls/{poll}/questions/{question}', [AdminPollController::class, 'destroyQuestion'])->name('polls.questions.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/archive', [ReportController::class, 'archive'])->name('reports.archive');
    Route::post('/reports/{poll}/reactivate', [ReportController::class, 'reactivate'])->name('reports.reactivate');
});
