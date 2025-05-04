<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Models\Sidebar;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // 1) Use Bootstrap 5 styling for all paginator links
        Paginator::useBootstrap();

        // 2) Compose menuItems for sidebar—but only if the user is logged in
        View::composer(
            // target any view that includes the sidebar partial
            ['partials.sidebar'],
            function ($view) {
                // if no user, send an empty collection
                if (! auth()->check()) {
                    $view->with('menuItems', collect());
                    return;
                }

                $user = auth()->user();
                // normalize role slug
                $roleName = $user->role->name ?? (string) $user->role;
                $slug     = strtolower($roleName);

                // fetch top-level menu items for this role, eagerly loading children
                $menus = Sidebar::forRole($slug)
                    ->whereNull('parent_id')
                    ->orderBy('order')
                    ->with([
                        'children' => function ($q) use ($slug) {
                            $q->forRole($slug)
                              ->orderBy('order');
                        }
                    ])
                    ->get();

                $view->with('menuItems', $menus);
            }
        );
    }
}
