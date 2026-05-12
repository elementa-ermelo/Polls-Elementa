@extends('layouts.app')

@section('content')

{{-- Poll switcher --}}
@if($allPolls->count() > 1)
<div style="margin-bottom: 28px;">
    <p style="margin: 0 0 12px; font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Beschikbare Polls</p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        @foreach($allPolls as $p)
            <a href="{{ route('polls.show', $p) }}"
               style="padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 600; transition: all 0.2s;
                      {{ $p->id === $poll->id
                            ? 'background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #fff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);'
                            : 'background: var(--surface); color: var(--primary); border: 1.5px solid var(--primary);'
                      }}
               ">
                {{ $p->title }}
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- Poll header --}}
<div class="card" style="margin-bottom:16px; padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1 style="margin:0 0 6px; font-size:24px;">{{ $poll->title }}</h1>
            @if($poll->question)
                <p style="margin:0; color:var(--muted); font-size:15px;">{{ $poll->question }}</p>
            @endif
        </div>
        @if($poll->user)
            <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                @if($poll->user->logo)
                    <img src="{{ asset('storage/' . $poll->user->logo) }}" alt="{{ $poll->user->name }}"
                         style="height:44px; width:44px; border-radius:50%; object-fit:cover; border:2px solid var(--line);">
                @else
                    <div style="height:44px; width:44px; border-radius:50%; background:var(--accent-soft); display:flex; align-items:center; justify-content:center;">
                        <span style="font-size:17px; color:var(--accent); font-weight:700;">{{ substr($poll->user->name,0,1) }}</span>
                    </div>
                @endif
                <div>
                    <p style="margin:0; font-size:11px; color:var(--muted);">Aangemaakt door</p>
                    <p style="margin:0; font-weight:600; font-size:14px;">{{ $poll->user->name }}</p>
                </div>
            </div>
        @endif
    </div>
    @if($poll->closes_at && $poll->isOpen())
        <div style="margin-top:14px; padding:8px 12px; background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; font-size:13px; color:#9a3412;">
            Sluit op {{ $poll->closes_at->format('d-m-Y \o\m H:i') }}
        </div>
    @endif
</div>

@if($poll->isOpen())

    {{-- Foutmeldingen --}}
    @if($errors->any())
        <div style="padding:14px 16px; background:#fee2e2; border:1px solid #fecaca; border-radius:10px; margin-bottom:16px;">
            <p style="margin:0 0 8px; font-weight:700; color:#991b1b;">Let op:</p>
            <ul style="margin:0; padding-left:20px; color:#991b1b; font-size:14px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('polls.vote', $poll) }}">
        @csrf

        {{-- Persoonlijke gegevens --}}
        <div class="card" style="margin-bottom:16px; padding:20px;">
            <h2 style="margin:0 0 16px; font-size:16px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Uw gegevens</h2>
            <div class="grid grid-2">
                <div class="field">
                    <label for="respondent_name">Naam *</label>
                    <input id="respondent_name" name="respondent_name" value="{{ old('respondent_name') }}" placeholder="Uw volledige naam" required>
                    @error('respondent_name')<p class="vote-err">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="email">E-mailadres *</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="naam@voorbeeld.nl" required>
                    @error('email')<p class="vote-err">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="field" style="max-width:200px;">
                <label for="age">Leeftijd *</label>
                <input id="age" type="number" name="age" min="16" max="120" value="{{ old('age') }}" placeholder="bv. 28" required>
                @error('age')<p class="vote-err">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Vragen --}}
        @if($poll->questions && $poll->questions->count() > 0)
            @foreach($poll->questions as $index => $question)
            <div class="card" style="margin-bottom:16px; padding:20px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                    <div style="width:28px; height:28px; border-radius:50%; background:var(--accent); color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $index+1 }}</div>
                    <h3 style="margin:0; font-size:17px; line-height:1.3;">{{ $question->question }}</h3>
                </div>

                @if(\App\Support\PollType::isOpenTextType($question->type))
                    <div class="field" style="margin:0;">
                        <textarea name="question_{{ $question->id }}" rows="4"
                                  placeholder="Typ hier uw antwoord…"
                                  style="resize:vertical;">{{ old('question_'.$question->id) }}</textarea>
                    </div>

                @elseif($question->type === \App\Support\PollType::RATING_STARS)
                    <div class="star-wrap">
                        @foreach(range(5,1) as $val)
                            @php($sid = 'q'.$question->id.'_r'.$val)
                            <input id="{{ $sid }}" class="star-inp" type="radio"
                                   name="question_{{ $question->id }}" value="{{ $val }}"
                                   @checked((string) old('question_'.$question->id) === (string) $val)>
                            <label for="{{ $sid }}" class="star-lbl" title="{{ $val }} ster{{ $val > 1 ? 'ren' : '' }}">★</label>
                        @endforeach
                    </div>

                @else
                    <div class="choice-grid">
                        @if($question->options && $question->options->count() > 0)
                            @foreach($question->options as $option)
                                @php($oid = 'q'.$question->id.'_o'.$option->id)
                                <div>
                                    <input class="choice-inp" id="{{ $oid }}" type="radio"
                                           name="question_{{ $question->id }}" value="{{ $option->id }}"
                                           @checked((string) old('question_'.$question->id) === (string) $option->id)>
                                    <label class="choice-lbl" for="{{ $oid }}">{{ $option->label }}</label>
                                </div>
                            @endforeach
                        @else
                            <p style="color:var(--muted); font-size:14px;">Geen antwoordopties beschikbaar.</p>
                        @endif
                    </div>
                @endif

                @error('question_'.$question->id)
                    <p class="vote-err" style="margin-top:10px;">Dit antwoord is verplicht.</p>
                @enderror
            </div>
            @endforeach

            {{-- Submit --}}
            <div class="card" style="padding:20px; text-align:center;">
                <p style="margin:0 0 14px; color:var(--muted); font-size:14px;">
                    Na het versturen ontvangt u een bevestigingsmail om uw stem te bevestigen.
                </p>
                <button type="submit" class="btn btn-primary"
                        style="padding:12px 40px; font-size:16px; border-radius:10px; width:100%; max-width:360px;">
                    Verstuur mijn antwoorden →
                </button>
            </div>
        @else
            <div class="card" style="padding:24px; text-align:center;">
                <p style="font-size:32px; margin:0 0 8px;">⚠️</p>
                <p style="margin:0; font-size:16px;">Deze poll bevat nog geen vragen.</p>
            </div>
        @endif
    </form>

@elseif($poll->opens_at && now()->lt($poll->opens_at))
    <div class="card" style="padding:24px; text-align:center;">
        <p style="font-size:32px; margin:0 0 8px;">📅</p>
        <p style="margin:0; font-size:16px;">Deze poll is nog niet open.<br>
           <strong>Start op {{ $poll->opens_at->format('d-m-Y \o\m H:i') }}</strong></p>
    </div>
@else
    <div class="card" style="padding:24px; text-align:center;">
        <p style="font-size:32px; margin:0 0 8px;">🔒</p>
        <p style="margin:0; font-size:16px;">Deze poll is gesloten. Bedankt voor uw interesse.</p>
    </div>
@endif

<style>
.vote-err { color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600; }

/* Radio keuze */
.choice-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}
.choice-inp {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
}
.choice-lbl {
    display: block;
    margin: 0;
    padding: 12px 16px;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    background: var(--surface);
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s;
    text-align: center;
}
.choice-lbl:hover {
    border-color: var(--primary);
    background: rgba(59, 130, 246, 0.08);
    transform: translateY(-2px);
}
.choice-inp:checked + .choice-lbl {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    font-weight: 700;
    color: var(--primary);
}

/* Sterren */
.star-wrap {
    display: inline-flex;
    flex-direction: row-reverse;
    gap: 8px;
    margin: 0;
}
.star-inp {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
}
.star-lbl {
    margin: 0;
    font-size: 40px;
    line-height: 1;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.15s;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.05));
}
.star-lbl:hover,
.star-lbl:hover ~ .star-lbl,
.star-inp:checked ~ .star-lbl {
    color: #f59e0b;
    filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.4));
    transform: scale(1.1);
}
.star-inp:checked ~ .star-lbl {
    transform: scale(1.15);
}
</style>

@endsection
