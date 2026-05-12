@extends('layouts.app')

@section('content')
<div style="max-width: 480px; margin: 60px auto;">
    <div style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; margin-bottom: 8px; background: linear-gradient(135deg, var(--accent) 0%, var(--primary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Maak je account aan</h1>
        <p class="meta">Begin met polls creëren en beheren</p>
    </div>

    <div class="card" style="padding: 40px;">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="field">
                <label for="name">Volledige naam *</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                       placeholder="Uw naam" required autofocus>
                @error('name')<p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="email">E-mailadres *</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="naam@voorbeeld.nl" required>
                @error('email')<p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="password">Wachtwoord *</label>
                <input id="password" type="password" name="password"
                       placeholder="Minimaal 8 tekens" required>
                @error('password')<p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;">{{ $message }}</p>@enderror
            </div>

            <div class="field" style="margin-bottom: 28px;">
                <label for="password-confirm">Wachtwoord bevestigen *</label>
                <input id="password-confirm" type="password" name="password_confirmation"
                       placeholder="Herhaal wachtwoord" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px; font-weight: 700;">
                Account aanmaken
            </button>
        </form>

        <div style="border-top: 1px solid var(--line); margin: 28px 0;"></div>

        <p style="margin: 0; text-align: center; font-size: 14px; color: var(--muted);">
            Al een account? <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 700; text-decoration: none;">Inloggen</a>
        </p>
    </div>
</div>
@endsection
