<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\SecurityHeadersMiddleware::class,
        \Illuminate\Http\Middleware\HandleCors::class,
    ];

    protected $middlewareGroups = [
        "web" => [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        "api" => [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            "throttle:api",
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\XSSProtectionMiddleware::class,
            \App\Http\Middleware\RoleBasedAccessControl::class,
        ],
    ];

    protected $routeMiddleware = [
        "auth" => \App\Http\Middleware\Authenticate::class,
        "throttle" => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        "rbac" => \App\Http\Middleware\RoleBasedAccessControl::class,
        "security.headers" => \App\Http\Middleware\SecurityHeadersMiddleware::class,
        "xss.protection" => \App\Http\Middleware\XSSProtectionMiddleware::class,
        "subscription.limits" => \App\Http\Middleware\CheckSubscriptionLimits::class,
    ];
}
