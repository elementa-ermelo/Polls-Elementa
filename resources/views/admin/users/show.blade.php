@extends('layouts.app')

@section('content')
<div class="mx-auto px-4 py-4 max-w-xl">
    <div class="flex justify-between items-center mb-3">
        <h1 class="text-xl font-bold">{{ $user->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white text-xs font-bold py-1 px-2 rounded">
                Bewerk
            </a>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white text-xs font-bold py-1 px-2 rounded">
                Terug
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded mb-3 text-xs">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-3 rounded border border-gray-300 text-sm space-y-2">
        <div class="grid grid-cols-2 gap-2">
            <span class="font-bold">Email:</span>
            <span>{{ $user->email }}</span>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <span class="font-bold">Type:</span>
            <span>
                @if($user->is_admin)
                    <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded text-xs font-bold">Admin</span>
                @else
                    <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs">Gebruiker</span>
                @endif
            </span>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <span class="font-bold">Lid sinds:</span>
            <span>{{ $user->created_at->format('d-m-Y') }}</span>
        </div>
    </div>
</div>
@endsection
