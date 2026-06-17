@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Organisatie Bewerken</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.organizations.update', $organization) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded border border-gray-300">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Naam *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $organization->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-bold mb-2">Beschrijving</label>
            <textarea id="description" name="description" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" rows="4">{{ old('description', $organization->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $organization->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="phone" class="block text-gray-700 font-bold mb-2">Telefoonnummer</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $organization->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="website" class="block text-gray-700 font-bold mb-2">Website</label>
            <input type="url" id="website" name="website" value="{{ old('website', $organization->website) }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-6">
            <label for="logo" class="block text-gray-700 font-bold mb-2">Logo</label>
            @if($organization->logo)
                <div class="mb-3">
                    <p class="text-gray-600 text-sm mb-2">Huidige logo:</p>
                    <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }}" class="h-40 w-40 object-cover rounded">
                    <p class="text-gray-600 text-sm mt-2">{{ $organization->logo }}</p>
                </div>
            @endif
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" onchange="previewLogo(event)">
            <p class="text-gray-600 text-sm mt-2">JPG, PNG of GIF. Maximaal 2MB. (Leeg laten om huidig logo te behouden)</p>
            <div id="logoPreview" style="margin-top: 12px; display: none;">
                <p class="text-gray-600 text-sm mb-2">Preview:</p>
                <img id="previewImage" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #e5e7eb;">
            </div>
            <p id="uploadedFileName" class="text-sm text-gray-500 mt-2"></p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('admin.organizations.show', $organization) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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
