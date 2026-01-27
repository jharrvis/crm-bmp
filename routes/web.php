<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data: Organization Structure (Owner & Admin only)
    Route::middleware(['role:Owner|Admin'])->group(function () {
        Route::resource('branches', \App\Http\Controllers\BranchController::class);
        Route::resource('divisions', \App\Http\Controllers\DivisionController::class);
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    });
});

require __DIR__ . '/auth.php';
