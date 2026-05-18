@extends('layouts.app')

@section('content')
<div class="inline" style="justify-content: space-between; margin-bottom: 24px; align-items: flex-start;">
    <div>
        <h1 style="margin-bottom: 4px;">Mijn Polls</h1>
        <p class="meta">Beheer en analyseer je polls</p>
    </div>
    <button type="button" class="btn btn-primary" id="create-poll-btn">+ Nieuwe poll</button>

    <script>
    document.getElementById('create-poll-btn').addEventListener('click', async () => {
        const btn = document.getElementById('create-poll-btn');
        btn.disabled = true;
        btn.innerText = 'Aan het maken...';

        try {
            const response = await fetch('{{ route("admin.polls.quick-create") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                window.location.href = data.redirect;
            } else {
                alert('Error: ' + response.statusText);
                btn.disabled = false;
                btn.innerText = '+ Nieuwe poll';
            }
        } catch (error) {
            alert('Error: ' + error.message);
            btn.disabled = false;
            btn.innerText = '+ Nieuwe poll';
        }
    });
    </script>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Maker</th>
            <th>Titel</th>
            <th>Type</th>
            <th>Status</th>
            <th>Publiek</th>
            <th>Stemmen (bevestigd/totaal)</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        @forelse($polls as $poll)
            <tr>
                <td>
                    @if($poll->user)
                        <div style="display: flex; align-items: center; gap: 6px;">
                            @if($poll->user->logo)
                                <img src="{{ asset('storage/' . $poll->user->logo) }}" alt="{{ $poll->user->name }}" style="height: 48px; width: 48px; border-radius: 50%; object-fit: cover; border: 1px solid #e5e7eb;">
                            @else
                                <div style="height: 32px; width: 32px; border-radius: 50%; background: #d1d5db; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 12px; color: #6b7280; font-weight: bold;">{{ substr($poll->user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span>{{ $poll->user->name }}</span>
                        </div>
                    @else
                        <span style="color: #9ca3af;">Onbekend</span>
                    @endif
                </td>
                <td>{{ $poll->title }}</td>
                <td>{{ $typeLabels[$poll->type] ?? $poll->type }}</td>
                <td>{{ $poll->status }}</td>
                <td>{{ $poll->is_public ? 'ja' : 'nee' }}</td>
                <td>{{ $poll->confirmed_votes_count }} / {{ $poll->votes_count }}</td>
                <td class="inline">
                    @if($poll->status !== 'archived')
                        <a class="btn" href="{{ route('admin.polls.show', $poll) }}">Bekijk</a>
                    @endif
                    @if(!$poll->is_public)
                        <a class="btn" href="{{ route('admin.polls.edit', $poll) }}">Bewerk</a>
                    @else
                        <button class="btn" disabled style="opacity: 0.5; cursor: not-allowed;" title="Actieve polls kunnen niet bewerkt worden">Bewerk</button>
                    @endif
                    <form method="post" action="{{ route('admin.polls.toggle-active', $poll) }}">
                        @csrf
                        <button class="btn" type="submit">{{ $poll->is_public ? 'Zet onactief' : 'Zet actief' }}</button>
                    </form>
                    <form method="post" action="{{ route('admin.polls.archive', $poll) }}">
                        @csrf
                        <button class="btn" type="submit">Archiveer</button>
                    </form>
                    <form method="post" action="{{ route('admin.polls.destroy', $poll) }}" onsubmit="return confirm('Weet je het zeker?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Verwijder</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Nog geen polls.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{ $polls->links() }}
@endsection





