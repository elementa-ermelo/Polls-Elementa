@extends('layouts.app')

@section('content')

<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 28px;">
    <div>
        <h1 style="margin: 0 0 6px; color: var(--text);">Email verzenden</h1>
        <p class="meta" style="margin: 0;">Stuur een aangepaste email naar respondenten van: <strong>{{ $poll->title }}</strong></p>
    </div>
    <a href="{{ route('admin.polls.show', $poll) }}" class="btn" style="font-size: 13px;">← Terug</a>
</div>

<div class="card" style="max-width: 800px;">
    <form method="POST" action="{{ route('admin.polls.send-email', $poll) }}">
        @csrf

        {{-- Onderwerp --}}
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Onderwerp <span style="color: #dc2626;">*</span></label>
            <input type="text" name="subject" value="{{ old('subject') }}" required
                   style="width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px; font-family: inherit; font-size: 14px;"
                   placeholder="bv. Reminder: Poll uitslag">
            @error('subject')
                <p style="color: #dc2626; font-size: 13px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Bericht --}}
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Bericht <span style="color: #dc2626;">*</span></label>
            <textarea name="message" required rows="10"
                      style="width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px; font-family: inherit; font-size: 14px; resize: vertical;"
                      placeholder="Schrijf hier je email bericht...">{{ old('message') }}</textarea>
            <p class="meta" style="margin-top: 6px; font-size: 12px;">Je kunt de volgende placeholders gebruiken:
                <br><code>{name}</code> - naam van de respondent
                <br><code>{email}</code> - email van de respondent
            </p>
            @error('message')
                <p style="color: #dc2626; font-size: 13px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Ontvangers selectie --}}
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 12px; font-weight: 600; font-size: 14px;">Verzenden naar <span style="color: #dc2626;">*</span></label>

            <div style="display: flex; gap: 20px; margin-bottom: 16px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="recipient_type" value="all" {{ old('recipient_type', 'all') === 'all' ? 'checked' : '' }}>
                    <span style="font-size: 14px;">Iedereen ({{ $respondents->count() }} respondenten)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="recipient_type" value="unconfirmed" {{ old('recipient_type') === 'unconfirmed' ? 'checked' : '' }}>
                    <span style="font-size: 14px;">Alleen onbevestigden</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="recipient_type" value="specific" {{ old('recipient_type') === 'specific' ? 'checked' : '' }}>
                    <span style="font-size: 14px;">Specifieke respondenten</span>
                </label>
            </div>

            {{-- Specifieke respondenten selectie --}}
            <div id="specific-recipients" style="display: {{ old('recipient_type') === 'specific' ? 'block' : 'none' }}; margin-top: 12px;">
                <div style="border: 1px solid var(--line); border-radius: 6px; max-height: 300px; overflow-y: auto;">
                    @foreach($respondents as $respondent)
                        <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-bottom: 1px solid var(--line); cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" name="specific_emails[]" value="{{ $respondent['email'] }}" {{ in_array($respondent['email'], old('specific_emails', [])) ? 'checked' : '' }}>
                            <div>
                                <p style="margin: 0; font-weight: 500; font-size: 13px;">{{ $respondent['name'] }}</p>
                                <p style="margin: 0; color: var(--muted); font-size: 12px;">{{ $respondent['email'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Preview --}}
        <div style="background: var(--bg); border: 1px solid var(--line); border-radius: 6px; padding: 16px; margin-bottom: 20px;">
            <p style="margin: 0 0 8px; font-weight: 600; font-size: 13px; text-transform: uppercase; color: var(--muted);">📧 Preview</p>
            <div style="background: #fff; border: 1px solid var(--line); border-radius: 4px; padding: 12px;">
                <p style="margin: 0 0 4px; font-size: 12px; color: var(--muted);"><strong>Van:</strong> polls@elementa.com</p>
                <p style="margin: 0 0 12px; font-size: 12px; color: var(--muted);"><strong>Onderwerp:</strong> <span id="preview-subject">{{ old('subject', '(nog leeg)') }}</span></p>
                <div style="border-top: 1px solid var(--line); padding-top: 12px; font-size: 13px; white-space: pre-wrap; word-break: break-word; line-height: 1.6;">
                    <span id="preview-message">{{ old('message', '(nog leeg)') }}</span>
                </div>
            </div>
        </div>

        {{-- Buttons --}}
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="font-size: 14px; padding: 10px 20px;">📧 Verzenden</button>
            <a href="{{ route('admin.polls.show', $poll) }}" class="btn" style="font-size: 14px; padding: 10px 20px;">Annuleren</a>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('specific-recipients').style.display =
            this.value === 'specific' ? 'block' : 'none';
    });
});

document.querySelector('input[name="subject"]').addEventListener('input', function() {
    document.getElementById('preview-subject').textContent = this.value || '(nog leeg)';
});

document.querySelector('textarea[name="message"]').addEventListener('input', function() {
    document.getElementById('preview-message').textContent = this.value || '(nog leeg)';
});
</script>

@endsection
