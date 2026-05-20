@extends('layouts.app')

@section('content')

{{-- Header --}}
<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 28px; flex-wrap: wrap;">
    <div>
        <h1 style="margin: 0 0 6px; color: var(--text);">{{ $poll->title }}</h1>
        @if($poll->question)
            <p class="meta" style="margin: 0 0 12px; white-space: pre-wrap; word-break: break-word;">{{ $poll->question }}</p>
        @endif
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                {{ $poll->status === 'active' ? 'background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46;' : ($poll->status === 'archived' ? 'background: #f1f5f9; color: #64748b;' : ($poll->status === 'inactive' ? 'background: #fee2e2; color: #991b1b;' : 'background: #fef3c7; color: #92400e;')) }}">
                {{ ucfirst($poll->status) }}
            </span>
            <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                {{ $poll->is_public ? 'background: linear-gradient(135deg, #dbeafe 0%, #bae6fd 100%); color: #0c4a6e;' : 'background: #f1f5f9; color: #64748b;' }}">
                {{ $poll->is_public ? '🌐 Publiek' : '🔒 Privé' }}
            </span>
            @if($poll->closes_at)
                <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; background: #f1f5f9; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    ⏰ {{ $poll->closes_at->format('d-m-Y H:i') }}
                </span>
            @endif
        </div>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        @if($poll->status !== 'archived')
            <a class="btn" href="{{ route('polls.show', $poll) }}" target="_blank" style="font-size: 13px;">👁 Bekijk poll</a>
        @endif
        @if(!$poll->is_public)
            <a class="btn" href="{{ route('admin.polls.edit', $poll) }}" style="font-size: 13px;">✏️ Bewerk</a>
        @else
            <button class="btn" disabled style="opacity: 0.5; cursor: not-allowed; font-size: 13px;" title="Actieve polls kunnen niet bewerkt worden">✏️ Bewerk</button>
        @endif
        <a class="btn" href="{{ route('admin.polls.send-email-form', $poll) }}" style="font-size: 13px;">📧 Email</a>
        <form method="post" action="{{ route('admin.polls.toggle-active', $poll) }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn" style="font-size:13px;">
                {{ $poll->is_public ? 'Deactiveer' : 'Activeer' }}
            </button>
        </form>
        <form method="post" action="{{ route('admin.polls.archive', $poll) }}" style="display:inline;" onsubmit="return confirm('Poll archiveren?');">
            @csrf
            <button type="submit" class="btn" style="font-size:13px; color:var(--muted);">Archiveer</button>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="card" style="text-align: center; padding: 24px; border-left: 5px solid var(--primary); background: linear-gradient(135deg, #f0f9ff 0%, #f5f3ff 100%);">
        <p style="margin: 0 0 8px; font-size: 32px; font-weight: 800; color: var(--primary);">{{ $totalRespondents }}</p>
        <p class="meta" style="margin: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Totaal Respondenten</p>
    </div>
    <div class="card" style="text-align: center; padding: 24px; border-left: 5px solid var(--success); background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);">
        <p style="margin: 0 0 8px; font-size: 32px; font-weight: 800; color: var(--success);">{{ $confirmedRespondents }}</p>
        <p class="meta" style="margin: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Bevestigd</p>
    </div>
    <div class="card" style="text-align: center; padding: 24px; border-left: 5px solid #9333ea; background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);">
        <p style="margin: 0 0 8px; font-size: 32px; font-weight: 800; color: #9333ea;">{{ $poll->questions->count() }}</p>
        <p class="meta" style="margin: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Vragen</p>
    </div>
</div>

{{-- Tabs Navigation --}}
<div style="display: flex; gap: 0; margin-bottom: 24px; border-bottom: 2px solid var(--line); flex-wrap: wrap;">
    <button onclick="showTab('beheer')" id="tab-beheer" class="tab-btn" style="padding: 14px 20px; border: none; background: none; cursor: pointer; font-weight: 700; color: var(--primary); border-bottom: 3px solid var(--primary); font-size: 15px; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.5px;">⚙️ Beheer</button>
    <button onclick="showTab('resultaten')" id="tab-resultaten" class="tab-btn" style="padding: 14px 20px; border: none; background: none; cursor: pointer; font-weight: 700; color: var(--muted); border-bottom: 3px solid transparent; font-size: 15px; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.5px;">📊 Resultaten</button>
    <button onclick="showTab('stemmen')" id="tab-stemmen" class="tab-btn" style="padding: 14px 20px; border: none; background: none; cursor: pointer; font-weight: 700; color: var(--muted); border-bottom: 3px solid transparent; font-size: 15px; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.5px;">📋 Alle Stemmen</button>
</div>

<script>
function showTab(tab) {
    document.getElementById('beheer-content').style.display = tab === 'beheer' ? 'block' : 'none';
    document.getElementById('resultaten-content').style.display = tab === 'resultaten' ? 'block' : 'none';
    document.getElementById('stemmen-content').style.display = tab === 'stemmen' ? 'block' : 'none';

    document.getElementById('tab-beheer').style.color = tab === 'beheer' ? 'var(--primary)' : 'var(--muted)';
    document.getElementById('tab-beheer').style.borderColor = tab === 'beheer' ? 'var(--primary)' : 'transparent';
    document.getElementById('tab-resultaten').style.color = tab === 'resultaten' ? 'var(--primary)' : 'var(--muted)';
    document.getElementById('tab-resultaten').style.borderColor = tab === 'resultaten' ? 'var(--primary)' : 'transparent';
    document.getElementById('tab-stemmen').style.color = tab === 'stemmen' ? 'var(--primary)' : 'var(--muted)';
    document.getElementById('tab-stemmen').style.borderColor = tab === 'stemmen' ? 'var(--primary)' : 'transparent';
}
</script>

{{-- TAB: Beheer --}}
<div id="beheer-content" style="display:block;">
<div class="card" style="margin-bottom:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="margin:0; font-size:15px; text-transform:uppercase; letter-spacing:.05em; color:var(--muted);">Vragen</h2>
        <a class="btn btn-primary" href="{{ route('admin.polls.questions.create', $poll) }}" style="font-size:13px;">+ Vraag toevoegen</a>
    </div>

    @forelse($poll->questions as $question)
        <div style="padding:14px; border:1px solid var(--line); border-radius:10px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div style="flex:1; min-width:0;">
                <p style="margin:0 0 4px; font-weight:600; font-size:15px;">{{ $question->question }}</p>
                <p class="meta" style="margin:0; font-size:13px;">
                    {{ $question->type }} &bull; {{ $question->options->count() }} opties &bull; {{ $question->votes->whereNotNull('confirmed_at')->count() }} bevestigde stemmen
                </p>
            </div>
            <div style="display:flex; gap:6px; flex-shrink:0;">
                <a class="btn" href="{{ route('admin.polls.questions.edit', [$poll, $question]) }}" style="font-size:13px; padding:6px 12px;">Bewerk</a>
                <form method="post" action="{{ route('admin.polls.questions.destroy', [$poll, $question]) }}" onsubmit="return confirm('Vraag verwijderen?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" type="submit" style="font-size:13px; padding:6px 12px;">Verwijder</button>
                </form>
            </div>
        </div>
    @empty
        <p class="meta" style="text-align:center; padding:20px 0;">Nog geen vragen. <a href="{{ route('admin.polls.questions.create', $poll) }}" style="color:var(--accent);">Eerste vraag toevoegen →</a></p>
    @endforelse
</div>

</div>

{{-- TAB: Resultaten --}}
<div id="resultaten-content" style="display:none;">
@if($poll->questions->count() > 0)
<div class="card" style="margin-bottom:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <h2 style="margin:0; font-size:15px; text-transform:uppercase; letter-spacing:.05em; color:var(--muted);">Resultaten</h2>
    </div>
    @foreach($poll->questions as $qi => $question)
        @php
            $qTotal = $question->votes->whereNotNull('confirmed_at')->count();
        @endphp
        <div style="{{ !$loop->first ? 'margin-top:24px; padding-top:20px; border-top:1px solid var(--line);' : '' }}">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                <div style="width:22px;height:22px;border-radius:50%;background:var(--accent);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $qi+1 }}</div>
                <p style="margin:0; font-weight:600; font-size:15px;">{{ $question->question }}</p>
                <span class="meta" style="margin-left:auto; font-size:13px; white-space:nowrap;">{{ $qTotal }} stem{{ $qTotal !== 1 ? 'men' : '' }}</span>
            </div>
            @foreach($question->options as $option)
                @php
                    $cnt = $option->votes->whereNotNull('confirmed_at')->count();
                    $pct = $qTotal > 0 ? round(($cnt / $qTotal) * 100, 1) : 0;
                @endphp
                <div style="margin-bottom:10px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:14px;">
                        <span>{{ $option->label }}</span>
                        <span style="color:var(--muted);">{{ $cnt }} &bull; {{ $pct }}%</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <span style="width:{{ $pct }}%; transition:width .4s;"></span>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    {{-- Open Antwoorden Sectie --}}
    @php
        $openQuestions = $poll->questions->filter(fn($q) => \App\Support\PollType::isOpenTextType($q->type));
    @endphp
    @if($openQuestions->count() > 0)
        <h3 style="margin: 24px 0 16px; font-size: 14px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .05em;">💬 Open Antwoorden</h3>
        @foreach($openQuestions as $qindex => $question)
            @php
                $answers = $question->votes->whereNotNull('open_answer')->unique('email');
            @endphp
            @if($answers->count() > 0)
                <div style="margin-bottom: 12px; border: 1px solid var(--line); border-radius: 10px; overflow: hidden;">
                    {{-- Header (Clickable) --}}
                    <button onclick="document.getElementById('qa-{{ $question->id }}').style.display = document.getElementById('qa-{{ $question->id }}').style.display === 'none' ? 'block' : 'none';"
                            style="width: 100%; padding: 14px; background: var(--bg); border: none; text-align: left; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 14px; font-weight: 600; transition: background .15s;">
                        <span>{{ $question->question }} <span style="color: var(--muted); font-size: 12px; font-weight: 400;">({{ $answers->count() }} antwoorden)</span></span>
                        <span style="font-size: 18px; color: var(--muted);">▼</span>
                    </button>

                    {{-- Content (Uitklapbaar) --}}
                    <div id="qa-{{ $question->id }}" style="display: none; padding: 14px; background: #fff; max-height: 500px; overflow-y: auto;">
                        <div style="display: grid; gap: 10px;">
                            @foreach($answers as $vote)
                                <div style="padding: 12px; background: var(--bg); border-radius: 6px; border-left: 3px solid var(--primary);">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                                        <div style="flex: 1;">
                                            <p style="margin: 0 0 4px; font-weight: 500; font-size: 13px;">{{ $vote->respondent_name }}</p>
                                            <p style="margin: 0 0 8px; color: var(--text); font-size: 13px; line-height: 1.5; word-break: break-word;">{{ $vote->open_answer }}</p>
                                            <p style="margin: 0; color: var(--muted); font-size: 11px;">{{ $vote->created_at->format('d-m H:i') }}
                                                @if($vote->confirmed_at)
                                                    <span style="color: var(--success);"> • ✓</span>
                                                @else
                                                    <span style="color: #ea580c;"> • ⏳</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <style>
            button[onclick*="qa-"]:hover { background: var(--line) !important; }
        </style>
    @endif
</div>
@endif
</div>

{{-- TAB: Alle Stemmen --}}
<div id="stemmen-content" style="display:none;">
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="margin:0; font-size:15px; text-transform:uppercase; letter-spacing:.05em; color:var(--muted);">Binnengekomen stemmen</h2>
        <span class="meta" style="font-size:13px;">{{ $totalRespondents }} respondenten</span>
    </div>

{{-- Alle Stemmen Tabel --}}
@if($poll->votes->count() > 0)
    <h3 style="margin: 20px 0 16px; font-size: 14px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .05em;">📋 Alle Respondenten</h3>
    <div style="display: grid; gap: 12px;">
        @php
            $respondents = $poll->votes->groupBy('email')->map(function($group) {
                return [
                    'name' => $group->first()->respondent_name,
                    'email' => $group->first()->email,
                    'age' => $group->first()->age,
                    'created_at' => $group->first()->created_at,
                    'confirmed' => $group->first()->confirmed_at,
                    'vote_id' => $group->first()->id
                ];
            })->values();
        @endphp
        @foreach($respondents as $index => $respondent)
            <div class="respondent-item" style="border: 1px solid var(--line); border-radius: 10px; background: var(--surface); overflow: hidden; transition: all 0.2s;">
                {{-- Collapsed Header (Always Visible) --}}
                <button onclick="this.closest('.respondent-item').querySelector('.respondent-expanded').style.display = this.closest('.respondent-item').querySelector('.respondent-expanded').style.display === 'none' ? 'block' : 'none'; this.querySelector('.toggle-icon').style.transform = this.closest('.respondent-item').querySelector('.respondent-expanded').style.display === 'none' ? 'rotate(0deg)' : 'rotate(180deg)';" style="width: 100%; padding: 12px 16px; border: none; background: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='none'">
                    <div style="display: flex; align-items: center; gap: 10px; flex: 1; text-align: left;">
                        <span style="font-size: 16px; font-weight: 700; color: var(--primary); min-width: 32px;">{{ $index + 1 }}</span>
                        <div>
                            <p style="margin: 0; font-weight: 700; font-size: 14px;">{{ $respondent['name'] }}</p>
                            <p style="margin: 0; color: var(--muted); font-size: 12px;">{{ $respondent['email'] }}</p>
                        </div>
                    </div>
                    <span class="toggle-icon" style="font-size: 18px; color: var(--muted); transition: transform 0.2s;">▼</span>
                </button>

                {{-- Expanded Details --}}
                <div class="respondent-expanded" style="display: none; padding: 12px 16px; border-top: 1px solid var(--line); background: var(--bg);">
                    <div style="display: flex; gap: 16px; margin-bottom: 12px; font-size: 13px; flex-wrap: wrap;">
                        <span><strong>Leeftijd:</strong> {{ $respondent['age'] }}</span>
                        <span><strong>Datum:</strong> {{ $respondent['created_at']->format('d-m-Y H:i') }}</span>
                        @if($respondent['confirmed'])
                            <span style="color: var(--success); font-weight: 700;">✓ Bevestigd</span>
                        @else
                            <span style="color: #ea580c; font-weight: 700;">⏳ Wacht</span>
                        @endif
                    </div>
                    <form method="post" action="{{ route('admin.polls.votes.destroy', [$poll, $respondent['vote_id']]) }}" onsubmit="return confirm('Respondent {{ $respondent['name'] }} en alle antwoorden verwijderen?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger" type="submit" style="font-size: 12px; padding: 6px 10px;">✕ Verwijderen</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p class="meta" style="text-align:center; padding:20px 0;">Nog geen stemmen binnengekomen.</p>
@endif
</div>
</div>

@endsection
