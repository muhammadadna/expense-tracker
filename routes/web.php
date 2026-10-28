<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FamilyController;
use App\Http\Controllers\MonthlySummaryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GoalController;
use App\Http\Middleware\EnsureUserHasFamily;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/family', [FamilyController::class, 'index'])->name('family.index');
    Route::post('/family', [FamilyController::class, 'store'])->name('family.store');
    Route::post('/family/join', [FamilyController::class, 'join'])->name('family.join');

    Route::middleware([EnsureUserHasFamily::class])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/monthly-summary', [MonthlySummaryController::class, 'index'])->name('monthly-summary.index');
        Route::get('/family/settings', [FamilyController::class, 'show'])->name('family.show');

        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{id}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{id}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

        Route::get('/category', [CategoryController::class, 'index'])->name('category.index');
        Route::get('/category/create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/category', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/category/{id}/edit', [CategoryController::class, 'edit'])->name('category.edit');
        Route::put('/category/{id}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/category/{id}', [CategoryController::class, 'destroy'])->name('category.delete');

        // Monthly Balancing & Goals Tracker
        Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
        Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
        Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
        Route::get('/goals/{id}', [GoalController::class, 'show'])->name('goals.show');
        Route::get('/goals/{id}/edit', [GoalController::class, 'edit'])->name('goals.edit');
        Route::put('/goals/{id}', [GoalController::class, 'update'])->name('goals.update');
        Route::delete('/goals/{id}', [GoalController::class, 'destroy'])->name('goals.destroy');
        Route::post('/goals/{id}/adjust', [GoalController::class, 'adjust'])->name('goals.adjust');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
