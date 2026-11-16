    @extends('layouts.app')

    @section('content')

    {{-- Poll switcher --}}
    @if($allPolls->count() > 1)
    <div style="margin-bottom: 28px;">
        <p style="margin: 0 0 12px; font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Open vragenlijsten</p>
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

    {{-- Introductie --}}
    <div class="card" style="margin-bottom:24px; padding:24px; background:linear-gradient(135deg, var(--accent) 0%, #a7d8de 100%); color:#fff; border:none;">
        <h2 style="margin:0 0 12px; font-size:18px; font-weight:700; color:#fff;">Welkom bij deze vragenlijst</h2>
        <p style="margin:0 0 8px; line-height:1.6; font-size:15px; color:#fff;">
            Wij horen graag wat u ervan vindt. Het invullen kost een paar minuten.
        </p>
    </div>

    {{-- Info over het proces --}}
    <div class="card" style="margin-bottom:16px; padding:20px; background:#dbeafe; border:1px solid #bfdbfe;">
        <h3 style="margin:0 0 12px; color:var(--primary); display:flex; align-items:center; gap:8px;">
            Zo werkt het
        </h3>
        <p style="margin:0 0 8px; color:#1e40af; font-size:14px; line-height:1.6;">
            @if($poll->anonymous)
            <strong>1. Anoniem.</strong> Wij vragen niet naar uw naam, e-mailadres of telefoonnummer. Op één apparaat kunnen hooguit drie mensen uit hetzelfde huis de vragenlijst invullen.
            @else
            <strong>1. Uw gegevens.</strong> Wij vragen uw naam en e-mailadres. Uw telefoonnummer is niet verplicht.
            @endif
        </p>
        <p style="margin:0 0 8px; color:#1e40af; font-size:14px; line-height:1.6;">
            <strong>2. De vragen.</strong> Wij zijn heel benieuwd naar wat u vindt, wilt u alle vragen beantwoorden? Het zijn er veel maar we hebben het met cijfers zo makkelijk mogelijk proberen te maken.
        </p>
        @if($poll->requires_confirmation && ! $poll->anonymous)
        <p style="margin:0 0 8px; color:#1e40af; font-size:14px; line-height:1.6;">
            <strong>3. Versturen en bevestigen.</strong> Na het versturen krijgt u een e-mail met een link.
            Klikt u binnen zeven dagen op die link, dan tellen uw antwoorden mee.
        </p>
        @else
        <p style="margin:0 0 8px; color:#1e40af; font-size:14px; line-height:1.6;">
            <strong>3. Versturen.</strong> Uw antwoorden tellen direct mee.
        </p>
        @endif
        <p style="margin:0; color:#1e40af; font-size:13px; opacity:0.9; font-style:italic;">
            Bedankt voor uw medewerking.
        </p>
    </div>

    {{-- Poll header --}}
    <div class="card" style="margin-bottom:16px; padding:24px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:24px; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <h1 style="margin:0 0 6px; font-size:24px;">{{ $poll->title }}</h1>
                @if($poll->question)
                    <p style="margin:0; color:var(--muted); font-size:15px;">{{ $poll->question }}</p>
                @endif
            </div>
            @php($orgNaam = $poll->organisatie_naam ?: $poll->user?->name)
            @php($orgLogo = $poll->organisatie_logo ?: $poll->user?->logo)
            @if($orgNaam && ! $poll->organisatie_logo)
                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap; flex-shrink:0; padding:12px; background:rgba(59, 130, 246, 0.05); border-radius:12px; max-width:100%;">
                    @if($orgLogo)
                        <img src="{{ asset('storage/' . $orgLogo) }}" alt="{{ $orgNaam }}"
                            style="height:90px; width:auto; max-width:min(240px, 100%); object-fit:contain; border-radius:12px; background:#fff; padding:6px;">
                    @else
                        <div style="height:100px; width:100px; border-radius:50%; background:var(--accent-soft); display:flex; align-items:center; justify-content:center; border:3px solid var(--primary); box-shadow:0 4px 12px rgba(59, 130, 246, 0.2);">
                            <span style="font-size:44px; color:var(--accent); font-weight:700;">{{ mb_substr($orgNaam, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <p style="margin:0 0 4px; font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Organisatie</p>
                        <p style="margin:0; font-weight:700; font-size:16px; color:var(--primary);">{{ $orgNaam }}</p>
                        @if($poll->user->email)
                                
                        @endif
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

    @if($needsAccessCode)
        {{-- Access Code Form --}}
        <div class="card" style="margin-bottom:16px; padding:20px; background:#fef3c7; border:2px solid #fbbf24;">
            <h2 style="margin:0 0 12px; font-size:16px; color:#92400e; font-weight:600;">Toegangscode</h2>
            <p style="margin:0 0 16px; color:#b45309; font-size:14px;">Voor deze vragenlijst heeft u een toegangscode nodig. Vul de code in om te beginnen.</p>
            
            @if($errors->has('error'))
                <div style="padding:12px; background:#fee2e2; border:1px solid #fecaca; border-radius:8px; margin-bottom:12px; color:#991b1b; font-size:14px;">
                    {{ $errors->first('error') }}
                </div>
            @endif

            <form method="post" action="{{ route('polls.verify-access-code', $poll) }}" style="display:flex; gap:8px;">
                @csrf
                <input type="text" name="access_code" placeholder="Code" 
                    style="flex:1; padding:10px 12px; border:1px solid #fbbf24; border-radius:6px; font-size:14px; text-transform:uppercase;"
                    required autofocus>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px; white-space:nowrap;">Verder</button>
            </form>
        </div>
    @elseif($alreadyOnDevice ?? false)
        <div class="card" style="padding:24px; text-align:center;">
            <p style="margin:0; font-size:16px;">Deze vragenlijst is op dit apparaat ingevuld. Bedankt voor uw medewerking.</p>
            @if($deviceRoom ?? false)
                <p style="margin:16px 0 8px; font-size:14px; color:var(--muted);">Wil iemand anders in huis de vragenlijst ook invullen op dit apparaat?</p>
                <a href="{{ route('polls.show', $poll) }}?nog=1" class="btn btn-primary" style="padding:10px 24px;">Nog iemand in huis vult in</a>
            @endif
        </div>
    @elseif($poll->isOpen())

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
            {{-- Spamrem: ondertekend openingsmoment en een lokveld dat mensen niet zien (Formulierrem) --}}
            <input type="hidden" name="geopend" value="{{ \App\Support\Formulierrem::kaartje((string) request()->ip()) }}">
            <div class="lokveld" aria-hidden="true"><label for="website">Laat dit veld leeg</label><input id="website" type="text" name="website" value="" tabindex="-1" autocomplete="off"></div>

            {{-- Persoonlijke gegevens (niet bij een anonieme vragenlijst) --}}
            @unless($poll->anonymous)
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
                <div class="grid grid-2">
                    <div class="field">
                        <label for="phone">Telefoonnummer</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="0612345678" pattern="[0-9+\s-]*">
                        @error('phone')<p class="vote-err">{{ $message }}</p>@enderror
                    </div>
                    {{-- Vraagt de poll zelf al naar leeftijd, dan dit losse veld niet ook nog tonen. --}}
                    @unless($poll->questions->contains(fn ($q) => str_contains(mb_strtolower($q->question), 'leeftijd')))
                    <div class="field">
                        <label for="age">Leeftijd</label>
                        <input id="age" type="number" name="age" max="120" value="{{ old('age') }}" placeholder="bv. 28">
                        @error('age')<p class="vote-err">{{ $message }}</p>@enderror
                    </div>
                    @endunless
                </div>
            </div>
            @endunless

            {{-- Vragen --}}
            @if($poll->questions && $poll->questions->count() > 0)

                @if($poll->questions->count() > 5)
                <div class="voortgang" id="voortgang" aria-live="polite">
                    <span id="voortgang-tekst">0 van {{ $poll->questions->count() }} vragen beantwoord</span>
                    <div class="voortgang-balk"><span id="voortgang-vul"></span></div>
                </div>
                @endif

                @foreach($poll->questions as $index => $question)
                <div class="card" style="margin-bottom:16px; padding:20px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                        <div style="width:28px; height:28px; border-radius:50%; background:var(--accent); color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $index+1 }}</div>
                        <h3 style="margin:0; font-size:17px; line-height:1.3;">{{ $question->question }}</h3>
                    </div>
                    @if($question->help_text)
                        <p style="margin:-8px 0 14px 38px; font-size:14px; color:var(--muted); line-height:1.5; white-space:pre-line;">{{ $question->help_text }}</p>
                    @endif

                    @if(\App\Support\PollType::isOpenTextType($question->type))
                        <div class="field" style="margin:0;">
                            <textarea name="question_{{ $question->id }}" rows="4"
                                    placeholder="Typ hier uw antwoord (niet verplicht)…"
                                    style="resize:vertical;">{{ old('question_'.$question->id) }}</textarea>
                        </div>

                    @elseif($question->type === \App\Support\PollType::RATING_STARS)
                        <div class="star-wrap">
                            {{-- Waarde = id van de optie (vroeger 1..5, wat als optie-id werd gelezen en dus leeg werd opgeslagen) --}}
                            @foreach($question->options->sortByDesc('position') as $option)
                                @php($sid = 'q'.$question->id.'_o'.$option->id)
                                <input id="{{ $sid }}" class="star-inp" type="radio"
                                    name="question_{{ $question->id }}" value="{{ $option->id }}"
                                    @checked((string) old('question_'.$question->id) === (string) $option->id)>
                                <label for="{{ $sid }}" class="star-lbl" title="{{ $option->label }}">★</label>
                            @endforeach
                        </div>

                    @else
                        {{-- Cijferrij: alle opties zijn getallen, eventueel plus "Weet ik niet" (die komt eronder) --}}
                        @php($cijfers = $question->options->filter(fn ($o) => ctype_digit((string) $o->label)))
                        @php($overig = $question->options->reject(fn ($o) => ctype_digit((string) $o->label)))
                        @php($isScore = $cijfers->count() >= 5 && $overig->every(fn ($o) => $o->label === 'Weet ik niet'))
                        {{-- Gewone keuzes gelijkmatig over de regels: 7 antwoorden = 4 + 3, niet 6 + 1 (een los laatste antwoord werd over het hoofd gezien) --}}
                        @php($nOpt = $question->options->count())
                        @php($kol = $nOpt > 0 ? (int) ceil($nOpt / ceil($nOpt / 6)) : 1)
                        <div class="choice-grid{{ $isScore ? ' choice-grid--score' : ' choice-grid--kol' }}" style="{{ $isScore ? '--n: '.$cijfers->count().';' : '--kol: '.$kol.';' }}">
                            @if($question->options && $question->options->count() > 0)
                                @foreach($isScore ? $cijfers : $question->options as $option)
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
                        @if($question->type === \App\Support\PollType::SCORE_1_10)
                            <div class="score-hint"><span>1 = heel slecht</span><span>10 = uitstekend</span></div>
                        @endif
                        @if($isScore)
                            @foreach($overig as $option)
                                @php($oid = 'q'.$question->id.'_o'.$option->id)
                                <div class="score-weetniet">
                                    <input class="choice-inp" id="{{ $oid }}" type="radio"
                                        name="question_{{ $question->id }}" value="{{ $option->id }}"
                                        @checked((string) old('question_'.$question->id) === (string) $option->id)>
                                    <label class="choice-lbl" for="{{ $oid }}">{{ $option->label }}</label>
                                </div>
                            @endforeach
                        @endif
                        @if($question->met_toelichting)
                            @php($tk = 'toelichting_'.$question->id)
                            <details class="toelichting" @if(old($tk)) open @endif>
                                <summary>Wilt u dit toelichten?</summary>
                                <textarea name="{{ $tk }}" rows="3" maxlength="2000" placeholder="Niet verplicht">{{ old($tk) }}</textarea>
                            </details>
                        @endif
                    @endif

                    @error('question_'.$question->id)
                        <p class="vote-err" style="margin-top:10px;">Dit antwoord is verplicht.</p>
                    @enderror
                </div>
                @endforeach

                {{-- Submit --}}
                <div class="card" style="padding:20px; text-align:center;">
                    <p style="margin:0 0 14px; color:var(--muted); font-size:14px;">
                        @if($poll->anonymous)
                            Op één apparaat kunnen hooguit drie mensen de vragenlijst invullen.
                        @elseif($poll->requires_confirmation)
                            Na het versturen ontvangt u een e-mail. Klik op de link in die mail om uw antwoorden te bevestigen.
                        @else
                            U kunt de lijst één keer per e-mailadres invullen.
                        @endif
                    </p>
                    <button type="submit" class="btn btn-primary"
                            style="padding:12px 40px; font-size:16px; border-radius:10px; width:100%; max-width:360px;">
                        Antwoorden versturen
                    </button>
                </div>
            @else
                <div class="card" style="padding:24px; text-align:center;">
                    <p style="font-size:32px; margin:0 0 8px;">⚠️</p>
                    <p style="margin:0; font-size:16px;">Deze vragenlijst bevat nog geen vragen.</p>
                </div>
            @endif
        </form>

    @elseif($poll->opens_at && now()->lt($poll->opens_at))
        <div class="card" style="padding:24px; text-align:center;">
            <p style="font-size:32px; margin:0 0 8px;">📅</p>
            <p style="margin:0; font-size:16px;">Deze vragenlijst is nog niet open.<br>
            <strong>Start op {{ $poll->opens_at->format('d-m-Y \o\m H:i') }}</strong></p>
        </div>
    @else
        <div class="card" style="padding:24px; text-align:center;">
            <p style="font-size:32px; margin:0 0 8px;">🔒</p>
            <p style="margin:0; font-size:16px;">Deze vragenlijst is gesloten.</p>
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

    @media (min-width: 641px) {
        .choice-grid--kol { grid-template-columns: repeat(var(--kol, 3), minmax(0, 1fr)); }
    }

    /* Cijfers 1-10: één rij op breed scherm, twee rijen van vijf op de telefoon */
    .choice-grid--score {
        grid-template-columns: repeat(var(--n, 10), minmax(0, 1fr));
        gap: 6px;
    }
    .choice-grid--score .choice-lbl {
        padding: 10px 0;
        font-size: 15px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .choice-grid--score .choice-lbl:hover { transform: none; }
    .score-hint {
        display: flex;
        justify-content: space-between;
        margin-top: 6px;
        font-size: 12px;
        color: var(--muted);
    }
    @media (max-width: 520px) {
        .choice-grid--score { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .choice-grid--score[style*="--n: 11"] { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    }

    .lokveld { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
    .score-weetniet { margin-top: 8px; }
    .toelichting { margin-top: 10px; }
    .toelichting summary { cursor: pointer; font-size: 14px; color: var(--primary); }
    .toelichting textarea { margin-top: 8px; width: 100%; resize: vertical; }
    .score-weetniet .choice-lbl { padding: 8px 16px; font-size: 14px; font-weight: 500; color: var(--muted); }

    /* Voortgang */
    .voortgang {
        position: sticky; top: 0; z-index: 5;
        margin: 0 0 16px; padding: 10px 14px;
        background: var(--surface); border: 1px solid var(--line); border-radius: 10px;
        font-size: 14px; color: var(--muted);
    }
    .voortgang-balk { margin-top: 6px; height: 6px; border-radius: 3px; background: var(--line); overflow: hidden; }
    .voortgang-balk span { display: block; height: 100%; width: 0; background: var(--primary); transition: width .2s; }

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

    <script nonce="{{ $cspNonce ?? '' }}">
    (function () {
        var tekst = document.getElementById('voortgang-tekst');
        if (!tekst) return;
        var vul = document.getElementById('voortgang-vul');
        var form = tekst.closest('form');
        var namen = [];
        form.querySelectorAll('[name^="question_"]').forEach(function (el) {
            if (namen.indexOf(el.name) === -1) namen.push(el.name);
        });
        function tel() {
            var klaar = 0;
            namen.forEach(function (n) {
                var els = form.querySelectorAll('[name="' + n + '"]');
                var ja = false;
                els.forEach(function (el) {
                    if ((el.type === 'radio' && el.checked) || (el.tagName === 'TEXTAREA' && el.value.trim() !== '')) ja = true;
                });
                if (ja) klaar++;
            });
            tekst.textContent = klaar + ' van ' + namen.length + ' vragen beantwoord';
            vul.style.width = (namen.length ? Math.round(klaar / namen.length * 100) : 0) + '%';
        }
        form.addEventListener('change', tel);
        form.addEventListener('input', tel);
        tel();
    })();
    </script>

    <script nonce="{{ $cspNonce ?? '' }}">
    // Scroll to top when page loads if there's a success or error message
    document.addEventListener('DOMContentLoaded', () => {
        const flashElement = document.querySelector('.flash');
        if (flashElement) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
    </script>

    @endsection
