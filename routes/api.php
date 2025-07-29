<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\RedirectActionController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas CRUD para gerenciar links curtos
Route::apiResource('redirects', RedirectController::class)->parameters([
    'redirects' => 'redirect'
]);

// Rotas para ações específicas dos redirects
Route::get('redirects/{redirect}/stats', [RedirectActionController::class, 'stats']);
Route::get('redirects/{redirect}/logs', [RedirectActionController::class, 'logs']);
