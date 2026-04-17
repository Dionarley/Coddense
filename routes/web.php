<?php

use App\Http\Controllers\RepositoryController;
use App\Jobs\ProcessRepositoryJob;
use App\Models\Repository;
use Illuminate\Support\Facades\Route;

Route::get('/', [RepositoryController::class, 'index'])->name('dashboard');
Route::resource('repositories', RepositoryController::class)->only(['store', 'show', 'destroy']);
Route::post('/repositories/{repository}/reprocess', function (Repository $repository) {
    $repository->codeEntities()->delete();
    $repository->update(['status' => 'pending']);
    ProcessRepositoryJob::dispatch($repository->id);

    return back()->with('success', 'Repositório será reprocessado!');
});
Route::get('/home', fn () => view('welcome'))->name('home');
