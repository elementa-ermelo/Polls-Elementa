@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Wachtwoord Wijzigen</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.update-password') }}" method="POST" class="bg-white p-6 rounded border border-gray-300 max-w-2xl">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="current_password" class="block text-gray-700 font-bold mb-2">Huidige Wachtwoord *</label>
            <input type="password" id="current_password" name="current_password" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-bold mb-2">Nieuw Wachtwoord *</label>
            <input type="password" id="password" name="password" required minlength="8" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            <p class="text-gray-600 text-sm mt-1">Minimaal 8 karakters</p>
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 font-bold mb-2">Bevestig Nieuw Wachtwoord *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="flex justify-between">
            <a href="{{ route('profile.edit') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Annuleren
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Wachtwoord Wijzigen
            </button>
        </div>
    </form>
</div>
@endsection
