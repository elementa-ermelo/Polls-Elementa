@extends('layouts.app')

@section('content')
<div class="mx-auto px-4 py-4 max-w-6xl">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Gebruikers</h1>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-1 px-3 rounded">
            + Nieuw
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded mb-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($users->isEmpty())
        <div class="bg-gray-100 border border-gray-400 text-gray-700 px-3 py-2 rounded text-center text-sm">
            Geen gebruikers gevonden. <a href="{{ route('admin.users.create') }}" class="text-blue-500 hover:text-blue-700 font-bold">Maak er één aan</a>.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full bg-white border border-gray-300 text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-1 py-0.5 border border-gray-300 text-left w-4 text-xs">F</th>
                        <th class="px-2 py-1 border border-gray-300 text-left">Naam</th>
                        <th class="px-2 py-1 border border-gray-300 text-left">Email</th>
                        <th class="px-2 py-1 border border-gray-300 text-left">Type</th>
                        <th class="px-2 py-1 border border-gray-300 text-left">Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 border-b border-gray-300">
                            <td class="px-0.5 py-0.5 border border-gray-300">
                                @if($user->logo)
                                    <img src="{{ asset('storage/' . $user->logo) }}" alt="{{ $user->name }}" style="height: 56px; width: 56px; border-radius: 50%; object-fit: cover; display: block;">
                                @endif
                            </td>
                            <td class="px-2 py-1 border border-gray-300">{{ $user->name }}</td>
                            <td class="px-2 py-1 border border-gray-300 text-gray-600">{{ $user->email }}</td>
                            <td class="px-2 py-1 border border-gray-300">
                                @if($user->is_admin)
                                    <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded text-xs font-bold">Admin</span>
                                @else
                                    <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs">Gebruiker</span>
                                @endif
                            </td>
                            <td class="px-2 py-1 border border-gray-300">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-blue-500 hover:text-blue-700 text-xs mr-2">Bekijk</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-yellow-500 hover:text-yellow-700 text-xs mr-2">Bewerk</a>
                                @if($user->id !== auth()->user()->id)
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;" onsubmit="return confirm('Verwijderen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Del</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2 text-sm">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
