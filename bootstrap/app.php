<?php

use App\Http\Middleware\AppLock;
use App\Http\Middleware\CheckMember;
use App\Http\Middleware\HandleLanguage;
use App\Http\Middleware\HandleLanguagePrefix;
use App\Http\Middleware\Role;
use App\Http\Middleware\SetSettingsToConfig;
use App\Http\Middleware\SetThemeOnQuery;
use App\Http\Middleware\VerifyDelivery;
use App\Http\Middleware\VerifyInstall;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies (Nginx/Cloudflare) so Laravel detects HTTPS correctly.
        // Without this, session cookies don't get the Secure flag, causing 419 CSRF errors.
        $middleware->trustProxies(at: '*');

        $middleware->prepend(SetSettingsToConfig::class);
        $middleware->web(append: [
            SetThemeOnQuery::class,
        ]);
        $middleware->alias([
            'verify.install' => VerifyInstall::class,
            'verify.delivery' => VerifyDelivery::class,
            'role' => Role::class,
            'handle.language' => HandleLanguage::class,
            'handle.language.prefix' => HandleLanguagePrefix::class,
            'app.lock' => AppLock::class,
            'check.member' => CheckMember::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
