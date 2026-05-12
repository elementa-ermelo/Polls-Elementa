@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Organisaties</h1>

    @if($organizations->isEmpty())
        <div class="bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded text-center">
            Geen organisaties beschikbaar.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($organizations as $organization)
                <a href="{{ route('organizations.show', $organization->slug) }}" class="bg-white rounded border border-gray-300 p-6 hover:shadow-lg transition">
                    <div class="mb-4">
                        @if($organization->logo)
                            <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }}" class="w-full h-40 object-cover rounded">
                        @else
                            <div class="w-full h-40 bg-gray-300 rounded flex items-center justify-center">
                                <span class="text-gray-600">Geen logo</span>
                            </div>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold mb-2 hover:text-blue-500">{{ $organization->name }}</h3>
                    @if($organization->description)
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($organization->description, 100) }}</p>
                    @endif
                    <div class="text-sm text-gray-500">
                        {{ $organization->polls()->where('status', 'active')->count() }} actieve polls
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
