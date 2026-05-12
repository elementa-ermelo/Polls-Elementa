@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px;">
    <div>
        <h1 style="margin-bottom: 6px;">📦 Archiefpolls</h1>
        <p class="meta">Gearchiveerde polls beheren en herstellen</p>
    </div>
    <a class="btn" href="{{ route('admin.reports.index') }}">← Terug</a>
</div>

<div class="card">
    <h2>Archiefpolls</h2>
    <table>
        <thead>
        <tr>
            <th>Poll</th>
            <th>Status</th>
            <th>Bevestigd/totaal</th>
            <th>Gearchiveerd op</th>
            <th>Actie</th>
        </tr>
        </thead>
        <tbody>
        @forelse($archivedPolls as $poll)
            <tr>
                <td><a href="{{ route('admin.polls.show', $poll) }}">{{ $poll->title }}</a></td>
                <td>{{ $poll->status }}</td>
                <td>{{ $poll->confirmed_votes_count }} / {{ $poll->unconfirmed_votes_count }}</td>
                <td>{{ $poll->updated_at?->format('d-m-Y H:i') ?? '-' }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.reports.reactivate', $poll) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn" style="font-size:13px; padding:6px 12px;">Activeren</button>
                    </form>
                    <form method="POST" action="{{ route('admin.polls.destroy', $poll) }}" style="display:inline;" onsubmit="return confirm('Poll definitief verwijderen?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="font-size:13px; padding:6px 12px; color:#b91c1c;">Verwijderen</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Nog geen gearchiveerde polls.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
