<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->is_admin) {
                abort(403, 'Alleen admins kunnen gebruikers beheren.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::latest()->paginate(12);

        return view('admin.users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,gif|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_admin'] = $request->boolean('is_admin');
        $validated['organization_id'] = auth()->user()->organization_id;

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $logoPath = $file->storeAs('logos', $filename, 'public');
            
            if ($logoPath) {
                $validated['logo'] = $logoPath;
            }
        }

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Gebruiker aangemaakt.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', ['user' => $user]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'is_admin' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,gif|max:2048',
        ]);

        $validated['is_admin'] = $request->boolean('is_admin');

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            // Delete old logo if it exists
            if ($user->logo && Storage::disk('public')->exists($user->logo)) {
                Storage::disk('public')->delete($user->logo);
            }
            
            // Store new logo
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $logoPath = $file->storeAs('logos', $filename, 'public');
            
            if ($logoPath) {
                $validated['logo'] = $logoPath;
            }
        }

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)->with('success', 'Gebruiker bijgewerkt.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->user()->id) {
            return back()->with('error', 'Je kunt jezelf niet verwijderen.');
        }

        if ($user->logo) {
            Storage::disk('public')->delete($user->logo);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Gebruiker verwijderd.');
    }
}
