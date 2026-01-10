<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\V1\CompleteTaskController;
use App\Http\Controllers\API\V1\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// prefix routers version 1 for API.
Route::prefix('v1')->group(base_path('routes/API/V1_no_authenticated.php'));
Route::prefix('v2')->middleware('auth:sanctum')->group(base_path('routes/API/V2_authenticated.php'));

// prefix routers for authentication
Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class);
    Route::post('/register', RegisterController::class);
    Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
});
