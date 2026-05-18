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
        <div style="display: flex; gap: 8px; align-items: flex-start;">
            <textarea id="question" name="question" rows="2" required style="flex: 1;">{{ old('question', $question->question) }}</textarea>
            <button type="button" class="btn" id="add-help-btn" style="padding: 8px 12px; margin-top: 4px; white-space: nowrap; font-size: 20px;" title="Voeg extra informatie toe">
                ℹ️
            </button>
        </div>
    </div>

    <input type="hidden" id="help_text" name="help_text" value="{{ old('help_text', $question->help_text ?? '') }}">

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
