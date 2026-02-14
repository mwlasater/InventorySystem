<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\BulkItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (or login if not authenticated)
Route::get('/', fn () => redirect()->route('dashboard'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Force password change (exempt from ForcePasswordChange middleware)
    Route::get('password/change', [ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::put('password/change', [ForcePasswordChangeController::class, 'update'])->name('password.force-change.update');

    // All other authenticated routes require active account + no forced password change
    Route::middleware(['check.active', 'force.password'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Tags
        Route::resource('tags', TagController::class)->only(['index', 'update', 'destroy']);
        Route::post('tags/merge', [TagController::class, 'merge'])->name('tags.merge');

        // Tag API (JSON endpoints for Alpine.js)
        Route::get('api/tags/search', [\App\Http\Controllers\Api\TagController::class, 'search'])->name('api.tags.search');
        Route::post('api/tags', [\App\Http\Controllers\Api\TagController::class, 'store'])->name('api.tags.store');

        // Locations
        Route::resource('locations', LocationController::class)->except(['show']);
        Route::post('locations/order', [LocationController::class, 'updateOrder'])->name('locations.order');

        // Inventory Items
        Route::resource('items', ItemController::class);
        Route::post('items/{item}/favorite', [ItemController::class, 'toggleFavorite'])->name('items.favorite');

        // Trash (soft-deleted items)
        Route::get('trash', [TrashController::class, 'index'])->name('trash.index');
        Route::post('trash/{item}/restore', [TrashController::class, 'restore'])->name('trash.restore');
        Route::delete('trash/{item}', [TrashController::class, 'forceDelete'])->name('trash.forceDelete');

        // Bulk operations
        Route::post('items-bulk', [BulkItemController::class, 'update'])->name('items.bulk');

        // Admin routes
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', UserController::class)->except(['show', 'destroy']);
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
            Route::get('users/{user}/activity', [UserController::class, 'activityLog'])->name('users.activity');
        });
    });
});
