@extends('layouts.app')

@section('content')
<div style="margin-bottom: 32px;">
    <h1 style="margin-bottom: 6px;">Polls invullen</h1>
    <p class="meta">Vul een van deze polls in en deel jouw mening</p>
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
