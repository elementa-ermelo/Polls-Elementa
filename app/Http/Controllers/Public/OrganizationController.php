<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Organization;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::query()
            ->withCount('polls')
            ->latest()
            ->get();

        return view('organizations.index', [
            'organizations' => $organizations,
        ]);
    }

    public function show(Organization $organization)
    {
        $polls = $organization->polls()
            ->where('status', 'active')
            ->withCount('votes')
            ->latest()
            ->get();

        return view('organizations.show', [
            'organization' => $organization,
            'polls' => $polls,
        ]);
    }
}
