<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->user()->id,
            'logo' => 'nullable|image|mimes:jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            if (auth()->user()->logo && Storage::disk('public')->exists(auth()->user()->logo)) {
                Storage::disk('public')->delete(auth()->user()->logo);
            }
            
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $logoPath = $file->storeAs('logos', $filename, 'public');

            \Log::info('Logo debug', [
                'logoPath' => $logoPath,
                'validated_logo' => $validated['logo'] ?? 'NOT SET',
            ]);
            
            if ($logoPath) {
                $validated['logo'] = $logoPath;
            }
        }

        \Log::info('Before update', ['validated' => $validated]);
        auth()->user()->update($validated);
        \Log::info('After update', ['user_logo' => auth()->user()->fresh()->logo]);

        return back()->with('success', 'Profiel bijgewerkt.');
    }

    public function changePassword()
    {
        return view('profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Wachtwoord gewijzigd.');
    }
}