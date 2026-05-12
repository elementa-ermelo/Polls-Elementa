@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Organization Header -->
    <div class="bg-white rounded border border-gray-300 p-6 mb-8">
        <div class="flex items-start gap-6">
            <div class="flex-shrink-0">
                @if($organization->logo)
                    <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }}" class="h-32 w-32 object-cover rounded">
                @else
                    <div class="h-32 w-32 bg-gray-300 rounded flex items-center justify-center">
                        <span class="text-gray-600">Geen logo</span>
                    </div>
                @endif
            </div>
            <div class="flex-grow">
                <h1 class="text-4xl font-bold mb-2">{{ $organization->name }}</h1>
                @if($organization->description)
                    <p class="text-gray-600 mb-4">{{ $organization->description }}</p>
                @endif
                <div class="space-y-2 text-sm text-gray-600">
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
        </div>
    </div>

    <!-- Polls -->
    <div>
        <h2 class="text-2xl font-bold mb-6">Polls van {{ $organization->name }}</h2>

        @if($polls->isEmpty())
            <div class="bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded text-center">
                Deze organisatie heeft nog geen polls gepubliceerd.
            </div>
        @else
            <div class="space-y-4">
                @foreach($polls as $poll)
                    <div class="bg-white rounded border border-gray-300 p-6 hover:shadow-lg transition">
                        <h3 class="text-xl font-bold mb-2">{{ $poll->title }}</h3>
                        @if($poll->description)
                            <p class="text-gray-600 mb-4">{{ $poll->description }}</p>
                        @endif
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">{{ $poll->votes->count() }} antwoorden</span>
                            <a href="{{ route('polls.show', $poll) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Doe mee
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
