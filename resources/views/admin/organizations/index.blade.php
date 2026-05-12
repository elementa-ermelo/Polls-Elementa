@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Organisaties</h1>
        <a href="{{ route('admin.organizations.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Nieuwe Organisatie
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($organizations->isEmpty())
        <div class="bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded text-center">
            Geen organisaties gevonden. <a href="{{ route('admin.organizations.create') }}" class="text-blue-500 hover:text-blue-700 font-bold">Maak er één aan</a>.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 border border-gray-300 text-left">Logo</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Naam</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Email</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Gebruikers</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Polls</th>
                        <th class="px-4 py-2 border border-gray-300 text-left">Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($organizations as $organization)
                        <tr class="hover:bg-gray-100 border-b border-gray-300">
                            <td class="px-4 py-2 border border-gray-300">
                                @if($organization->logo)
                                    <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }}" class="h-20 w-20 object-cover rounded">
                                @else
                                    <div class="h-12 w-12 bg-gray-300 rounded flex items-center justify-center">
                                        <span class="text-gray-600 text-xs">Geen logo</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2 border border-gray-300">{{ $organization->name }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $organization->email ?? '-' }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $organization->users_count ?? 0 }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $organization->polls_count ?? 0 }}</td>
                            <td class="px-4 py-2 border border-gray-300">
                                <a href="{{ route('admin.organizations.show', $organization) }}" class="text-blue-500 hover:text-blue-700 mr-2">Bekijk</a>
                                <a href="{{ route('admin.organizations.edit', $organization) }}" class="text-yellow-500 hover:text-yellow-700 mr-2">Bewerk</a>
                                <form action="{{ route('admin.organizations.destroy', $organization) }}" method="POST" style="display:inline;" onsubmit="return confirm('Weet je het zeker?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">Verwijder</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
