<?php

use App\Http\Controllers\RepositoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RepositoryController::class, 'index'])->name('dashboard');
Route::resource('repositories', RepositoryController::class)->only(['store', 'show', 'destroy']);
Route::get('/home', fn () => view('welcome'))->name('home');
