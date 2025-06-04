<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;              // ← make sure you import your User model

class AuditLogController extends Controller
{
    /**
     * Display a paginated list of users’ last_login timestamps.
     */
    public function index(Request $request)
    {
        $logs = User::select('id','name','email','last_login')
                    ->whereNotNull('last_login')
                    ->orderBy('last_login','desc')
                    ->paginate(15);

        return view('audit-logs.index', compact('logs'));
    }
}
