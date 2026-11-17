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
        <?php
            // aantal kaarten zoals de invuller ze ziet: een themakaart telt één keer, een standaard verborgen vervolgvraag niet
            $kaartAantal = 0; $heeftGroepen = false; $vorigeG = null;
            foreach ($poll->questions->values() as $qi => $q) {
                $g = trim((string) $q->groep) ?: null;
                $heeftGroepen = $heeftGroepen || $g !== null;
                $vorigeQ = $poll->questions->values()->get($qi - 1);
                if (! ($vorigeQ && $vorigeQ->vervolg_bij) && ($g === null || $g !== $vorigeG)) { $kaartAantal++; }
                $vorigeG = $g;
            }
        ?>
        <p style="margin:0 0 8px; color:#1e40af; font-size:14px; line-height:1.6;">
            @if($heeftGroepen)
            <strong>2. De vragen.</strong> {{ $kaartAantal }} kaarten, elk met vragen over één onderwerp, brengen u vanzelf naar de finish. Bij elke kaart gaat er bovenaan een lampje van de slinger branden. Wij zijn heel benieuwd naar wat u vindt, dank u wel alvast.
            @else
            <strong>2. De vragen.</strong> Wij zijn heel benieuwd naar wat u vindt, wilt u alle vragen beantwoorden? Het zijn er veel maar we hebben het met cijfers zo makkelijk mogelijk proberen te maken. Wij kunnen uw antwoorden goed gebruiken, dank u wel alvast.
            @endif
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
        {{-- geen los bedankje meer: punt 2 zegt al "dank u wel alvast" (IJme, 17 nov) --}}
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
        @if(session('success'))
            @include('polls.partials.beloning')
        @endif
        <div class="card" style="padding:24px; text-align:center;">
            @unless(session('success'))
            <p style="margin:0; font-size:16px;">Deze vragenlijst is op dit apparaat ingevuld. Bedankt voor uw medewerking.</p>
            @endunless
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

        <form method="post" action="{{ route('polls.vote', $poll) }}" data-vragenlijst>
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
                    <span id="voortgang-tekst">0 van {{ $heeftGroepen ? $kaartAantal.' kaarten' : $poll->questions->count().' vragen' }} beantwoord</span>
                    <div class="slinger" id="slinger" aria-hidden="true"></div>
                    {{-- hardloper met startnummer 2040 die naar de finish loopt; sprint op het laatste stuk (IJme, 17 nov) --}}
                    <div class="baan" id="baan" aria-hidden="true">
                        <div class="baan-lijn"></div>
                        <svg class="baan-vlag" viewBox="0 0 40 72"><line x1="6" y1="2" x2="6" y2="72" stroke="#1f2a1f" stroke-width="2.5"/><rect x="7" y="3" width="28" height="20" fill="#fff" stroke="#1f2a1f" stroke-width="1.5"/><path fill="#1f2a1f" d="M7 3h7v5H7zM21 3h7v5h-7zM14 8h7v5h-7zM28 8h7v5h-7zM7 13h7v5H7zM21 13h7v5h-7zM14 18h7v5h-7zM28 18h7v5h-7z"/></svg>
                        <svg class="loper" id="loper" viewBox="0 0 70 94">
                            <g class="lijf">
                                <g transform="translate(33,50)"><g class="d d2"><line x1="0" y1="0" x2="0" y2="17" stroke="#d9a383" stroke-width="7" stroke-linecap="round"/><g transform="translate(0,17)"><g class="k k2"><line x1="0" y1="0" x2="0" y2="17" stroke="#d9a383" stroke-width="6" stroke-linecap="round"/><path d="M-3 15 h11 a3 3 0 0 1 0 6 h-11 z" fill="#3a3f47"/></g></g></g></g>
                                <g transform="translate(37,28)"><g class="a a2"><line x1="0" y1="0" x2="0" y2="12" stroke="#c99373" stroke-width="5.5" stroke-linecap="round"/><line x1="0" y1="12" x2="8" y2="18" stroke="#c99373" stroke-width="5" stroke-linecap="round"/></g></g>
                                <path d="M30 26 Q37 22 43 26 L41 50 L28 50 Z" fill="#2f9e44"/>
                                <rect x="31" y="31" width="11" height="9" rx="1.5" fill="#fff"/>
                                <text x="36.5" y="38" text-anchor="middle" font-size="5.2" font-weight="700" fill="#163a21" font-family="sans-serif">2040</text>
                                <path d="M27 47 L42 47 L43 55 L35 55 L34 52 L33 55 L26 55 Z" fill="#3b82f6"/>
                                <g transform="translate(33,50)"><g class="d d1"><line x1="0" y1="0" x2="0" y2="17" stroke="#e8b493" stroke-width="7" stroke-linecap="round"/><g transform="translate(0,17)"><g class="k k1"><line x1="0" y1="0" x2="0" y2="17" stroke="#e8b493" stroke-width="6" stroke-linecap="round"/><path d="M-3 15 h11 a3 3 0 0 1 0 6 h-11 z" fill="#e5484d"/></g></g></g></g>
                                <rect x="36" y="22" width="6" height="6" rx="2" fill="#e8b493"/>
                                <circle cx="40" cy="15" r="8" fill="#e8b493"/>
                                <path d="M32 14 Q32 5 41 6 Q48 7 47 13 Q43 10 38 11 Q35 12 34 16 Z" fill="#5a3a22"/>
                                <circle cx="44" cy="14.5" r="1" fill="#2a2a2a"/>
                                <path d="M43 19 q2 1 3.5 -0.5" stroke="#8a4b3a" stroke-width="1" fill="none" stroke-linecap="round"/>
                                <rect x="32.5" y="9" width="14" height="2.6" rx="1.3" fill="#f5c400"/>
                                <g transform="translate(35,28)"><g class="a a1"><line x1="0" y1="0" x2="0" y2="12" stroke="#e8b493" stroke-width="5.5" stroke-linecap="round"/><line x1="0" y1="12" x2="8" y2="18" stroke="#e8b493" stroke-width="5" stroke-linecap="round"/><path d="M-3.5 -1 h7 v6 h-7 z" fill="#2f9e44"/></g></g>
                                <circle class="zweet" cx="50" cy="10" r="1.8" fill="#3b82f6"/>
                            </g>
                        </svg>
                    </div>
                </div>
                @endif

                @foreach($poll->questions as $index => $question)
                @php($stopOptie = $question->stop_bij ? $question->options->firstWhere('label', $question->stop_bij) : null)
                @php($overOptie = $question->overslaan_bij ? $question->options->firstWhere('label', $question->overslaan_bij) : null)
                @php($vervolgOptie = $question->vervolg_bij ? $question->options->firstWhere('label', $question->vervolg_bij) : null)
                @php($mist = $errors->has('question_'.$question->id) || $errors->has('question_'.$question->id.'.*'))
                {{-- themakaart: opeenvolgende vragen met dezelfde groep op één kaart (IJme, 17 nov) --}}
                <?php
                    $groep = trim((string) $question->groep) ?: null;
                    $opentGroep = $groep && trim((string) $poll->questions->get($index - 1)?->groep) !== $groep;
                    $sluitGroep = $groep && trim((string) $poll->questions->get($index + 1)?->groep) !== $groep;
                    if ($opentGroep) {
                        $groepId = $question->id;
                        $groepToel = null;
                        for ($j = $index; $j < $poll->questions->count() && trim((string) $poll->questions->get($j)->groep) === $groep; $j++) {
                            if (! $groepToel && $poll->questions->get($j)->met_toelichting) { $groepToel = $poll->questions->get($j); }
                        }
                    }
                ?>
                @if($opentGroep)
                <div class="card themakaart" data-groep="{{ $groepId }}">
                    <div class="kaartkop">
                        <div class="vraagnr" style="width:28px; height:28px; border-radius:50%; background:var(--accent); color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $index+1 }}</div>
                        <h3>{{ $groep }}</h3>
                        <span class="nog-vragen" hidden></span>
                    </div>
                    @if($question->groep_uitleg)
                        <p class="kaart-uitleg">{{ $question->groep_uitleg }}</p>
                    @endif
                @endif
                <div class="{{ $groep ? 'vraagkaart in-groep' : 'card vraagkaart' }}{{ $mist ? ' mist' : '' }}"@if($groep) data-in-groep="{{ $groepId }}"@endif @if(! \App\Support\PollType::isOpenTextType($question->type) && ! $question->standaard_antwoord) data-verplicht @endif data-vraag="{{ $question->id }}" data-pos="{{ $question->position }}"@if($stopOptie) data-stop-optie="q{{ $question->id }}_o{{ $stopOptie->id }}"@endif @if($overOptie) data-sla-over="q{{ $question->id }}_o{{ $overOptie->id }}"@endif @if($vervolgOptie) data-vervolg-bij="q{{ $question->id }}_o{{ $vervolgOptie->id }}"@endif style="margin-bottom:16px; padding:20px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                        @unless($groep)
                        <div class="vraagnr" data-nr="{{ $index+1 }}" style="width:28px; height:28px; border-radius:50%; background:var(--accent); color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $index+1 }}</div>
                        @endunless
                        <h3 style="margin:0; font-size:17px; line-height:1.3;">{{ $groep && $question->kort ? $question->kort : $question->question }}</h3>
                        @unless($groep)
                        <span class="nog-vragen" hidden></span>
                        @endunless
                    </div>
                    @if(\App\Support\PollType::isMultiple($question->type))
                        <p style="margin:-8px 0 14px 38px; font-size:14px; color:var(--muted); line-height:1.5;">U kunt meer antwoorden aanvinken.</p>
                    @endif
                    @if($question->help_text)
                        <p style="margin:-8px 0 14px 38px; font-size:14px; color:var(--muted); line-height:1.5; white-space:pre-line;">{{ $question->help_text }}</p>
                    @elseif($question->standaard_antwoord)
                        <p style="margin:-8px 0 14px 38px; font-size:14px; color:var(--muted); line-height:1.5;">Niet verplicht. Geen antwoord telt als "{{ $question->standaard_antwoord }}".</p>
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
                        @php($cMin = $isScore ? (int) $cijfers->min(fn ($o) => (int) $o->label) : 0)
                        @php($cMax = $isScore ? (int) $cijfers->max(fn ($o) => (int) $o->label) : 1)
                        <div class="choice-grid{{ $isScore ? ' choice-grid--score' : ' choice-grid--kol' }}" style="{{ $isScore ? '--n: '.$cijfers->count().';' : '--kol: '.$kol.';' }}">
                            @if($question->options && $question->options->count() > 0)
                                @php($meer = \App\Support\PollType::isMultiple($question->type))
                                @foreach($isScore ? $cijfers : $question->options as $option)
                                    @php($oid = 'q'.$question->id.'_o'.$option->id)
                                    <div>
                                        @if($meer)
                                        {{-- "Geen van deze" / "Niets, ..." sluit de andere vinkjes uit --}}
                                        <input class="choice-inp" id="{{ $oid }}" type="checkbox"@if(preg_match('/^(Geen|Niets)\b/u', $option->label)) data-alleen @endif
                                            name="question_{{ $question->id }}[]" value="{{ $option->id }}"
                                            @checked(in_array((string) $option->id, array_map('strval', (array) old('question_'.$question->id, [])), true))>
                                        @else
                                        <input class="choice-inp" id="{{ $oid }}" type="radio"
                                            name="question_{{ $question->id }}" value="{{ $option->id }}"
                                            @checked((string) old('question_'.$question->id) === (string) $option->id)>
                                        @endif
                                        <label class="choice-lbl" for="{{ $oid }}"@if($isScore) style="--kleur: hsl({{ $cMax > $cMin ? round(((int) $option->label - $cMin) / ($cMax - $cMin) * 120) : 120 }}, 62%, 42%);"@endif>{{ $option->label }}</label>
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
                        @if($question->met_toelichting && ! $groep)
                            @php($tk = 'toelichting_'.$question->id)
                            <div class="toelichting" @unless(old($tk)) hidden @endunless>
                                <label for="{{ $tk }}">Wilt u dit toelichten? <span>Mag leeg blijven.</span></label>
                                <textarea id="{{ $tk }}" name="{{ $tk }}" rows="2" maxlength="2000" placeholder="Uw toelichting">{{ old($tk) }}</textarea>
                            </div>
                        @endif
                    @endif

                    {{-- zichtbaar zodra de kaart .mist heeft (na de controle bij versturen of een serverfout) --}}
                    <p class="vote-err mist-tekst" style="margin-top:12px;">Deze vraag is nog niet beantwoord.</p>
                </div>
                @if($stopOptie)
                <div class="card stop-melding" data-voor="{{ $question->id }}" hidden>
                    {{-- elke stop (buiten de regio, nooit in het centrum) dezelfde grote melding; "toch verder" blijft (IJme) --}}
                    <p class="stop-klaar">Dan zijn we nu klaar</p>
                    <p style="margin:0 0 12px; font-size:15px; line-height:1.5; text-align:center;">Bedankt voor uw belangstelling. U kunt hieronder versturen.</p>
                    <p style="margin:0; text-align:center;"><button type="button" class="btn toch-verder">Toch alle vragen invullen</button></p>
                </div>
                @endif
                @if($sluitGroep)
                    @if($groepToel)
                        @php($tk = 'toelichting_'.$groepToel->id)
                        <div class="toelichting" @unless(old($tk)) hidden @endunless>
                            <label for="{{ $tk }}">Wilt u hier iets over toelichten? <span>Mag leeg blijven.</span></label>
                            <textarea id="{{ $tk }}" name="{{ $tk }}" rows="2" maxlength="2000" placeholder="Uw toelichting">{{ old($tk) }}</textarea>
                        </div>
                    @endif
                </div>
                @endif
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
    .vraagkaart .mist-tekst { display: none; font-size: 15px; }
    .vraagkaart.mist { border: 2px solid var(--danger); }
    .vraagkaart.mist .mist-tekst { display: block; }

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
    /* Cijferkleuren: het gekozen cijfer kleurt van rood (laag) naar groen (hoog), met een veertje */
    .choice-grid--score .choice-inp:checked + .choice-lbl { background: var(--kleur, var(--primary)); border-color: var(--kleur, var(--primary)); color: #fff; box-shadow: none; animation: veer .35s ease-out; }
    @keyframes veer { 0% { transform: scale(.85); } 60% { transform: scale(1.12); } 100% { transform: scale(1); } }
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
    .toelichting { margin-top: 14px; animation: inschuiven .25s ease-out; }
    .toelichting label { display: block; font-size: 15px; font-weight: 600; color: var(--primary); }
    .toelichting label span { font-weight: 400; font-size: 13px; color: var(--muted); }
    .toelichting textarea { margin-top: 6px; width: 100%; resize: vertical; }
    @keyframes inschuiven { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }
    .nog-vragen { margin-left: auto; padding-left: 8px; font-size: 13px; color: var(--muted); white-space: nowrap; }
    .score-weetniet .choice-lbl { padding: 8px 16px; font-size: 14px; font-weight: 500; color: var(--muted); }

    /* Voortgang */
    .voortgang {
        position: sticky; top: 60px; z-index: 5; /* onder de vaste balk met het logo (60px) */
        margin: 0 0 16px; padding: 10px 14px;
        background: var(--surface); border: 1px solid var(--line); border-radius: 10px;
        font-size: 14px; color: var(--muted);
    }
    /* Slimme sprong */
    .vraagkaart.overgeslagen { display: none; }
    .stop-melding { margin-bottom: 16px; padding: 20px; background: #f0f9e8; border: 1px solid #c6e3a8; }
    .stop-klaar { margin: 0 0 10px; font-size: 30px; line-height: 1.2; font-weight: 700; color: #2f6f2f; text-align: center; }

    /* Lichtjesslinger als voortgang: elk beantwoorde vraag een lampje */
    .slinger { display: flex; gap: 2px; align-items: flex-start; width: 100%; margin: 8px auto 0; padding-top: 3px; border-top: 1px solid #9ca3af; }
    .voortgang #voortgang-tekst { display: block; text-align: center; }
    .slinger .lamp { flex: 1 1 0; aspect-ratio: 3 / 4; border-radius: 50% 50% 45% 45%; background: #d1d5db; position: relative; }
    .slinger .lamp:nth-child(odd) { margin-top: 3px; }
    .slinger .lamp::before { content: ''; position: absolute; top: -3px; left: 30%; width: 40%; height: 3px; background: #6b7280; border-radius: 1px; }
    .slinger .lamp.aan:nth-child(4n+1) { background: #f5c400; box-shadow: 0 0 6px 2px rgba(245, 196, 0, .65); }
    .slinger .lamp.aan:nth-child(4n+2) { background: #e5484d; box-shadow: 0 0 6px 2px rgba(229, 72, 77, .55); }
    .slinger .lamp.aan:nth-child(4n+3) { background: #2f9e44; box-shadow: 0 0 6px 2px rgba(47, 158, 68, .55); }
    .slinger .lamp.aan:nth-child(4n+4) { background: #3b82f6; box-shadow: 0 0 6px 2px rgba(59, 130, 246, .55); }
    .slinger .lamp.aan { animation: aangaan .45s ease-out; }
    @keyframes aangaan { 0% { transform: scale(.6); filter: brightness(2); } 60% { transform: scale(1.25); } 100% { transform: scale(1); } }

    /* Hardloper naar de finish (onder de slinger) */
    .baan { position: relative; height: 62px; margin: 6px auto 0; --f: 0; }
    .baan-lijn { position: absolute; left: 0; right: 30px; bottom: 4px; height: 2px; background: #c8cfc9; }
    .baan-vlag { position: absolute; right: 2px; bottom: 4px; width: 26px; height: 46px; }
    .loper { position: absolute; bottom: 1px; width: 43px; height: 58px; left: calc((100% - 30px - 43px) * var(--f)); transition: left .7s ease-out; --t: .62s; }
    .loper.sprint { --t: .36s; }
    .loper g.d, .loper g.k, .loper g.a, .loper g.lijf { transform-origin: 0 0; }
    .loper .lijf { animation: wip calc(var(--t) / 2) ease-in-out infinite; }
    .loper .d1 { animation: dij var(--t) linear infinite; }
    .loper .d2 { animation: dij var(--t) linear infinite; animation-delay: calc(var(--t) / -2); }
    .loper .k1 { animation: knie var(--t) linear infinite; }
    .loper .k2 { animation: knie var(--t) linear infinite; animation-delay: calc(var(--t) / -2); }
    .loper .a1 { animation: arm var(--t) ease-in-out infinite; animation-delay: calc(var(--t) / -2); }
    .loper .a2 { animation: arm var(--t) ease-in-out infinite; }
    .loper .zweet { opacity: 0; }
    .loper.sprint .zweet { opacity: 1; animation: druppel .45s linear infinite; }
    .loper.binnen .d1, .loper.binnen .d2, .loper.binnen .k1, .loper.binnen .k2, .loper.binnen .lijf { animation: none; }
    .loper.binnen .a1, .loper.binnen .a2 { animation: none; transform: rotate(-165deg); }
    .loper.binnen { animation: springen .55s ease-out 2; }
    @keyframes dij { 0% { transform: rotate(-38deg); } 50% { transform: rotate(30deg); } 100% { transform: rotate(-38deg); } }
    @keyframes knie { 0% { transform: rotate(8deg); } 35% { transform: rotate(12deg); } 55% { transform: rotate(55deg); } 75% { transform: rotate(95deg); } 100% { transform: rotate(8deg); } }
    @keyframes arm { 0% { transform: rotate(-45deg); } 50% { transform: rotate(40deg); } 100% { transform: rotate(-45deg); } }
    @keyframes wip { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-3px); } }
    @keyframes druppel { from { transform: translate(0, 0); opacity: 1; } to { transform: translate(-9px, 7px); opacity: 0; } }
    @keyframes springen { 50% { transform: translateY(-12px); } }
    @media (max-width: 520px) { .baan { height: 44px; margin-top: 4px; } .baan-vlag { width: 20px; height: 36px; } .loper { width: 31px; height: 42px; left: calc((100% - 30px - 31px) * var(--f)); } }
    @media (prefers-reduced-motion: reduce) { .loper, .loper * { animation: none !important; transition: none; } }

    /* Kaarten duidelijk van elkaar: bovenrand in de kleur van het eigen lampje, schaduw, ruimte (IJme, 17 nov) */
    .card.vraagkaart, .card.themakaart {
        margin-bottom: 28px !important; padding-top: 26px !important;
        border-color: #d5ddd6;
        box-shadow: inset 0 6px 0 var(--lamp, #3b82f6), 0 10px 28px rgba(22, 58, 33, .12);
    }

    /* Themakaarten: meer vragen op één kaart */
    .themakaart { margin-bottom: 16px; padding: 20px; }
    .themakaart.overgeslagen { display: none; }
    .themakaart .kaartkop { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
    .themakaart .kaartkop h3 { margin: 0; font-size: 18px; line-height: 1.3; }
    .themakaart .kaart-uitleg { margin: 4px 0 4px 38px; font-size: 14px; line-height: 1.5; color: var(--muted); }
    .vraagkaart.in-groep { margin: 0 !important; padding: 16px 0 6px !important; border-top: 1px solid var(--line); }
    .kaartkop + .vraagkaart.in-groep { border-top: 0; }
    .vraagkaart.in-groep ~ .vraagkaart.in-groep .score-hint { display: none; } /* uitleg 1-10 alleen bij de eerste regel */
    .vraagkaart.in-groep > p { margin-left: 0 !important; } /* geen inspringing: in een kaart staat er geen nummerbolletje voor */
    .vraagkaart.in-groep > div:first-child { margin-bottom: 10px !important; }
    .vraagkaart.in-groep h3 { font-size: 16px !important; font-weight: 600; }
    .vraagkaart.in-groep.mist { border: 2px solid var(--danger); border-radius: 10px; padding: 14px 12px 8px !important; margin: 6px 0 !important; }

    /* Vinkje in het nummerbolletje */
    .vraagnr.klaar { background: #2f8f3a !important; animation: omdraaien .4s ease-out; }
    @keyframes omdraaien { from { transform: rotateY(90deg); } to { transform: rotateY(0); } }

    /* Kloppend hart (logo in de kop) */
    .topnav-logo img.klop { animation: klop .9s ease-in-out; }
    @keyframes klop { 0%, 100% { transform: scale(1); } 15% { transform: scale(1.15); } 30% { transform: scale(1); } 45% { transform: scale(1.1); } 60% { transform: scale(1); } }

    /* Confetti bij het bedankje */
    .confetti-laag { position: fixed; inset: 0; pointer-events: none; overflow: hidden; z-index: 200; }
    .confetti-laag i { position: absolute; top: -14px; width: 8px; height: 12px; border-radius: 2px; animation-name: val; animation-timing-function: cubic-bezier(.25, .6, .4, 1); animation-fill-mode: forwards; }
    @keyframes val { to { transform: translate(var(--zij), 105vh) rotate(var(--draai)); } }

    @media (prefers-reduced-motion: reduce) {
        .slinger .lamp.aan, .vraagnr.klaar, .choice-grid--score .choice-inp:checked + .choice-lbl, .topnav-logo img.klop { animation: none; }
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

    <script nonce="{{ $cspNonce ?? '' }}">
    // Voortgang als lichtjesslinger, vinkje per beantwoorde vraag, slimme sprong en een kloppend hart.
    (function () {
        var form = document.querySelector('form[data-vragenlijst]');
        if (!form) return;
        var rustig = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var kaarten = Array.prototype.slice.call(form.querySelectorAll('.vraagkaart'));
        var tekst = document.getElementById('voortgang-tekst');
        var slinger = document.getElementById('slinger');
        var baan = document.getElementById('baan');
        var loper = document.getElementById('loper');
        var meldingen = Array.prototype.slice.call(form.querySelectorAll('.stop-melding'));
        var logo = document.querySelector('.topnav-logo img');
        // meer sprongvragen mogelijk (woonplaats-exit, "Nooit"); de eerste gekozen sprong telt
        var stopKaarten = Array.prototype.slice.call(form.querySelectorAll('.vraagkaart[data-stop-optie]'));
        // met themakaarten tellen we in kaarten, anders in vragen (IJme: "1 van 17 kaarten beantwoord")
        var metKaarten = !!form.querySelector('.themakaart');
        var enkel = metKaarten ? 'kaart' : 'vraag', meervoud = metKaarten ? 'kaarten' : 'vragen';
        var toch = false, half = false, heel = false, eerste = true;

        function beantwoord(k) {
            var ja = false;
            k.querySelectorAll('[name^="question_"]').forEach(function (el) {
                if (((el.type === 'radio' || el.type === 'checkbox') && el.checked) || (el.tagName === 'TEXTAREA' && el.value.trim() !== '')) ja = true;
            });
            return ja;
        }
        function vervolgVerborgen(k) {
            var v = kaarten[kaarten.indexOf(k) - 1];
            if (!v || !v.hasAttribute('data-vervolg-bij')) return false;
            var o = document.getElementById(v.getAttribute('data-vervolg-bij'));
            return !(o && o.checked);
        }
        function actieveStop() {
            if (toch) return null;
            var gekozen = null;
            stopKaarten.forEach(function (k) {
                if (vervolgVerborgen(k)) return; // verborgen vervolgvraag laat niet stoppen
                var o = document.getElementById(k.getAttribute('data-stop-optie'));
                if (o && o.checked && (!gekozen || parseInt(k.getAttribute('data-pos'), 10) < parseInt(gekozen.getAttribute('data-pos'), 10))) gekozen = k;
            });
            return gekozen;
        }
        function klop() {
            if (!logo || rustig) return;
            logo.classList.remove('klop'); void logo.offsetWidth; logo.classList.add('klop');
        }
        function bouwSlinger(n) {
            if (!slinger || slinger.children.length === n) return;
            slinger.innerHTML = '';
            // zo breed als de lampjes (max 16px per stuk), in het midden; op de telefoon de volle breedte
            slinger.style.maxWidth = (n * 16) + 'px';
            if (baan) baan.style.maxWidth = slinger.style.maxWidth;
            for (var i = 0; i < n; i++) { var s = document.createElement('span'); s.className = 'lamp'; slinger.appendChild(s); }
        }
        function werkBij() {
            var stopKaart = actieveStop();
            var stop = !!stopKaart;
            var stopPos = stop ? parseInt(stopKaart.getAttribute('data-pos'), 10) : -1;
            kaarten.forEach(function (k, i) {
                var weg = stop && parseInt(k.getAttribute('data-pos'), 10) > stopPos;
                // sprong over één vraag: de vorige vraag heeft een overslaan-antwoord dat gekozen is
                var vorige = kaarten[i - 1];
                if (!weg && vorige && vorige.hasAttribute('data-sla-over')) {
                    var o = document.getElementById(vorige.getAttribute('data-sla-over'));
                    weg = !!(o && o.checked);
                }
                // vervolgvraag: alleen tonen als bij de vorige vraag het juiste antwoord gekozen is
                if (!weg && vorige && vorige.hasAttribute('data-vervolg-bij')) {
                    var ov = document.getElementById(vorige.getAttribute('data-vervolg-bij'));
                    weg = !(ov && ov.checked);
                }
                k.classList.toggle('overgeslagen', weg);
            });
            meldingen.forEach(function (m) { m.hidden = !(stop && m.getAttribute('data-voor') === stopKaart.getAttribute('data-vraag')); });
            var zichtbaar = kaarten.filter(function (k) { return !k.classList.contains('overgeslagen'); });
            zichtbaar.forEach(function (k) { if (beantwoord(k)) k.classList.remove('mist'); });
            // themakaarten: vragen met dezelfde groep tellen als één kaart (nummer, lampje, "nog x")
            var eenheden = [], perGroep = {};
            zichtbaar.forEach(function (k) {
                var g = k.getAttribute('data-in-groep');
                if (!g) { eenheden.push({ el: k, leden: [k] }); return; }
                if (!perGroep[g]) { perGroep[g] = { el: form.querySelector('.themakaart[data-groep="' + g + '"]'), leden: [] }; eenheden.push(perGroep[g]); }
                perGroep[g].leden.push(k);
            });
            form.querySelectorAll('.themakaart').forEach(function (t) { t.classList.toggle('overgeslagen', !perGroep[t.getAttribute('data-groep')]); });
            var klaar = 0;
            eenheden.forEach(function (e, j) {
                var iets = e.leden.some(beantwoord);
                var ja = iets && e.leden.every(function (k) { return !k.hasAttribute('data-verplicht') || beantwoord(k); });
                if (ja) klaar++;
                // toelichting verschijnt zodra er een antwoord is (niet meer openklikken); bij een themakaart één per kaart
                var t = e.el.querySelector('.toelichting');
                if (t && t.hidden && iets) t.hidden = false;
                e.el.style.setProperty('--lamp', ['#f5c400', '#e5484d', '#2f9e44', '#3b82f6'][j % 4]);
                var nr = e.el.querySelector('.vraagnr');
                if (!nr) return;
                // doorlopend nummeren over de zichtbare kaarten (na een sprong geen gat in de nummers)
                nr.setAttribute('data-nr', String(j + 1));
                if (nr.classList.contains('klaar') !== ja) nr.classList.toggle('klaar', ja);
                nr.textContent = ja ? '\u2713' : String(j + 1);
                // vanaf de helft: hoeveel kaarten er nog komen
                var nog = e.el.querySelector('.nog-vragen');
                if (nog) {
                    var rest = eenheden.length - j;
                    nog.hidden = j < Math.floor(eenheden.length / 2);
                    nog.textContent = rest === 1 ? 'Laatste ' + enkel : 'Nog ' + rest + ' ' + meervoud;
                }
            });
            // hardloper: schuift mee, sprint op het laatste stuk, springt bij de finish
            // binnen = alle verplichte vragen beantwoord (open vragen mogen leeg blijven)
            var aantal = eenheden.length;
            var rest = aantal - klaar;
            var openVerplicht = zichtbaar.filter(function (k) { return k.hasAttribute('data-verplicht') && !beantwoord(k); }).length;
            var binnen = aantal > 0 && openVerplicht === 0 && klaar >= aantal / 2;
            var sprint = !binnen && aantal >= 12 && rest > 0 && rest <= Math.max(3, Math.round(aantal / 4));
            if (baan) baan.style.setProperty('--f', binnen ? '1' : aantal ? (klaar / aantal).toFixed(3) : '0');
            if (loper) { loper.classList.toggle('sprint', sprint); loper.classList.toggle('binnen', binnen); }
            if (tekst) tekst.textContent = binnen ? 'Binnen! Nu nog op versturen drukken'
                : sprint ? 'Laatste rechte stuk, nog ' + rest + ' ' + (rest === 1 ? enkel : meervoud)
                : klaar + ' van ' + aantal + ' ' + meervoud + ' beantwoord';
            bouwSlinger(aantal);
            if (slinger) Array.prototype.forEach.call(slinger.children, function (l, i) { l.classList.toggle('aan', i < klaar); });
            var deel = aantal ? klaar / aantal : 0;
            if (!eerste) {
                if (deel >= 0.5 && !half) { half = true; klop(); }
                if (deel >= 1 && !heel) { heel = true; klop(); }
            } else { half = deel >= 0.5; heel = deel >= 1; eerste = false; }
        }
        form.querySelectorAll('.toch-verder').forEach(function (b) {
            b.addEventListener('click', function () { toch = true; werkBij(); });
        });
        form.addEventListener('change', function (e) {
            // meerkeuze: "Geen van deze" haalt de andere vinkjes weg, en een ander vinkje haalt "Geen van deze" weg
            var el = e.target;
            if (el.type === 'checkbox' && el.checked) {
                var kaart = el.closest('.vraagkaart');
                kaart.querySelectorAll('input[type=checkbox]').forEach(function (c) {
                    if (c !== el && (el.hasAttribute('data-alleen') || c.hasAttribute('data-alleen'))) c.checked = false;
                });
            }
            if (stopKaarten.some(function (k) { return k.contains(e.target); })) toch = false;
            werkBij();
        });
        form.addEventListener('input', werkBij);
        form.addEventListener('submit', function (e) {
            werkBij();
            var open = kaarten.filter(function (k) {
                return !k.classList.contains('overgeslagen') && k.hasAttribute('data-verplicht') && !beantwoord(k);
            });
            kaarten.forEach(function (k) { k.classList.toggle('mist', open.indexOf(k) >= 0); });
            if (!open.length) return;
            e.preventDefault();
            // onder de vaste balk (logo 60px + voortgang met slinger en loper) uitkomen
            var kop = document.getElementById('voortgang');
            window.scrollTo(0, open[0].getBoundingClientRect().top + window.scrollY - (kop ? 60 + kop.offsetHeight + 20 : 140));
        });
        werkBij();
    })();
    </script>


    <script nonce="{{ $cspNonce ?? '' }}">
    // Scroll to top when page loads if there's a success or error message
    document.addEventListener('DOMContentLoaded', () => {
        const flashElement = document.querySelector('.flash');
        const mistKaart = document.querySelector('.vraagkaart.mist');
        if (mistKaart) {
            const kop = document.getElementById('voortgang');
            window.scrollTo(0, mistKaart.getBoundingClientRect().top + window.scrollY - (kop ? 60 + kop.offsetHeight + 20 : 140));
        } else if (flashElement) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
    </script>

    @endsection
