<?php

namespace App\Http;

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {
    protected $routeMiddleware = [
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];
    
}