@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Gebruiker Bewerken</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded border border-gray-300">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Naam *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-bold mb-2">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-6">
            <label for="is_admin" class="flex items-center">
                <input type="checkbox" id="is_admin" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} class="mr-3">
                <span class="text-gray-700 font-bold">Dit is een admin gebruiker</span>
            </label>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Logo</label>
            @if($user->logo)
                <div class="mb-3">
                    <p class="text-gray-600 text-sm mb-2">Huidigie logo:</p>
                    <img src="{{ asset('storage/' . $user->logo) }}" alt="{{ $user->name }}" style="width: 100px; height: 100px; border-radius: 8px; object-fit: cover; border: 2px solid #e5e7eb;">
                    <p class="text-gray-600 text-sm mt-2">{{ $user->logo }}</p>
                </div>
            @endif
            <label for="logo" class="block text-gray-700 font-bold mb-2">{{ $user->logo ? 'Nieuw logo' : 'Logo' }}</label>
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" onchange="previewLogo(event)">
            <p class="text-gray-600 text-sm mt-2">JPG, PNG of GIF. Maximaal 2MB.</p>
            <div id="logoPreview" style="margin-top: 12px; display: none;">
                <p class="text-gray-600 text-sm mb-2">Preview:</p>
                <img id="previewImage" style="width: 100px; height: 100px; border-radius: 8px; object-fit: cover; border: 2px solid #e5e7eb;">
            </div>
            <p id="uploadedFileName" class="text-sm text-gray-500 mt-2"></p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('admin.users.show', $user) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Annuleren
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Wijzigingen Opslaan
            </button>
        </div>
    </form>
</div>

<script>
function previewLogo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
            document.getElementById('uploadedFileName').textContent = 'Bestand: ' + file.name;
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
