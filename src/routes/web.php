<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;










Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.post');


Route::get('/', function () {
    return view('welcome');
});
