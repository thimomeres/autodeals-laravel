<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tamu (belum login) yang akses /dashboard, /infentory, dll. → dialihkan ke login
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Admin yang sudah login tidak boleh buka halaman login lagi
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        $middleware->validateCsrfTokens(except: [
            'api/submit-offer',
        ]);

        $middleware->alias([
            'offer.api' => \App\Http\Middleware\VerifyOfferApiKey::class,
            'owner' => \App\Http\Middleware\EnsureOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();





































