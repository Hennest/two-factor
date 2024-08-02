<?php

declare(strict_types=1);

use Hennest\TwoFactor\Http\Middleware\RedirectIfTwoFactorAuthenticatable;
use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => 'hello')->name('login');
Route::post('/login', fn () => 'hello')
    ->middleware(
        middleware: RedirectIfTwoFactorAuthenticatable::class
    );
Route::get('/dashboard', fn () => 'dashboard')->name('dashboard');
