<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Responses\FortifyLoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterViewResponse::class,
            \App\Http\Responses\FortifyRegisterViewResponse::class
        );

        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\FortifyLoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::ignoreRoutes();


        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::lower($request->input('email')) . '|' . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Event::listen(Registered::class, function () {
            session(['url.intended' => '/attendance']);
        });

        Fortify::loginView(function (Request $request) {
            if ($request->is('admin/login')) {
                return view('admin.login'); // 管理者用ログイン画面
            }
            return view('items.login'); // 一般ユーザー用ログイン画面
        });

        Fortify::registerView(function () {
            return view('items.register');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        Route::middleware(['web', 'auth'])
            ->prefix('email')
            ->group(function () {
                Route::get('/verify', [EmailVerificationPromptController::class, '__invoke'])
                    ->name('verification.notice');

                Route::get('/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                    ->middleware(['signed'])
                    ->name('verification.verify');

                Route::post('/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                    ->middleware(['throttle:6,1'])
                    ->name('verification.send');
            });
    }
}
