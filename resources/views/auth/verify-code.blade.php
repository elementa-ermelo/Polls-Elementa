@extends('layouts.app')

@section('content')
<div style="max-width:480px; margin:60px auto;">
    <div style="text-align:center; margin-bottom:24px;">
        <h1 style="font-size:28px; margin-bottom:8px;">Inlogcode</h1>
        <p class="meta">We hebben een code van 6 cijfers gestuurd naar {{ $adres }}. De code is 10 minuten geldig.</p>
    </div>

    <div class="card" style="padding:32px;">
        @if(session('status'))
            <p style="margin:0 0 16px; color:var(--success); font-weight:600;">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('login.code.controle') }}">
            @csrf
            <div class="field">
                <label for="code">Code</label>
                <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                       pattern="[0-9 ]*" maxlength="7" required autofocus style="font-size:22px; letter-spacing:4px; text-align:center;">
                @error('code')<p style="color:var(--danger); font-size:13px; margin:6px 0 0; font-weight:600;">{{ $message }}</p>@enderror
            </div>
            <label style="display:flex; gap:8px; align-items:center; font-weight:400; margin:0 0 20px;">
                <input type="checkbox" name="vertrouw" value="1"> Vertrouw dit apparaat 10 dagen
            </label>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:16px; font-weight:700;">Inloggen</button>
        </form>
        <form method="POST" action="{{ route('login.code.opnieuw') }}" style="margin-top:16px; text-align:center;">
            @csrf
            <button type="submit" class="btn" style="font-size:13px;">Stuur een nieuwe code</button>
        </form>
    </div>
</div>
@endsection
