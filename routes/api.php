<?php

use App\Http\Controllers\Api\V1\BarcodeController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * Token-authenticated API (Laravel Sanctum). Every route requires a valid
 * personal access token (Authorization: Bearer <token>) and an active account.
 * Tokens inherit their user's access, so the user's role/status governs.
 */
Route::middleware(['auth:sanctum', 'api.active', 'throttle:api'])->prefix('v1')->name('api.v1.')->group(function () {
    Route::get('user', fn (Request $request) => $request->user()->only(['id', 'username', 'display_name', 'email', 'role']))
        ->name('user');

    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    Route::get('items/{item}', [ItemController::class, 'show'])->name('items.show');

    Route::get('search', SearchController::class)->name('search');
    Route::get('barcode-lookup', BarcodeController::class)->name('barcode-lookup');
    Route::get('tags', [TagController::class, 'index'])->name('tags.index');
});
