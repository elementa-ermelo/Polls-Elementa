<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::all();
        return view('admin.organizations.index', ['organizations' => $organizations]);
    }

    public function create()
    {
        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|email|unique:organizations',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,gif|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        Organization::create($validated);

        return redirect()->route('admin.organizations.index')->with('success', 'Organisatie aangemaakt.');
    }

    public function show(Organization $organization)
    {
        $organization->load('polls', 'users');
        return view('admin.organizations.show', ['organization' => $organization]);
    }

    public function edit(Organization $organization)
    {
        return view('admin.organizations.edit', ['organization' => $organization]);
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => ['nullable', 'email', Rule::unique('organizations')->ignore($organization->id)],
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,gif|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                \Storage::disk('public')->delete($organization->logo);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $organization->update($validated);

        return redirect()->route('admin.organizations.show', $organization)->with('success', 'Organisatie bijgewerkt.');
    }

    public function destroy(Organization $organization)
    {
        if ($organization->logo) {
            \Storage::disk('public')->delete($organization->logo);
        }
        $organization->delete();

        return redirect()->route('admin.organizations.index')->with('success', 'Organisatie verwijderd.');
    }
}
