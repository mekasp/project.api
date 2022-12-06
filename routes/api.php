<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\API\AuthController;
use \App\Http\Controllers\API\UserController;
use \App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\LabelController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/token/create', [AuthController::class, 'createToken']);

Route::post('/users', [UserController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{id}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::get('/users', [UserController::class, 'show']);
    Route::put('/users', [UserController::class, 'update']);
    Route::delete('/users', [UserController::class, 'destroy']);

    Route::post('/projects', [ProjectController::class, 'store']);
    Route::post('/projects/{id}', [ProjectController::class, 'sync']);
    Route::get('/projects', [ProjectController::class, 'show']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    Route::post('/labels', [LabelController::class, 'store']);
    Route::post('/labels/{id}', [LabelController::class, 'sync']);
    Route::get('/labels', [LabelController::class, 'show']);
    Route::delete('/labels/{label}', [LabelController::class, 'delete']);
});

