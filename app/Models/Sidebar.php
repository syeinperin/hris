<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Sidebar extends Model
{
    // Fillable columns
    protected $fillable = [
        'title',
        'route',
        'icon',
        'parent_id',
        'order',
        'roles',
    ];

    // Cast roles JSON → array
    protected $casts = [
        'roles' => 'array',
    ];

    /**
     * Scope to filter by a given role name.
     */
    public function scopeForRole(Builder $query, string $roleName): Builder
    {
        return $query->whereJsonContains('roles', $roleName);
    }

    /**
     * Nested children of a sidebar item.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
                    ->orderBy('order');
    }

    /**
     * Grab all items the current user should see, grouped by parent_id.
     */
    public static function forCurrentUser()
    {
        $user = Auth::user();
        $roleName = $user && $user->role ? $user->role->name : null;

        $items = self::query()
            ->when($roleName, fn($q) => $q->forRole($roleName))
            ->orderBy('order')
            ->get();

        return $items->groupBy('parent_id');
    }
}
