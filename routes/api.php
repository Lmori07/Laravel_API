<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\V1\CompleteTaskController;
use App\Http\Controllers\API\V1\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//prefix routers version 1 for API.
Route::prefix('v1')->group(function () {
    Route::apiResource('/tasks', TaskController::class);
    Route::patch('/tasks/{task}/complete',CompleteTaskController::class );
});

//prefix routers for authentication
Route::prefix('auth')->group(function () {
   Route::post('/login', LoginController::class);
});
