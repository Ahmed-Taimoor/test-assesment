<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserProfileApiController;
use App\Http\Controllers\Api\AdminApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/
Route::post('/login', [AuthController::class, 'login'])->middleware('rate.limit');
Route::post('/register', [AuthController::class, 'register'])->middleware('rate.limit');

Route::middleware(['auth:sanctum', 'rate.limit'])->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileApiController::class, 'show']);
        Route::put('/', [UserProfileApiController::class, 'update']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [AdminApiController::class, 'index']);
        Route::get('/users/{user}', [AdminApiController::class, 'show']);
        Route::put('/users/{user}', [AdminApiController::class, 'update']);
        Route::delete('/users/{user}', [AdminApiController::class, 'destroy']);
        Route::get('/dashboard', [AdminApiController::class, 'dashboard']);
    });
});
