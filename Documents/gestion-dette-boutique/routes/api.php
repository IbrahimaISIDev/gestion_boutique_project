<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Vos autres routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Routes existantes pour les articles
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::apiResource('/clients', ClientController::class)->only(['index', 'store', 'show']);

Route::apiResource('/articles', ArticleController::class);
    Route::get('/articles/trashed', [ArticleController::class, 'trashed']);
    Route::patch('/articles/{id}/restore', [ArticleController::class, 'restore']);
    Route::delete('/articles/{id}/force-delete', [ArticleController::class, 'forceDelete']);
    Route::post('/articles/stock', [ArticleController::class, 'updateMultiple']);
});
