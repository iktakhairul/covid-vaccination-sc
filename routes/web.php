<?php

use App\Http\Controllers\Frontend\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Common Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Frontend\RegisterController;

Route::get('/', function () {
    return redirect()->route('vaccine-registration.create');
})->name('home');

Route::get('/vaccine-registration', [RegisterController::class, 'create'])->name('vaccine-registration.create');
Route::post('/vaccine-registration', [RegisterController::class, 'store'])->name('vaccine-registration.store');
Route::get('/search', [SearchController::class, 'index'])->name('search.page');
