@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Mijn Profiel</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Profile Photo Section -->
    <div class="bg-white p-6 rounded border border-gray-300 mb-6">
        <h2 class="text-2xl font-bold mb-4">Profielfoto</h2>
        <div class="flex items-center gap-6">
            <div>
                @if($user->logo)
                    <img src="{{ asset('storage/' . $user->logo) }}" alt="{{ $user->name }}" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb;">
                @else
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: #d1d5db; display: flex; align-items: center; justify-content: center; border: 3px solid #e5e7eb;">
                        <span style="font-size: 48px; color: #6b7280; font-weight: bold;">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            <div>
                <p class="text-gray-600 text-sm mb-2">Huidige foto:</p>
                <p class="font-bold mb-4">{{ $user->logo ? 'Foto ingesteld' : 'Geen foto ingesteld' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Profile Edit Form -->
        <div class="bg-white p-6 rounded border border-gray-300">
            <h2 class="text-2xl font-bold mb-4">Gegevens</h2>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-bold mb-2">Naam *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-bold mb-2">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-6">
                    <label for="logo" class="block text-gray-700 font-bold mb-2">Logo</label>
                    <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" onchange="previewLogo(event)">
                    <p class="text-gray-600 text-sm mt-2">JPG, PNG of GIF. Maximaal 2MB.</p>
                    <div id="logoPreview" style="margin-top: 12px; display: none;">
                        <p class="text-gray-600 text-sm mb-2">Preview:</p>
                        <img id="previewImage" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb;">
                    </div>
                    <p id="uploadedFileName" class="text-sm text-gray-500 mt-2"></p>
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                    Opslaan
                </button>
            </form>
        </div>

        <!-- Additional Info -->
        <div class="bg-white p-6 rounded border border-gray-300">
            <h2 class="text-2xl font-bold mb-4">Informatie</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-gray-600 text-sm">Type:</p>
                    <p class="font-bold">
                        @if($user->is_admin)
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">Admin</span>
                        @else
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">Gebruiker</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Lid sinds:</p>
                    <p class="font-bold">{{ $user->created_at->format('d-m-Y') }}</p>
                </div>
            </div>

            <a href="{{ route('profile.change-password') }}" class="block mt-6 bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center">
                Wachtwoord Wijzigen
            </a>
        </div>
    </div>
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