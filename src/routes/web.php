<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserRequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminRequestController;



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

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/attendances/{id}', [AdminAttendanceController::class, 'show'])->name('attendances.show');
    Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests.index');
});

Route::get('/admin/users/{user}/attendances', [AdminAttendanceController::class, 'userIndex'])->name('admin.attendances.user_index');

Route::get('/admin/requests', [AdminRequestController::class, 'index'])
    ->name('admin.requests.index');

Route::get('/admin/requests/{id}', [AdminRequestController::class, 'show'])
    ->name('admin.requests.show');

Route::patch('/admin/requests/{id}/approve', [AdminRequestController::class, 'approve'])
    ->name('admin.requests.approve');

Route::get('/', function () {
    return view('welcome');
});
