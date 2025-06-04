<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);
        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($file = $request->file('image')) {
            // store under storage/app/public/announcements
            $data['image_path'] = $file->store('announcements', 'public');
        }

        $data['created_by'] = auth()->id();
        Announcement::create($data);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement published.');
    }

    public function show(Announcement $announcement)
    {
        return view('announcements.show', compact('announcement'));
    }
}
