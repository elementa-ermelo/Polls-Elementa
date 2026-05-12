@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Nieuwe Organisatie</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.organizations.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded border border-gray-300">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Naam *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="Bijv. Gemeente Ermelo">
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Beschrijving</label>
            <textarea id="description" name="description" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" rows="4" placeholder="Bijv. De gemeente Ermelo is een...">{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="info@gemeente.nl">
        </div>

        <div class="mb-4">
            <label for="phone" class="block text-gray-700 font-bold mb-2">Telefoonnummer</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="0123-456789">
        </div>

        <div class="mb-4">
            <label for="website" class="block text-gray-700 font-bold mb-2">Website</label>
            <input type="url" id="website" name="website" value="{{ old('website') }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="https://example.nl">
        </div>

        <div class="mb-6">
            <label for="logo" class="block text-gray-700 font-bold mb-2">Logo</label>
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
            <p class="text-gray-600 text-sm mt-2">JPG, PNG of GIF. Maximaal 2MB.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('admin.organizations.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Annuleren
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Organisatie Aanmaken
            </button>
        </div>
    </form>
</div>
@endsection
