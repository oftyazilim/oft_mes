<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Customize reset password URL for SPA frontend
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $base = env('FRONTEND_URL', config('app.url'));
            $path = env('FRONTEND_PASSWORD_RESET_PATH', '/reset-password');
            // Ensure no trailing slash duplication
            $base = rtrim($base ?? '', '/');
            $path = '/' . ltrim($path, '/');
            $email = urlencode($user->email);
            $tokenParam = urlencode($token);
            // Example: https://frontend/reset-password?token=...&email=...
            return "$base$path?token=$tokenParam&email=$email";
        });
    }
}
