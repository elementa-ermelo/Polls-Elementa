@extends('layouts.app')

@section('content')
<div style="max-width:480px; margin:60px auto;">
    <div style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; margin-bottom: 8px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Welkom terug</h1>
        <p class="meta">Log in op je account om verder te gaan</p>
    </div>

    <div class="card" style="padding: 40px;">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">E-mailadres</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="naam@voorbeeld.nl" required autofocus>
                @error('email')<p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;">{{ $message }}</p>@enderror
            </div>

            <div class="field" style="margin-bottom: 28px;">
                <label for="password">Wachtwoord</label>
                <input id="password" type="password" name="password"
                       placeholder="Uw wachtwoord" required>
                @error('password')<p style="color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600;">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px; font-weight: 700;">
                Inloggen
            </button>
        </form>

        <div style="border-top: 1px solid var(--line); margin: 28px 0;"></div>

        <p style="margin: 0; text-align: center; font-size: 14px; color: var(--muted);">
            Nog geen account? <a href="{{ route('register') }}" style="color: var(--primary); font-weight: 700; text-decoration: none;">Account aanmaken</a>
        </p>
    </div>
</div>
@endsection
