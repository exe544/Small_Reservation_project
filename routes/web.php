<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\CompanyGuideController;
use App\Http\Controllers\CompanyActivityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ActivityController;

Route::get('/', HomeController::class)->name('home');
Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activity.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('companies', CompanyController::class)->middleware('isAdmin')->except('show');

    Route::resource('companies.users', CompanyUserController::class)->except('show');
    Route::resource('companies.guides', CompanyGuideController::class)->except('show');
    Route::resource('companies.activities', CompanyActivityController::class)->except('show');
});

require __DIR__.'/auth.php';
