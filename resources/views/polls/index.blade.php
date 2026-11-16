@extends('layouts.app')

@section('content')

{{-- Hero Section --}}
<div style="margin-bottom: 48px; padding: 40px 24px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); border-radius: 16px; color: white;">
    <h1 style="margin: 0 0 12px; font-size: 32px; font-weight: 700;">📊 Welkom bij Polls Elementa</h1>
    <p style="margin: 0 0 6px; font-size: 18px; opacity: 0.95;">Hier vindt u de vragenlijsten die openstaan.</p>
    <p style="margin: 0; opacity: 0.85; line-height: 1.6; max-width: 600px;">
        Organisaties gebruiken deze site om inwoners en klanten vragen te stellen. Uw antwoorden gaan naar de
        organisatie die de vragenlijst heeft opgesteld.
    </p>
</div>

{{-- How it Works Section --}}
<div style="margin-bottom: 48px;">
    <h2 style="margin-bottom: 24px; text-align: center;">Hoe werkt het?</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">1️⃣</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Kies een vragenlijst</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Klik bij de vragenlijst op 'Invullen'.
            </p>
        </div>
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">2️⃣</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Uw gegevens</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                U vult uw naam en e-mailadres in. Telefoonnummer en leeftijd zijn niet verplicht.
            </p>
        </div>
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">3️⃣</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Beantwoord de vragen</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Kies bij elke vraag een antwoord. Open vragen mag u overslaan.
            </p>
        </div>
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">📧</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Bevestig per e-mail</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Bij sommige vragenlijsten krijgt u een e-mail met een link. Pas als u daarop klikt, tellen uw antwoorden mee.
            </p>
        </div>
        <div style="padding: 24px; background: var(--surface); border-radius: 12px; border: 1px solid var(--line);">
            <div style="font-size: 32px; margin-bottom: 12px;">✅</div>
            <h3 style="margin: 0 0 8px; font-size: 16px;">Klaar</h3>
            <p style="margin: 0; color: var(--muted); font-size: 14px;">
                Uw antwoorden zijn verwerkt. Bedankt voor het meedoen.
            </p>
        </div>
    </div>
</div>

<div style="margin-bottom: 32px;">
    <h1 style="margin-bottom: 6px;">📋 Open vragenlijsten</h1>
    <p class="meta">Kies hieronder een vragenlijst.</p>
</div>

{{-- Why Email Verification --}}
<div style="margin-bottom: 48px; padding: 24px; background: #fef3c7; border: 2px solid #fbbf24; border-radius: 12px;">
    <h3 style="margin: 0 0 12px; color: #92400e; display: flex; align-items: center; gap: 8px;">
        <span>🔐</span> Waarom een bevestiging per e-mail?
    </h3>
    <p style="margin: 0; color: #b45309; line-height: 1.6;">
        Met de bevestigingslink controleren wij of het e-mailadres van u is. Zo kan niemand anders namens u
        antwoorden en blijft de uitslag betrouwbaar. De link is zeven dagen geldig.
    </p>
</div>

@if($polls->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
        <h2>Geen vragenlijsten</h2>
        <p style="color: var(--muted); margin-bottom: 0;">Op dit moment staan er geen vragenlijsten open.</p>
    </div>
@else
    <div style="display: grid; gap: 16px;">
        @foreach($polls as $poll)
            <div class="card" style="padding: 24px; border-left: 5px solid var(--primary);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                    <div>
                        <h3 style="margin-top: 0; margin-bottom: 8px; color: var(--primary);">{{ $poll->title }}</h3>
                        <p style="margin: 0 0 12px 0; color: var(--text-light); font-size: 15px;">{{ $poll->question }}</p>
                        @if($poll->user)
                            <div style="display: flex; align-items: center; gap: 12px; margin-top: 16px; padding: 12px; background: rgba(59, 130, 246, 0.05); border-radius: 8px;">
                                @if($poll->user->logo)
                                    <img src="{{ asset('storage/' . $poll->user->logo) }}" alt="{{ $poll->user->name }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); flex-shrink: 0;">
                                @else
                                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #dbeafe 0%, #cffafe 100%); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; color: var(--primary); flex-shrink: 0; border: 2px solid var(--primary);">
                                        {{ substr($poll->user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p style="margin: 0; font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;">Van</p>
                                    <p style="margin: 0; font-weight: 700; font-size: 14px; color: var(--text);">{{ $poll->user->name }}</p>
                                </div>
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
