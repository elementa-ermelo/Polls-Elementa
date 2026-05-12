@extends('layouts.app')

@section('content')
<div style="margin-bottom: 32px;">
    <h1 style="margin-bottom: 6px;">Rapportage & Analytics</h1>
    <p class="meta">Overzicht van al je polls en stemmen</p>
</div>

<div class="grid grid-2" style="margin-bottom: 28px;">
    <div class="card" style="border-left: 5px solid var(--primary); background: linear-gradient(135deg, #f0f9ff 0%, #f5f3ff 100%);">
        <div style="font-size: 28px; font-weight: 800; color: var(--primary); margin-bottom: 6px;">{{ $totals['polls'] }}</div>
        <div style="color: var(--muted); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Totaal Polls</div>
    </div>
    <div class="card" style="border-left: 5px solid var(--accent); background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);">
        <div style="font-size: 28px; font-weight: 800; color: var(--accent); margin-bottom: 6px;">
            {{ $totals['active'] }} <span style="color: var(--muted); font-size: 14px; margin-left: 4px;">/ {{ $totals['archived'] }}</span>
        </div>
        <div style="color: var(--muted); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Actief / Archief</div>
    </div>
</div>

<div class="card">
    <h2>Polloverzicht</h2>
    <p><a class="btn" href="{{ route('admin.reports.archive') }}">Open archiefpagina</a></p>
    <table>
        <thead>
        <tr>
            <th>Poll</th>
            <th>Status</th>
            <th>Bevestigd/totaal</th>
            <th>Open tot</th>
        </tr>
        </thead>
        <tbody>
        @forelse($polls as $poll)
            <tr>
                <td><a href="{{ route('admin.polls.show', $poll) }}">{{ $poll->title }}</a></td>
                <td>{{ $poll->status }}</td>
                <td>{{ $poll->confirmed_respondents }} / {{ $poll->total_respondents }}</td>
                <td>{{ $poll->closes_at?->format('d-m-Y H:i') ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Nog geen data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
