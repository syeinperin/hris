<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display a listing of holidays.
     */
    public function index()
    {
        $holidays = Holiday::orderBy('date')->get();
        return view('holidays.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create()
    {
        return view('holidays.form', ['holiday' => new Holiday]);
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'date'         => 'required|date|unique:holidays,date',
            'type'         => 'required|in:regular,special',
            // allow checkbox values 0 or 1
            'is_recurring' => 'sometimes|in:0,1',
        ]);

        Holiday::create($data);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday created.');
    }

    /**
     * Show the form for editing the specified holiday.
     */
    public function edit(Holiday $holiday)
    {
        return view('holidays.form', compact('holiday'));
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'date'         => 'required|date|unique:holidays,date,' . $holiday->id,
            'type'         => 'required|in:regular,special',
            'is_recurring' => 'sometimes|in:0,1',
        ]);

        $holiday->update($data);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday updated.');
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday removed.');
    }
}
