<?php

namespace App\Http\Controllers;

use App\Models\InactiveUser;
use Illuminate\Http\Request;

class InactiveUserController extends Controller
{
    /**
     * Display a listing of inactive users.
     */
    public function index()
    {
        $inactiveUsers = InactiveUser::inactive()->get();
        return view('inactive_users.index', compact('inactiveUsers'));
    }

    /**
     * Show the form for creating a new inactive user.
     */
    public function create()
    {
        return view('inactive_users.create');
    }

    /**
     * Store a newly created inactive user in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:employees,email',
            // add other fields as needed…
        ]);

        // force status to inactive
        $data['status'] = 'inactive';

        InactiveUser::create($data);

        return redirect()
            ->route('inactive_users.index')
            ->with('success', 'Inactive user created successfully.');
    }

    /**
     * Display the specified inactive user.
     */
    public function show(InactiveUser $inactiveUser)
    {
        return view('inactive_users.show', compact('inactiveUser'));
    }

    /**
     * Show the form for editing the specified inactive user.
     */
    public function edit(InactiveUser $inactiveUser)
    {
        return view('inactive_users.edit', compact('inactiveUser'));
    }

    /**
     * Update the specified inactive user in storage.
     */
    public function update(Request $request, InactiveUser $inactiveUser)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:employees,email,' . $inactiveUser->id,
            'status'     => 'required|in:active,inactive',
            // other fields…
        ]);

        $inactiveUser->update($data);

        return redirect()
            ->route('inactive_users.index')
            ->with('success', 'Inactive user updated successfully.');
    }

    /**
     * Remove the specified inactive user from storage.
     */
    public function destroy(InactiveUser $inactiveUser)
    {
        $inactiveUser->delete();

        return redirect()
            ->route('inactive_users.index')
            ->with('success', 'Inactive user deleted successfully.');
    }
}
