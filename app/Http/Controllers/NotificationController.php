<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Discipline\InfractionReport;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
                              ->notifications()
                              ->latest()
                              ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function show($id)
    {
        $note = auth()->user()
                     ->notifications()
                     ->findOrFail($id);

        if (! $note->read_at) {
            $note->markAsRead();
        }

        $data = $note->data;

        if (Arr::has($data, 'infraction_id')) {
            $infraction = InfractionReport::with('actions.type')
                                          ->findOrFail($data['infraction_id']);

            return view('notifications.show', compact('note', 'infraction'));
        }

        // fallback to any URL in your payload (or back to index)
        $fallback = Arr::get($data, 'url', route('notifications.index'));
        return redirect($fallback);
    }

    public function markRead($id)
    {
        $note = auth()->user()
                     ->notifications()
                     ->findOrFail($id);

        $note->markAsRead();

        return back();
    }

    public function markAllRead()
    {
        auth()->user()
             ->unreadNotifications
             ->markAsRead();

        return back();
    }
}
