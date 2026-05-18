@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h1 style="margin:0 0 4px;">{{ $poll->exists ? 'Poll bewerken' : 'Nieuwe poll' }}</h1>
        <p class="meta" style="margin:0;">{{ $poll->exists ? 'Wijzig de gegevens van deze poll.' : 'Vul de gegevens in en voeg vragen toe.' }}</p>
    </div>
    <a class="btn" href="{{ route('admin.polls.index') }}">← Terug</a>
</div>

<form method="post" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    {{-- Basisgegevens --}}
    <div class="card" style="margin-bottom:16px;">
        <h2 style="margin:0 0 16px; font-size:16px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Basisgegevens</h2>

        <div class="field">
            <label for="title">Titel *</label>
            <input id="title" name="title" placeholder="Geef de poll een duidelijke naam" value="{{ old('title', $poll->title) }}" required>
            @error('title')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="question">Beschrijving <span style="font-weight:400; color:var(--muted);">(optioneel)</span></label>
            <textarea id="question" name="question" rows="3" placeholder="Voeg hier een beschrijving of toelichting in...">{{ old('question', $poll->question ?? '') }}</textarea>
            @error('question')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="field" style="max-width:280px;">
            <label for="status">Zichtbaarheid *</label>
            <select id="status" name="status" required>
                <option value="active"   @selected(old('status', $poll->exists ? $poll->status : 'active') === 'active')>Publiek</option>
                <option value="archived" @selected(old('status', $poll->status) === 'archived')>Archief</option>
            </select>
        </div>
        <input type="hidden" name="is_public" value="1">

        <div class="grid grid-2">
            <div class="field">
                <label for="opens_at">Open vanaf <span style="font-weight:400; color:var(--muted);">(optioneel)</span></label>
                <input id="opens_at" type="datetime-local" name="opens_at" value="{{ old('opens_at', optional($poll->opens_at)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="field">
                <label for="closes_at">Sluit op <span style="font-weight:400; color:var(--muted);">(optioneel)</span></label>
                <input id="closes_at" type="datetime-local" name="closes_at" value="{{ old('closes_at', optional($poll->closes_at)->format('Y-m-d\TH:i')) }}">
            </div>
        </div>
        @error('closes_at')<p class="field-error">{{ $message }}</p>@enderror

        <div class="field" style="max-width:280px;">
            <label for="access_code">Toegangscode <span style="font-weight:400; color:var(--muted);">(optioneel)</span></label>
            <input id="access_code" type="text" name="access_code" placeholder="Laat leeg voor openbare poll" value="{{ old('access_code', $poll->access_code ?? '') }}" style="text-transform:uppercase;">
            <p style="font-size:12px; color:var(--muted); margin:6px 0 0;">Als je een code instelt, moeten deelnemers deze invoeren om de poll in te vullen.</p>
        </div>
    </div>

    {{-- Vragen --}}
    @if(!$poll->exists)
    <div class="card" style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="margin:0; font-size:16px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Vragen</h2>
            <button type="button" id="add-question-btn" class="btn btn-primary" style="font-size:14px;">+ Vraag toevoegen</button>
        </div>

        <div id="questions-list"></div>

        <div id="no-questions-hint" style="padding:32px; text-align:center; border:2px dashed var(--line); border-radius:10px; color:var(--muted);">
            <p style="margin:0; font-size:15px;">Klik op <strong>+ Vraag toevoegen</strong> om te beginnen.</p>
        </div>

        <textarea id="questions_json" name="questions_json" style="display:none;"></textarea>
    </div>
    @else
    <div class="card" style="margin-bottom:16px; background:#fffbeb; border-color:#fcd34d;">
        <p style="margin:0;">Vragen beheer je op de <a href="{{ route('admin.polls.show', $poll) }}" style="color:var(--accent); font-weight:600;">detailpagina van de poll</a>.</p>
    </div>
    @endif

    {{-- Acties --}}
    <div style="display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit" style="padding:10px 24px;">
            {{ $poll->exists ? 'Wijzigingen opslaan' : 'Poll aanmaken' }}
        </button>
        <a class="btn" href="{{ route('admin.polls.index') }}" style="padding:10px 24px;">Annuleren</a>
    </div>
</form>

<style>
.field-error { color: var(--danger); font-size: 13px; margin: 6px 0 0; font-weight: 600; }

/* Question builder */
.q-block {
    background: var(--surface);
    border: 1.5px solid var(--line);
    border-radius: 12px;
    margin-bottom: 16px;
    overflow: hidden;
    transition: all .2s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.q-block:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}
.q-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid var(--line);
}
.q-num {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: #fff;
    font-size: 14px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}
.q-body { padding: 16px; }
.q-body .field { margin-bottom: 14px; }
.q-body .field:last-child { margin-bottom: 0; }
.opt-row {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
}
.opt-row input { flex: 1; }
.opt-row button {
    flex-shrink: 0;
    width: 36px; height: 36px;
    padding: 0;
    border-radius: 8px;
    border: 1.5px solid var(--line);
    background: var(--surface);
    color: #94a3b8;
    font-size: 18px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}
.opt-row button:hover {
    border-color: var(--danger);
    color: var(--danger);
    background: #fef2f2;
}
.add-opt-btn {
    margin-top: 8px;
    font-size: 13px;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1.5px dashed var(--primary);
    background: rgba(59, 130, 246, 0.05);
    color: var(--primary);
    cursor: pointer;
    font-weight: 600;
    transition: all .2s;
}
.add-opt-btn:hover {
    background: rgba(59, 130, 246, 0.15);
    border-color: var(--primary-dark);
}
.remove-q-btn {
    margin-left: auto;
    padding: 6px 12px;
    border-radius: 8px;
    border: 1.5px solid #fca5a5;
    background: var(--surface);
    color: var(--danger);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.remove-q-btn:hover {
    background: #fef2f2;
    border-color: var(--danger);
}
</style>

@if(!$poll->exists)
<script>
const typeLabels    = @json($typeLabels ?? []);
const defaultsByType = @json($defaultsByType ?? []);
const ratingType    = '{{ \App\Support\PollType::RATING_STARS }}';
const openTextType  = '{{ \App\Support\PollType::OPEN_TEXT }}';

const list   = document.getElementById('questions-list');
const hint   = document.getElementById('no-questions-hint');
const jsonEl = document.getElementById('questions_json');
const form   = document.querySelector('form');

function serialize() {
    const q = [];
    list.querySelectorAll('.q-block').forEach((block, i) => {
        const text = block.querySelector('[data-q-text]').value.trim();
        const type = block.querySelector('[data-q-type]').value;
        const opts = [...block.querySelectorAll('[data-q-opt]')]
            .map(el => el.value.trim()).filter(Boolean);
        if (text) q.push({ position: i, question: text, type, options: opts });
    });
    jsonEl.value = JSON.stringify(q);
    return q;
}

function renumber() {
    list.querySelectorAll('.q-num').forEach((el, i) => el.textContent = i + 1);
    hint.style.display = list.children.length ? 'none' : 'block';
}

function buildOptRow(container, val = '') {
    const row = document.createElement('div');
    row.className = 'opt-row';

    const inp = document.createElement('input');
    inp.type = 'text';
    inp.setAttribute('data-q-opt', '1');
    inp.value = val;
    inp.placeholder = 'Optie…';
    inp.addEventListener('input', serialize);

    const del = document.createElement('button');
    del.type = 'button';
    del.innerHTML = '×';
    del.title = 'Verwijder optie';
    del.addEventListener('click', () => { row.remove(); serialize(); });

    row.append(inp, del);
    container.appendChild(row);
}

function renderOptions(block, type) {
    const wrap = block.querySelector('[data-opts-wrap]');
    wrap.innerHTML = '';

    if (type === openTextType) {
        wrap.innerHTML = '<p style="font-size:13px;color:var(--muted);margin:0;">Open tekstveld — geen opties nodig.</p>';
        serialize();
        return;
    }

    const defaults = defaultsByType[type] || [];

    if (type === ratingType) {
        wrap.innerHTML = '<p style="font-size:13px;color:var(--muted);margin:0;">Sterren 1–5 worden automatisch aangemaakt.</p>';
        serialize();
        return;
    }

    defaults.forEach(v => buildOptRow(wrap, v));

    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.className = 'add-opt-btn';
    addBtn.textContent = '+ Optie toevoegen';
    addBtn.addEventListener('click', () => { buildOptRow(wrap, ''); serialize(); });
    wrap.appendChild(addBtn);

    serialize();
}

function addQuestion() {
    const block = document.createElement('div');
    block.className = 'q-block';

    const typeOptions = Object.entries(typeLabels)
        .map(([k, v]) => `<option value="${k}">${v}</option>`).join('');

    block.innerHTML = `
        <div class="q-header">
            <div class="q-num">1</div>
            <span style="font-weight:600; font-size:15px;">Vraag</span>
            <button type="button" class="remove-q-btn">Verwijder</button>
        </div>
        <div class="q-body">
            <div class="field">
                <label style="font-size:13px;">Vraagstelling *</label>
                <input type="text" data-q-text placeholder="Typ hier je vraag…">
            </div>
            <div class="field">
                <label style="font-size:13px;">Type antwoord *</label>
                <select data-q-type>${typeOptions}</select>
            </div>
            <div class="field">
                <label style="font-size:13px;">Antwoordopties</label>
                <div data-opts-wrap></div>
            </div>
        </div>
    `;

    list.appendChild(block);
    renumber();

    const typeSelect = block.querySelector('[data-q-type]');
    const textInput  = block.querySelector('[data-q-text]');

    typeSelect.addEventListener('change', () => renderOptions(block, typeSelect.value));
    textInput.addEventListener('input', serialize);

    block.querySelector('.remove-q-btn').addEventListener('click', () => {
        block.remove(); renumber(); serialize();
    });

    renderOptions(block, typeSelect.value);
}

document.getElementById('add-question-btn').addEventListener('click', e => {
    e.preventDefault(); addQuestion();
});

form.addEventListener('submit', e => {
    const q = serialize();
    if (!q.length) {
        e.preventDefault();
        alert('Voeg minstens 1 vraag toe.');
    }
});

document.addEventListener('DOMContentLoaded', addQuestion);
</script>
@endif

@endsection
