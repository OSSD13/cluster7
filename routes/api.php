<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MinorCaseController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Minor Cases API Routes (accessible without authentication for simplicity)
Route::prefix('minor-cases')->group(function () {
    Route::get('/', [MinorCaseController::class, 'index']);
    Route::post('/', [MinorCaseController::class, 'store']);
    Route::put('/{id}', [MinorCaseController::class, 'update']);
    Route::delete('/{id}', [MinorCaseController::class, 'destroy']);
});

// Add a route to get available boards
Route::get('/boards', function () {
    $trelloService = app(App\Services\TrelloService::class);

    try {
        $boards = $trelloService->getBoards(['id', 'name']);
        return response()->json($boards);
    } catch (\Exception $e) {
        return response()->json([]);
    }
});
