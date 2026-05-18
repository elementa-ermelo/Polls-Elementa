@extends('layouts.app')

@section('content')

{{-- Hero Section --}}
<div style="margin-bottom: 48px; padding: 40px 24px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); border-radius: 16px; color: white;">
    <h1 style="margin: 0 0 12px; font-size: 32px; font-weight: 700;">📊 Welkom bij Polls Elementa</h1>
    <p style="margin: 0 0 6px; font-size: 18px; opacity: 0.95;">Jouw mening telt!</p>
    <p style="margin: 0; opacity: 0.85; line-height: 1.6; max-width: 600px;">
        Vul onze polls in en help ons beter te begrijpen wat jij denkt. Jouw antwoorden helpen organisaties 
        betere beslissingen te nemen op basis van echte meningen van mensen zoals jij.
    </p>
</div>

{{-- How it Works Section --}}
<div style="margin-bottom: 48px;">
    <h2 style="margin-bottom: 24px; text-align: center;">Hoe werkt het?</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">1️⃣</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Kies een poll</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Selecteer een poll die je interesseert en klik op "Invullen".
            </p>
        </div>
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">2️⃣</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Vul je gegevens in</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Voer je naam, e-mailadres en leeftijd in. Dit wordt gebruikt voor statistieken.
            </p>
        </div>
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">3️⃣</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Beantwoord de vragen</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Geef eerlijk antwoord op alle vragen. Jouw mening is waardevol!
            </p>
        </div>
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">📧</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Bevestig per e-mail</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Check je mailbox en bevestig je stem. Dit zorgt ervoor dat je antwoord telt!
            </p>
        </div>
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">✅</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Klaar!</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Je stem is verwerkt. Bedankt dat je hebt deelgenomen!
            </p>
        </div>
    </div>
</div>

<div style="margin-bottom: 32px;">
    <h1 style="margin-bottom: 6px;">📋 Beschikbare Polls</h1>
    <p class="meta">Vul een poll in en deel jouw mening • Jouw antwoord telt!</p>
</div>

{{-- Why Email Verification --}}
<div style="margin-bottom: 48px; padding: 24px; background: #fef3c7; border: 2px solid #fbbf24; border-radius: 12px;">
    <h3 style="margin: 0 0 12px; color: #92400e; display: flex; align-items: center; gap: 8px;">
        <span>🔐</span> Waarom e-mail bevestiging?
    </h3>
    <p style="margin: 0; color: #b45309; line-height: 1.6;">
        We sturen je een bevestigingslink per e-mail om ervoor te zorgen dat jij echt bent wie je zegt te zijn. 
        Dit voorkomt dat iemand anders je e-mailadres gebruikt en zorgt ervoor dat het resultaten van de poll 
        betrouwbaar blijven. Je hebt 24 uur de tijd om je stem te bevestigen.
    </p>
</div>

@if($polls->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
        <h2>Geen polls beschikbaar</h2>
        <p style="color: var(--muted); margin-bottom: 0;">Er zijn op dit moment geen polls beschikbaar. Kom later terug!</p>
    </div>
@else
    <div style="display: grid; gap: 16px;">
        @foreach($polls as $poll)
            <div class="card" style="padding: 24px; border-left: 5px solid var(--primary);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                    <div style="flex: 1;">
                        <h3 style="margin-top: 0; margin-bottom: 8px; color: var(--primary);">{{ $poll->title }}</h3>
                        <p style="margin: 0 0 12px 0; color: var(--text-light); font-size: 15px;">{{ $poll->question }}</p>
                        @if($poll->user)
                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 12px;">
                                @if($poll->user->logo)
                                    <img src="{{ asset('storage/' . $poll->user->logo) }}" alt="{{ $poll->user->name }}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 1px solid var(--line);">
                                @else
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #dbeafe 0%, #cffafe 100%); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: var(--primary);">
                                        {{ substr($poll->user->name, 0, 1) }}
                                    </div>
                                @endif
                                <span style="font-size: 13px; color: var(--muted);"><strong style="color: var(--text);">{{ $poll->user->name }}</strong></span>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('polls.show', $poll) }}" class="btn btn-primary" style="white-space: nowrap; margin-top: 0;">
                        Invullen →
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
