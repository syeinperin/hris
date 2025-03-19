<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        $role = Role::where('name', 'admin')->first();
        
        if (!$role) {
            return abort(404, 'Role not found');
        }

        return view('admin.index', compact('role'));
    }
}
