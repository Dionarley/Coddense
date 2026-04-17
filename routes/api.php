<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/repositories', [ApiController::class, 'repositories']);
Route::post('/repositories', [ApiController::class, 'storeRepository']);
Route::get('/repositories/{repository}', [ApiController::class, 'showRepository']);
Route::delete('/repositories/{repository}', [ApiController::class, 'destroyRepository']);
Route::get('/repositories/{repository}/entities', [ApiController::class, 'entities']);
Route::get('/repositories/{repository}/vulnerabilities', [ApiController::class, 'vulnerabilities']);
Route::get('/repositories/{repository}/technical-debt', [ApiController::class, 'technicalDebt']);
Route::post('/repositories/{repository}/reprocess', [ApiController::class, 'reprocess']);
Route::get('/stats', [ApiController::class, 'stats']);
