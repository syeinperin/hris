<?php

namespace App\Http\Controllers;
use App\Models\Role;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function show(Role $role) {
    return view('roles.show', compact('role'));
}
}
