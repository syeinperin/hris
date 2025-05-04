<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sidebar;

class SidebarDebugController extends Controller
{
    /**
     * Display debug info for sidebar role filtering.
     */
    public function index(Request $request)
    {
        // 1) Get the authenticated user
        $user = $request->user();

        // 2) Raw role value (string column or related model)
        $rawRole = is_string($user->role)
            ? $user->role
            : ($user->role->name ?? '—');

        // 3) Normalize to lowercase slug
        $slug = strtolower($rawRole);

        // 4) Pull every sidebar’s id/title/roles
        $all = Sidebar::select('id','title','roles')
            ->orderBy('order')
            ->get()
            ->map(fn($i) => [
                'id'    => $i->id,
                'title' => $i->title,
                'roles' => $i->roles,
            ]);

        // 5) Pull only those matching `forRole($slug)`
        $allowed = Sidebar::forRole($slug)
            ->select('id','title','roles')
            ->orderBy('order')
            ->get()
            ->map(fn($i) => [
                'id'    => $i->id,
                'title' => $i->title,
                'roles' => $i->roles,
            ]);

        // 6) JSON-dump everything
        return response()->json([
            'rawRole' => $rawRole,
            'slug'    => $slug,
            'all'     => $all,
            'allowed' => $allowed,
        ]);
    }
}
