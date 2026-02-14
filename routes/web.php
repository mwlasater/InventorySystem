<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\BulkItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemDocumentController;
use App\Http\Controllers\ItemPhotoController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\QrLabelController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedFilterController;
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

        // Item Photos
        Route::post('items/{item}/photos', [ItemPhotoController::class, 'store'])->name('items.photos.store');
        Route::delete('items/{item}/photos/{photo}', [ItemPhotoController::class, 'destroy'])->name('items.photos.destroy');
        Route::post('items/{item}/photos/{photo}/primary', [ItemPhotoController::class, 'setPrimary'])->name('items.photos.primary');
        Route::post('items/{item}/photos/reorder', [ItemPhotoController::class, 'reorder'])->name('items.photos.reorder');
        Route::put('items/{item}/photos/{photo}/caption', [ItemPhotoController::class, 'updateCaption'])->name('items.photos.caption');

        // Item Documents
        Route::post('items/{item}/documents', [ItemDocumentController::class, 'store'])->name('items.documents.store');
        Route::get('items/{item}/documents/{document}/download', [ItemDocumentController::class, 'download'])->name('items.documents.download');
        Route::delete('items/{item}/documents/{document}', [ItemDocumentController::class, 'destroy'])->name('items.documents.destroy');

        // Transactions
        Route::get('items/{item}/transactions/create', [TransactionController::class, 'create'])->name('items.transactions.create');
        Route::post('items/{item}/transactions', [TransactionController::class, 'store'])->name('items.transactions.store');

        // QR Labels
        Route::get('items/{item}/qr-label', [QrLabelController::class, 'show'])->name('items.qr-label');
        Route::post('items/qr-batch', [QrLabelController::class, 'batchPrint'])->name('items.qr-batch');

        // Barcode API
        Route::get('api/barcode/lookup', [\App\Http\Controllers\Api\BarcodeLookupController::class, 'lookup'])->name('api.barcode.lookup');

        // Search API
        Route::get('api/search', [\App\Http\Controllers\Api\SearchController::class, 'search'])->name('api.search');

        // Saved Filters API
        Route::get('api/filters', [SavedFilterController::class, 'index'])->name('api.filters.index');
        Route::post('api/filters', [SavedFilterController::class, 'store'])->name('api.filters.store');
        Route::delete('api/filters/{filter}', [SavedFilterController::class, 'destroy'])->name('api.filters.destroy');

        // Dashboard chart data API
        Route::get('api/dashboard/charts', [DashboardController::class, 'chartData'])->name('api.dashboard.charts');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/collection-summary', [ReportController::class, 'collectionSummary'])->name('reports.collection-summary');
        Route::get('reports/valuation', [ReportController::class, 'valuationReport'])->name('reports.valuation');
        Route::get('reports/transactions', [ReportController::class, 'transactionReport'])->name('reports.transactions');
        Route::get('reports/location-inventory', [ReportController::class, 'locationInventory'])->name('reports.location-inventory');
        Route::get('reports/status-breakdown', [ReportController::class, 'statusBreakdown'])->name('reports.status-breakdown');
        Route::get('reports/acquisition-history', [ReportController::class, 'acquisitionHistory'])->name('reports.acquisition-history');

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
