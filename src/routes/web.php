<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserRequestController;




Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.post');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');

Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendances.index');
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendances.store');

Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendances.list');

Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');

Route::get('/stamp_correction_request/list', [UserRequestController::class, 'index'])->name('request.list');


Route::get('/', function () {
    return view('welcome');
});
