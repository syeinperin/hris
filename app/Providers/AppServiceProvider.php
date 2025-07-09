<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use App\Models\Sidebar;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // 1) Use Bootstrap 5 styling for paginators
        Paginator::useBootstrap();

        // 2) Sidebar menu items (only when authenticated)
        View::composer(
            ['partials.sidebar'],
            function ($view) {
                if (! auth()->check()) {
                    $view->with('menuItems', collect());
                    return;
                }

                $user = auth()->user();
                $slug = strtolower($user->role->name);

                $menus = Sidebar::forRole($slug)
                    ->whereNull('parent_id')
                    ->orderBy('order')
                    ->with(['children' => function ($q) use ($slug) {
                        $q->forRole($slug)->orderBy('order');
                    }])
                    ->get();

                $view->with('menuItems', $menus);
            }
        );

        // 3) Share the logged‐in user’s employee_code in every view
        View::composer('*', function ($view) {
            $view->with(
                'myEmployeeCode',
                optional(Auth::user()?->employee)->employee_code
            );
        });
    }
}