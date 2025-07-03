<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // … (global & group middleware omitted for brevity) …

    /**
     * Route‐specific middleware.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth'               => \App\Http\Middleware\Authenticate::class,
        'auth.basic'         => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers'      => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'                => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'              => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm'   => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'             => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'           => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'           => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'role'               => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,

        // ← Add this line to enforce only active users can access protected routes:
        'active'             => \App\Http\Middleware\EnsureUserIsActive::class,
    ];
}
