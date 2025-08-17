<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::query()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'body'  => ['required','string'],
            'image' => ['nullable','image','max:2048'],
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('announcements', 'public')
            : null;

        Announcement::create([
            'title'        => $data['title'],
            'body'         => $data['body'],
            'image_path'   => $imagePath,
            'published_at' => now(),
            'created_by'   => auth()->id(),
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement published.');
    }

    public function show(Request $request, Announcement $announcement)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'title'        => $announcement->title,
                'body'         => $announcement->body,
                // ✅ use the public files route so everyone can view
                'image_url'    => $announcement->image_path
                                  ? route('public.files', ['path' => $announcement->image_path])
                                  : null,
                'published_at' => ($announcement->published_at ?? $announcement->created_at)->format('Y-m-d H:i'),
            ]);
        }

        return view('announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'        => ['required','string','max:255'],
            'body'         => ['required','string'],
            'image'        => ['nullable','image','max:2048'],
            'remove_image' => ['nullable','boolean'],
        ]);

        if (($request->boolean('remove_image') || $request->hasFile('image')) && $announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
            $announcement->image_path = null;
        }

        if ($request->hasFile('image')) {
            $announcement->image_path = $request->file('image')->store('announcements', 'public');
        }

        $announcement->update([
            'title' => $data['title'],
            'body'  => $data['body'],
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted.');
    }
}
