<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        // paginate your announcements
        $announcements = Announcement::latest()->paginate(10);

        // render announcements/index.blade.php
        return view('announcements.index', compact('announcements'));
    }
}
