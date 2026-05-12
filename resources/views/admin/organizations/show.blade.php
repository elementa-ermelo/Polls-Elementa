@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">{{ $organization->name }}</h1>
            <p class="text-gray-600">{{ $organization->description }}</p>
        </div>
        <div>
            <a href="{{ route('admin.organizations.edit', $organization) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                Bewerk
            </a>
            <a href="{{ route('admin.organizations.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Terug
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Logo -->
        <div class="bg-white p-6 rounded border border-gray-300">
            <h3 class="text-lg font-bold mb-4">Logo</h3>
            @if($organization->logo)
                <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }}" class="max-w-full h-auto rounded">
            @else
                <div class="h-40 bg-gray-300 rounded flex items-center justify-center">
                    <span class="text-gray-600">Geen logo</span>
                </div>
            @endif
        </div>

        <!-- Contactgegevens -->
        <div class="bg-white p-6 rounded border border-gray-300">
            <h3 class="text-lg font-bold mb-4">Contactgegevens</h3>
            <div class="space-y-2">
                @if($organization->email)
                    <p><strong>Email:</strong> <a href="mailto:{{ $organization->email }}" class="text-blue-500 hover:text-blue-700">{{ $organization->email }}</a></p>
                @endif
                @if($organization->phone)
                    <p><strong>Telefoon:</strong> {{ $organization->phone }}</p>
                @endif
                @if($organization->website)
                    <p><strong>Website:</strong> <a href="{{ $organization->website }}" target="_blank" class="text-blue-500 hover:text-blue-700">{{ $organization->website }}</a></p>
                @endif
            </div>
        </div>

        <!-- Statistieken -->
        <div class="bg-white p-6 rounded border border-gray-300">
            <h3 class="text-lg font-bold mb-4">Statistieken</h3>
            <div class="space-y-2">
                <p><strong>Gebruikers:</strong> {{ $organization->users->count() }}</p>
                <p><strong>Polls:</strong> {{ $organization->polls->count() }}</p>
                <p><strong>Aangemaakt:</strong> {{ $organization->created_at->format('d-m-Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Gebruikers -->
    <div class="bg-white p-6 rounded border border-gray-300 mb-8">
        <h3 class="text-lg font-bold mb-4">Gebruikers ({{ $organization->users->count() }})</h3>
        @if($organization->users->isEmpty())
            <p class="text-gray-600">Geen gebruikers.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="px-4 py-2 text-left">Naam</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Lid sinds</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($organization->users as $user)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $user->name }}</td>
                                <td class="px-4 py-2">{{ $user->email }}</td>
                                <td class="px-4 py-2">{{ $user->created_at->format('d-m-Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Polls -->
    <div class="bg-white p-6 rounded border border-gray-300">
        <h3 class="text-lg font-bold mb-4">Polls ({{ $organization->polls->count() }})</h3>
        @if($organization->polls->isEmpty())
            <p class="text-gray-600">Geen polls.</p>
        @else
            <div class="space-y-4">
                @foreach($organization->polls as $poll)
                    <div class="border border-gray-200 p-4 rounded hover:bg-gray-50">
                        <h4 class="font-bold text-lg mb-2">{{ $poll->title }}</h4>
                        <p class="text-gray-600 mb-2">{{ $poll->description }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">{{ $poll->votes->count() }} antwoorden</span>
                            <a href="{{ route('polls.show', $poll) }}" class="text-blue-500 hover:text-blue-700">Bekijk Poll</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
