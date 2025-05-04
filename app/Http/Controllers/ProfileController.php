<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'email'           => ['required','email','max:255', Rule::unique('users')->ignore($user->id)],
            'profile_picture' => ['nullable','image','max:2048'],
        ]);

        if ($request->hasFile('profile_picture')) {
            // Saves to storage/app/public/profiles/...
            $path = $request->file('profile_picture')
                            ->store('profiles', 'public');
            // Store only "profiles/filename.jpg" in DB
            $data['profile_picture'] = $path;
        }

        $user->update($data);

        return back()->with('success','Profile updated.');
    }
}
