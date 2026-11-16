@extends('layouts.app')

@section('content')
<div style="margin-bottom: 12px;">
    <a class="btn" href="{{ route('admin.polls.show', $poll) }}">← Terug naar poll</a>
    <h1>{{ $question->exists ? 'Vraag bewerken' : 'Nieuwe vraag' }}</h1>
</div>

<form class="card" method="post" action="{{ $action }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="field">
        <label for="question">Vraag</label>
        <textarea id="question" name="question" rows="2" required>{{ old('question', $question->question) }}</textarea>
        @error('question')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label for="help_text">Toelichting <span style="font-weight:400; color:var(--muted);">(optioneel, staat klein onder de vraag)</span></label>
        <textarea id="help_text" name="help_text" rows="2" placeholder="Bijvoorbeeld: denk hierbij aan de afgelopen 12 maanden">{{ old('help_text', $question->help_text ?? '') }}</textarea>
        @error('help_text')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <input type="hidden" name="met_toelichting" value="0">
        <label class="switch-row" for="met_toelichting">
            <input type="checkbox" id="met_toelichting" name="met_toelichting" value="1" class="switch-input" @checked((bool) old('met_toelichting', $question->met_toelichting ?? false))>
            <span class="switch-track" aria-hidden="true"><span class="switch-knob"></span></span>
            <span>
                <strong>Toelichtingsveld tonen</strong><br>
                <span style="font-size:12px; color:var(--muted);">Aan: onder de antwoorden staat een ingeklapt, niet verplicht veld "Wilt u dit toelichten?". De toelichtingen staan in de uitslag bij de vraag.</span>
            </span>
        </label>
    </div>
    @if($question->exists && $question->options->isNotEmpty() && ! \App\Support\PollType::isOpenTextType($question->type))
    <div class="field">
        <label for="standaard_antwoord">Bij geen antwoord <span style="font-weight:400; color:var(--muted);">(optioneel)</span></label>
        <select id="standaard_antwoord" name="standaard_antwoord">
            <option value="">Antwoord is verplicht</option>
            @foreach($question->options as $o)
                <option value="{{ $o->label }}" @selected(old('standaard_antwoord', $question->standaard_antwoord) === $o->label)>Telt als: {{ $o->label }}</option>
            @endforeach
        </select>
        <p style="font-size:12px; color:var(--muted); margin:6px 0 0;">Kies je een optie, dan mag de vraag leeg blijven en telt een leeg antwoord als die optie. Zet er in de toelichting bij wat er gebeurt.</p>
    </div>
    <div class="field">
        <label for="stop_bij">Slimme sprong <span style="font-weight:400; color:var(--muted);">(optioneel)</span></label>
        <select id="stop_bij" name="stop_bij">
            <option value="">Geen sprong</option>
            @foreach($question->options as $o)
                <option value="{{ $o->label }}" @selected(old('stop_bij', $question->stop_bij) === $o->label)>Bij "{{ $o->label }}" zijn de vragen hierna niet nodig</option>
            @endforeach
        </select>
        <p style="font-size:12px; color:var(--muted); margin:6px 0 0;">De invuller ziet de vragen hierna dichtklappen en kan meteen versturen, met een knop om toch alles in te vullen.</p>
    </div>
    @endif
    <style>
        .switch-row { display:flex; align-items:flex-start; gap:12px; cursor:pointer; font-weight:400; }
        .switch-input { position:absolute; opacity:0; width:1px; height:1px; }
        .switch-track { flex-shrink:0; width:44px; height:24px; border-radius:12px; background:#cbd5e1; position:relative; transition:background .2s; margin-top:2px; }
        .switch-knob { position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; transition:left .2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
        .switch-input:checked + .switch-track { background:var(--primary); }
        .switch-input:checked + .switch-track .switch-knob { left:23px; }
        .switch-input:focus-visible + .switch-track { outline:2px solid var(--primary); outline-offset:2px; }
    </style>

    @if($question->exists && ($answerCount = $question->votes()->count()) > 0)
        <div style="padding:12px 14px; background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; margin-bottom:16px; font-size:14px; color:#9a3412;">
            Op deze vraag zijn al {{ $answerCount }} antwoorden gegeven. Tekst aanpassen en opties hernoemen kan zonder dat er iets verloren gaat;
            het type ligt vast. Haal je een optie weg, dan verliezen de antwoorden op díé optie hun keuze.
        </div>
    @endif

    <div class="field">
        <label for="type">Type poll</label>
        <select id="type" name="type" required>
            @foreach($typeLabels as $key => $label)
                <option value="{{ $key }}" @selected(old('type', $question->type ?? $poll->type) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <p class="meta" id="options-help" style="margin-top: 0;">Standaardopties worden geladen op basis van het gekozen type. Je kunt ze daarna aanpassen.</p>
        <div id="option-fields" class="grid" style="gap: 8px;"></div>
        <div class="inline" style="margin-top: 10px;">
            <button type="button" class="btn" id="add-option">+ Optie toevoegen</button>
            <button type="button" class="btn" id="reset-options">Standaardopties opnieuw laden</button>
        </div>
        <textarea id="options_text" name="options_text" rows="8" style="display: none;">{{ old('options_text', $optionsText) }}</textarea>
    </div>

    <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--line);">

    <button class="btn btn-primary" type="submit">Opslaan</button>
</form>

<script>
    const ratingType = '{{ \App\Support\PollType::RATING_STARS }}';
    const defaultsByType = @json($defaultsByType);
    const typeSelect = document.getElementById('type');
    const optionsText = document.getElementById('options_text');
    const optionFields = document.getElementById('option-fields');
    const optionsHelp = document.getElementById('options-help');
    const addOptionButton = document.getElementById('add-option');
    const resetOptionsButton = document.getElementById('reset-options');

    function splitLines(text) {
        return (text || '')
            .split(/\r\n|\r|\n/)
            .map(line => line.trim())
            .filter(Boolean);
    }

    function updateHiddenTextarea() {
        const values = Array.from(optionFields.querySelectorAll('input[data-option-input]'))
            .map(input => input.value.trim())
            .filter(Boolean);

        optionsText.value = values.join("\n");
    }

    function isRatingType() {
        return typeSelect.value === ratingType;
    }

    function optionRow(value = '') {
        const row = document.createElement('div');
        row.className = 'inline';
        row.style.alignItems = 'center';

        const input = document.createElement('input');
        input.type = 'text';
        input.value = value;
        input.setAttribute('data-option-input', '1');
        input.placeholder = 'Optie';
        input.style.flex = '1';
        input.readOnly = isRatingType();

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-danger';
        removeButton.textContent = 'Verwijder';
        removeButton.style.display = isRatingType() ? 'none' : 'inline-block';

        removeButton.addEventListener('click', () => {
            row.remove();

            if (optionFields.querySelectorAll('input[data-option-input]').length === 0) {
                optionFields.appendChild(optionRow(''));
            }

            updateHiddenTextarea();
        });

        input.addEventListener('input', updateHiddenTextarea);

        row.appendChild(input);
        row.appendChild(removeButton);

        return row;
    }

    function renderOptions(values) {
        optionFields.innerHTML = '';

        values.forEach(value => {
            optionFields.appendChild(optionRow(value));
        });

        if (values.length === 0) {
            optionFields.appendChild(optionRow(''));
        }

        updateHiddenTextarea();
    }

    function applyDefaultsForType(showResetText = false) {
        const type = typeSelect.value;
        const defaults = defaultsByType[type] || [];
        renderOptions(defaults);

        if (isRatingType()) {
            addOptionButton.style.display = 'none';
            resetOptionsButton.style.display = 'none';
            optionsHelp.textContent = 'Bij sterren staat dit vast op 1 t/m 5 sterren en is niet aanpasbaar.';
            return;
        }

        addOptionButton.style.display = 'inline-block';
        resetOptionsButton.style.display = 'inline-block';

        if (type === 'event_dates') {
            optionsHelp.textContent = showResetText
                ? 'Standaard event-datums geladen. Je kunt ze aanpassen (formaat dd-mm-jjjj).'
                : 'Gebruik datums als dd-mm-jjjj. Deze worden als eventdata opgeslagen.';
        } else {
            optionsHelp.textContent = showResetText
                ? 'Standaardopties opnieuw geladen. Je kunt ze aanpassen.'
                : 'Standaardopties worden geladen op basis van het gekozen type. Je kunt ze daarna aanpassen.';
        }
    }

    typeSelect.addEventListener('change', applyDefaultsForType);

    addOptionButton.addEventListener('click', () => {
        optionFields.appendChild(optionRow(''));
        updateHiddenTextarea();
    });

    resetOptionsButton.addEventListener('click', () => applyDefaultsForType(true));

    const initialValues = splitLines(optionsText.value);

    if (isRatingType()) {
        applyDefaultsForType();
    } else if (initialValues.length > 0) {
        renderOptions(initialValues);
    } else {
        applyDefaultsForType();
    }
</script>
@endsection
