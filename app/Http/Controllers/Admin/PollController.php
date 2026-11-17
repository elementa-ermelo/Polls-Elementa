<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollRequest;
use App\Models\Poll;
use App\Models\PollQuestion;
use App\Models\Vote;
use App\Support\PollType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PollController extends Controller
{
    public function index(): View
    {
        Poll::archiveExpiredPolls();

        $query = Poll::query()
            ->with('user')
            ->withCount(['votes', 'confirmedVotes']);

        // Eigen polls, polls waar je mede-beheerder bent, of alles voor admins
        $query->visibleTo(auth()->user());

        $polls = $query->latest()->paginate(12);

        return view('admin.polls.index', [
            'polls' => $polls,
            'typeLabels' => PollType::labels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.polls.form', [
            'poll' => new Poll([
                'status' => 'inactive',
                'is_public' => false,
            ]),
            'typeLabels' => PollType::labels(),
            'defaultsByType' => PollType::defaultsByType(),
            'action' => route('admin.polls.store'),
            'method' => 'POST',
            'optionsText' => '',
        ]);
    }

    public function quickCreate(): JsonResponse
    {
        $poll = Poll::create([
            'title' => 'Nieuwe poll',
            'question' => '',
            'type' => 'closed',
            'status' => 'inactive',
            'is_public' => false,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('admin.polls.show', $poll),
        ]);
    }

    public function store(StorePollRequest $request): RedirectResponse
    {
        Log::info('Nieuwe poll aanmaakverzoek ontvangen', [
            'user_id' => auth()->id(),
            'title' => $request->validated('title'),
        ]);

        try {
            $poll = DB::transaction(function () use ($request) {
                $status = $request->validated('status');
                // Zichtbaar voor deelnemers = status Live. Vroeger kwam dit uit een
                // verborgen veld dat altijd 1 was, waardoor elke nieuwe poll meteen
                // openbaar stond en daarna niet meer te bewerken was.
                $isPublic = $status === 'active';
                $accessCode = $request->input('access_code');
                
                // Generate unique access code if provided
                if ($accessCode && strlen($accessCode) > 0) {
                    $accessCode = strtoupper(str_replace(' ', '', $accessCode));
                } else {
                    $accessCode = null;
                }

                $poll = Poll::query()->create([
                    'title' => $request->validated('title'),
                    'question' => (string) ($request->validated('question') ?? ''),
                    'type' => $request->validated('type') ?? 'closed',
                    'status' => $status,
                    'opens_at' => $request->validated('opens_at'),
                    'closes_at' => $request->validated('closes_at'),
                    'is_public' => $isPublic,
                    'access_code' => $accessCode,
                    'requires_confirmation' => $request->boolean('requires_confirmation'),
                    'anonymous' => $request->boolean('anonymous'),
                    'organisatie_naam' => $request->filled('organisatie_naam') ? trim($request->input('organisatie_naam')) : null,
                    'user_id' => auth()->id(),
                ]);

                Log::info('Poll aangemaakt in database', [
                    'poll_id' => $poll->id,
                    'title' => $poll->title,
                    'user_id' => auth()->id(),
                ]);

                $this->bewaarOrganisatieLogo($request, $poll);

                // Process questions from JSON
                $questionsJson = $request->input('questions_json', '{}');
                try {
                    $questions = json_decode($questionsJson, true) ?? [];
                    
                    foreach (array_values($questions) as $index => $qData) {
                        $type = $qData['type'] ?? $poll->type;
                        if (! array_key_exists($type, PollType::labels())) {
                            $type = 'closed';
                        }

                        $question = $poll->questions()->create([
                            'question' => mb_substr((string) ($qData['question'] ?? ''), 0, 1000),
                            'help_text' => isset($qData['help_text']) ? mb_substr((string) $qData['help_text'], 0, 2000) : null,
                            'type' => $type,
                            'position' => $index,
                        ]);
                        
                        $this->syncQuestionOptions($question, implode("\n", $qData['options'] ?? []));
                    }

                    Log::info('Vragen en opties verwerkt', [
                        'poll_id' => $poll->id,
                        'question_count' => count($questions),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Fout bij verwerken vragen JSON', [
                        'poll_id' => $poll->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                return $poll;
            });

            Log::info('Poll succesvol aangemaakt', [
                'poll_id' => $poll->id,
                'title' => $poll->title,
            ]);

            return redirect()->route('admin.polls.show', $poll)->with('success', 'Poll aangemaakt! 🎉');
        } catch (\Throwable $e) {
            Log::error('Fout bij aanmaken poll', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->withErrors(['error' => 'De poll kon niet worden aangemaakt. Probeer het opnieuw; blijft het misgaan, meld het dan.']);
        }
    }

    public function show(Poll $poll): View
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        // Filters in de uitslag: alleen inzenders met een bepaald antwoord en/of een bepaalde herkomst.
        $filterAntwoord = request()->integer('antwoord') ?: null;
        $filterHerkomst = request()->filled('herkomst') ? request()->string('herkomst')->toString() : null;
        $emails = null;
        if ($filterAntwoord) {
            $emails = Vote::query()->where('poll_id', $poll->id)->where('poll_option_id', $filterAntwoord)
                ->distinct()->pluck('email');
        }
        if ($filterHerkomst !== null) {
            $metHerkomst = Vote::query()->where('poll_id', $poll->id)
                ->when($filterHerkomst === 'onbekend', fn ($q) => $q->whereNull('herkomst'), fn ($q) => $q->where('herkomst', $filterHerkomst))
                ->distinct()->pluck('email');
            $emails = $emails === null ? $metHerkomst : $emails->intersect($metHerkomst)->values();
        }
        $beperk = fn ($q) => $emails === null ? $q : $q->whereIn('email', $emails);

        $poll->load([
            'questions.options.votes' => $beperk,
            'questions.votes' => $beperk,
            'options.votes' => $beperk,
            'votes' => fn ($query) => $beperk($query)->latest()->with(['option', 'question']),
        ]);

        $totalRespondents = $poll->votes()->distinct('email')->count('email');
        $confirmedRespondents = $poll->confirmedVotes()->distinct('email')->count('email');
        $gefilterdeRespondenten = $emails === null ? null : $emails->count();

        $herkomstOverzicht = Vote::query()->where('poll_id', $poll->id)
            ->selectRaw("COALESCE(herkomst, 'onbekend') AS bron, COUNT(DISTINCT email) AS n")
            ->groupBy('bron')->orderByDesc('n')->get();
        $druksteUren = Vote::query()->where('poll_id', $poll->id)
            ->selectRaw("DATE_FORMAT(created_at, '%d-%m %H:00') AS uur, COUNT(DISTINCT email) AS n")
            ->groupBy('uur')->orderByDesc('n')->limit(5)->get();
        // Antwoorden om op te filteren: keuzevragen met hooguit 8 opties (dus niet de cijferrijen).
        $filterVragen = $poll->questions->filter(fn ($q) => ! \App\Support\PollType::isOpenTextType($q->type)
            && $q->options->count() > 0 && $q->options->count() <= 8);

        return view('admin.polls.show', [
            'poll' => $poll,
            'totalRespondents' => $totalRespondents,
            'confirmedRespondents' => $confirmedRespondents,
            'canManage' => $poll->isManagedBy(auth()->user()),
            'filterAntwoord' => $filterAntwoord,
            'filterHerkomst' => $filterHerkomst,
            'gefilterdeRespondenten' => $gefilterdeRespondenten,
            'herkomstOverzicht' => $herkomstOverzicht,
            'druksteUren' => $druksteUren,
            'filterVragen' => $filterVragen,
        ]);
    }

    public function downloadPdf(Poll $poll): Response
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        try {
            $poll->load([
                'questions.options',
                'questions.votes',
                'options.votes',
                'votes' => fn ($query) => $query->latest()->with(['option', 'question']),
            ]);

            $totalRespondents = $poll->votes()->distinct('email')->count('email');
            $confirmedRespondents = $poll->confirmedVotes()->distinct('email')->count('email');

            // Group votes by respondent
            $respondents = Vote::where('poll_id', $poll->id)
                ->get()
                ->groupBy('email')
                ->map(function ($group) {
                    return [
                        'respondent_name' => $group->first()->respondent_name,
                        'email' => $group->first()->email,
                        'age' => $group->first()->age,
                        'created_at' => $group->first()->created_at,
                        'confirmed_at' => $group->first()->confirmed_at,
                        'votes' => $group,
                    ];
                });

            $filename = 'antwoorden_' . \Illuminate\Support\Str::slug($poll->title, '_') . '_' . now()->format('Y-m-d') . '.pdf';

            // Generate simple HTML directly
            $html = $this->generatePdfHtml($poll, $respondents, $totalRespondents, $confirmedRespondents);

            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4')
                ->setOption('defaultFont', 'Arial')
                ->setOption('isHtml5ParserEnabled', true);

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('Fout bij PDF downloaden', [
                'poll_id' => $poll->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    private function generatePdfHtml(Poll $poll, $respondents, $totalRespondents, $confirmedRespondents): string
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{POLL_TITLE}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .stats { margin: 20px 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .stat-box { background: #f5f5f5; padding: 15px; border-left: 4px solid #0066cc; }
        .stat-value { font-size: 24px; font-weight: bold; color: #0066cc; }
        .stat-label { font-size: 11px; color: #666; text-transform: uppercase; }
        .respondent { margin: 30px 0; border: 1px solid #ddd; page-break-inside: avoid; }
        .respondent-header { background: #0066cc; color: white; padding: 12px 15px; }
        .respondent-info { background: #f9f9f9; padding: 12px 15px; font-size: 11px; }
        .answers { padding: 15px; }
        .answer { margin: 15px 0; padding: 10px; border-left: 3px solid #e0e0e0; background: #fafafa; }
        .answer-q { font-weight: bold; color: #0066cc; margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>{POLL_TITLE}</h1>
    <p><em>{POLL_QUESTION}</em></p>
    
    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{TOTAL_RESPONDENTS}</div>
            <div class="stat-label">Totaal Respondenten</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{CONFIRMED_RESPONDENTS}</div>
            <div class="stat-label">Bevestigd</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{QUESTION_COUNT}</div>
            <div class="stat-label">Vragen</div>
        </div>
    </div>

    {RESPONDENTS_HTML}

    <p style="margin-top: 40px; font-size: 10px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 20px;">
        Gegenereerd op {GENERATED_AT}
    </p>
</body>
</html>
HTML;

        $respondentsHtml = '';
        foreach ($respondents as $respondent) {
            $answersHtml = '';
            foreach ($poll->questions as $question) {
                $answer = \App\Models\Vote::antwoordTekst($respondent['votes'], $question->id);
                $answer = $answer === null ? '(Niet beantwoord)' : ($answer === '' ? '(Geen antwoord)' : $answer);
                
                $answersHtml .= sprintf(
                    '<div class="answer"><div class="answer-q">%s</div>%s</div>',
                    htmlspecialchars($question->question),
                    htmlspecialchars($answer)
                );
            }

            $status = $respondent['confirmed_at'] ? '✓ Bevestigd' : '⏳ In afwachting';
            $respondentsHtml .= sprintf(
                '<div class="respondent">
                    <div class="respondent-header">%s <span style="float:right;">%s</span></div>
                    <div class="respondent-info">
                        <strong>Email:</strong> %s<br>
                        <strong>Leeftijd:</strong> %s<br>
                        <strong>Datum:</strong> %s
                    </div>
                    <div class="answers">%s</div>
                </div>',
                htmlspecialchars($respondent['respondent_name']),
                $status,
                htmlspecialchars($respondent['email']),
                htmlspecialchars((string)($respondent['age'] ?? '-')),
                $respondent['created_at']->format('d-m-Y H:i'),
                $answersHtml
            );
        }

        return strtr($html, [
            '{POLL_TITLE}' => htmlspecialchars($poll->title),
            '{POLL_QUESTION}' => htmlspecialchars($poll->question ?? ''),
            '{TOTAL_RESPONDENTS}' => $totalRespondents,
            '{CONFIRMED_RESPONDENTS}' => $confirmedRespondents,
            '{QUESTION_COUNT}' => $poll->questions->count(),
            '{RESPONDENTS_HTML}' => $respondentsHtml,
            '{GENERATED_AT}' => now()->format('d-m-Y H:i'),
        ]);
    }

    /** Logo op de vragenlijst zelf opslaan; het vorige logo van deze vragenlijst gaat weg. */
    private function bewaarOrganisatieLogo(StorePollRequest $request, Poll $poll): void
    {
        if (! $request->hasFile('organisatie_logo') || ! $request->file('organisatie_logo')->isValid()) {
            return;
        }
        $pad = $request->file('organisatie_logo')->store('logos', 'public');
        if (! $pad) {
            return;
        }
        $oud = $poll->organisatie_logo;
        $poll->update(['organisatie_logo' => $pad]);
        if ($oud && $oud !== $pad && \Illuminate\Support\Facades\Storage::disk('public')->exists($oud)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oud);
        }
    }

    public function edit(Poll $poll): View
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        if ($poll->is_public) {
            return redirect()->route('admin.polls.show', $poll)
                ->with('error', 'Deze poll staat live. Zet hem eerst op inactief om de gegevens te bewerken.');
        }

        $optionsText = '';

        return view('admin.polls.form', [
            'poll' => $poll,
            'typeLabels' => PollType::labels(),
            'defaultsByType' => PollType::defaultsByType(),
            'action' => route('admin.polls.update', $poll),
            'method' => 'PUT',
            'optionsText' => $optionsText,
        ]);
    }

    public function update(StorePollRequest $request, Poll $poll): RedirectResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        if ($poll->is_public) {
            return redirect()->route('admin.polls.show', $poll)
                ->with('error', 'Deze poll staat live. Zet hem eerst op inactief om de gegevens te bewerken.');
        }

        return DB::transaction(function () use ($request, $poll) {
            $status = $request->validated('status');
            $accessCode = $request->input('access_code');
            
            // Generate unique access code if provided
            if ($accessCode && strlen($accessCode) > 0) {
                $accessCode = strtoupper(str_replace(' ', '', $accessCode));
            } else {
                $accessCode = null;
            }

            $poll->update([
                'title' => $request->validated('title'),
                'question' => (string) ($request->validated('question') ?? ''),
                'type' => $request->validated('type') ?? $poll->type,
                'status' => $status,
                'opens_at' => $request->validated('opens_at'),
                'closes_at' => $request->validated('closes_at'),
                'is_public' => $status === 'active',
                'access_code' => $accessCode,
                'requires_confirmation' => $request->boolean('requires_confirmation'),
                    'anonymous' => $request->boolean('anonymous'),
                    'organisatie_naam' => $request->filled('organisatie_naam') ? trim($request->input('organisatie_naam')) : null,
            ]);

            $this->bewaarOrganisatieLogo($request, $poll);

            return redirect()->route('admin.polls.show', $poll)->with('success', 'Poll bijgewerkt.');
        });
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        if (! $poll->isOwnedBy(auth()->user())) {
            abort(403);
        }

        $poll->delete();

        return redirect()->route('admin.polls.index')->with('success', 'Poll verwijderd.');
    }

    public function archive(Poll $poll): RedirectResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        $poll->update([
            'status' => 'archived',
            'is_public' => false,
        ]);

        return back()->with('success', 'Poll gearchiveerd.');
    }

    public function toggleActive(Poll $poll): RedirectResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        $newState = ! $poll->is_public;

        $poll->update([
            'is_public' => $newState,
            'status' => $newState ? 'active' : 'inactive',
        ]);

        return back()->with('success', $newState ? 'Poll staat nu actief.' : 'Poll staat nu inactief.');
    }

    public function deleteVote(Poll $poll, Vote $vote): RedirectResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        if ((int) $vote->poll_id !== (int) $poll->id) {
            abort(404);
        }

        try {
            $respondentEmail = $vote->email;
            $respondentName = $vote->respondent_name;

            Vote::where('poll_id', $poll->id)
                ->where('email', $respondentEmail)
                ->delete();

            Log::info('Respondent verwijderd', [
                'poll_id' => $poll->id,
                'email' => $respondentEmail,
                'name' => $respondentName,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', "✓ Respondent '$respondentName' en alle antwoorden verwijderd.");
        } catch (\Throwable $e) {
            Log::error('Fout bij verwijderen respondent', [
                'poll_id' => $poll->id,
                'vote_id' => $vote->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'De respondent kon niet worden verwijderd.');
        }
    }

    public function createQuestion(Poll $poll): View
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        $question = new PollQuestion([
            'type' => $poll->type,
        ]);

        return view('admin.polls.question-form', [
            'poll' => $poll,
            'question' => $question,
            'typeLabels' => PollType::labels(),
            'defaultsByType' => PollType::defaultsByType(),
            'action' => route('admin.polls.questions.store', $poll),
            'method' => 'POST',
            'optionsText' => '',
        ]);
    }

    public function storeQuestion(Poll $poll): RedirectResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        $data = $this->validateQuestion();

        return DB::transaction(function () use ($poll, $data) {
            $position = $poll->questions()->max('position') ?? -1;

            $question = $poll->questions()->create([
                'question' => $data['question'],
                'help_text' => $data['help_text'] ?? null,
                'met_toelichting' => request()->boolean('met_toelichting'),
                'groep' => trim((string) request()->input('groep', '')) ?: null,
                'kort' => trim((string) request()->input('kort', '')) ?: null,
                'groep_uitleg' => trim((string) request()->input('groep_uitleg', '')) ?: null,
                'type' => $data['type'],
                'position' => $position + 1,
            ]);

            $optionsText = request()->input('options_text', '');
            $this->syncQuestionOptions($question, (string) $optionsText);
            // Bij geen antwoord: alleen bewaren als het een bestaande optie van deze vraag is.
            $standaard = trim((string) request()->input('standaard_antwoord', ''));
            $question->update(['standaard_antwoord' => $standaard !== '' && $question->options()->where('label', $standaard)->exists() ? $standaard : null]);
            $stop = trim((string) request()->input('stop_bij', ''));
            $question->update(['stop_bij' => $stop !== '' && $question->options()->where('label', $stop)->exists() ? $stop : null]);
            $over = trim((string) request()->input('overslaan_bij', ''));
            $question->update(['overslaan_bij' => $over !== '' && $question->options()->where('label', $over)->exists() ? $over : null]);
            $vervolg = trim((string) request()->input('vervolg_bij', ''));
            $question->update(['vervolg_bij' => $vervolg !== '' && $question->options()->where('label', $vervolg)->exists() ? $vervolg : null]);

            return redirect()->route('admin.polls.show', $poll)->with('success', 'Vraag toegevoegd.');
        });
    }

    public function editQuestion(Poll $poll, PollQuestion $question): View
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        if ((int) $question->poll_id !== (int) $poll->id) {
            abort(404);
        }

        $optionsText = $question->options->map(function ($option) {
            if ($option->event_date) {
                return $option->event_date->format('d-m-Y');
            }

            return $option->label;
        })->implode("\n");

        return view('admin.polls.question-form', [
            'poll' => $poll,
            'question' => $question,
            'typeLabels' => PollType::labels(),
            'defaultsByType' => PollType::defaultsByType(),
            'action' => route('admin.polls.questions.update', [$poll, $question]),
            'method' => 'PUT',
            'optionsText' => $optionsText,
        ]);
    }

    public function updateQuestion(Poll $poll, PollQuestion $question): RedirectResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        if ((int) $question->poll_id !== (int) $poll->id) {
            abort(404);
        }

        $data = $this->validateQuestion();

        // Zijn er al antwoorden, dan mag het type niet meer om: de gegeven
        // antwoorden passen dan niet meer bij de vraag.
        $answerCount = $question->votes()->count();
        if ($answerCount > 0 && $data['type'] !== $question->type) {
            return back()->withInput()->with('error', "Op deze vraag zijn al {$answerCount} antwoorden gegeven. Het type kan daarom niet meer worden gewijzigd.");
        }

        return DB::transaction(function () use ($poll, $question, $data) {
            $question->update([
                'question' => $data['question'],
                'help_text' => $data['help_text'] ?? null,
                'met_toelichting' => request()->boolean('met_toelichting'),
                'groep' => trim((string) request()->input('groep', '')) ?: null,
                'kort' => trim((string) request()->input('kort', '')) ?: null,
                'groep_uitleg' => trim((string) request()->input('groep_uitleg', '')) ?: null,
                'type' => $data['type'],
            ]);

            $optionsText = request()->input('options_text', '');
            $this->syncQuestionOptions($question, (string) $optionsText);
            // Bij geen antwoord: alleen bewaren als het een bestaande optie van deze vraag is.
            $standaard = trim((string) request()->input('standaard_antwoord', ''));
            $question->update(['standaard_antwoord' => $standaard !== '' && $question->options()->where('label', $standaard)->exists() ? $standaard : null]);
            $stop = trim((string) request()->input('stop_bij', ''));
            $question->update(['stop_bij' => $stop !== '' && $question->options()->where('label', $stop)->exists() ? $stop : null]);
            $over = trim((string) request()->input('overslaan_bij', ''));
            $question->update(['overslaan_bij' => $over !== '' && $question->options()->where('label', $over)->exists() ? $over : null]);
            $vervolg = trim((string) request()->input('vervolg_bij', ''));
            $question->update(['vervolg_bij' => $vervolg !== '' && $question->options()->where('label', $vervolg)->exists() ? $vervolg : null]);

            return redirect()->route('admin.polls.show', $poll)->with('success', 'Vraag bijgewerkt.');
        });
    }

    public function destroyQuestion(Poll $poll, PollQuestion $question): RedirectResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        if ((int) $question->poll_id !== (int) $poll->id) {
            abort(404);
        }

        $question->delete();

        return back()->with('success', 'Vraag verwijderd.');
    }

    /** @return array{question: string, help_text: ?string, type: string} */
    private function validateQuestion(): array
    {
        return request()->validate([
            'question' => ['required', 'string', 'max:1000'],
            'help_text' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(PollType::labels()))],
            'options_text' => ['nullable', 'string', 'max:20000'],
        ], [
            'question.required' => 'Vul de vraag in.',
        ]);
    }

    /**
     * Zet de antwoordopties van een vraag gelijk aan de opgegeven lijst.
     *
     * Opties worden op volgorde BIJGEWERKT in plaats van weggegooid en opnieuw
     * aangemaakt. Vroeger verloren alle al gegeven antwoorden op een vraag hun
     * optie (poll_option_id werd NULL) zodra je de vraag opsloeg, ook bij alleen
     * een tikfout in de vraagtekst. Nu houdt optie 1 zijn id, optie 2 ook, enz.
     * Alleen opties die echt uit de lijst verdwijnen worden verwijderd.
     */
    private function syncQuestionOptions(PollQuestion $question, $optionsText): void
    {
        if ($question->type === PollType::RATING_STARS) {
            $options = collect(PollType::defaults(PollType::RATING_STARS));
        } else {
            // Handle both string and array input
            if (is_array($optionsText)) {
                $options = collect($optionsText);
            } else {
                $options = collect(preg_split('/\r\n|\r|\n/', trim($optionsText)))
                    ->map(fn ($line) => trim((string) $line))
                    ->filter();
            }
        }

        if ($options->isEmpty()) {
            $options = collect(PollType::defaults($question->type));
        }

        $options = $options->values();
        $existing = $question->options()->get()->values();

        // 1. Opties waarvan de tekst gelijk bleef, houden hun eigen rij.
        $assigned = [];   // index nieuwe lijst => PollOption
        $used = [];
        foreach ($options as $index => $label) {
            $match = $existing->first(fn ($o) => ! isset($used[$o->id]) && $o->label === $label);
            if ($match) {
                $assigned[$index] = $match;
                $used[$match->id] = true;
            }
        }

        // 2. Nieuwe teksten nemen een vrijgekomen rij over (= hernoemd), op volgorde.
        $free = $existing->reject(fn ($o) => isset($used[$o->id]))->values();
        foreach ($options as $index => $label) {
            if (! isset($assigned[$index]) && $free->isNotEmpty()) {
                $assigned[$index] = $free->shift();
            }
        }

        foreach ($options as $index => $label) {
            $eventDate = null;

            if (PollType::needsDateParsing($question->type)) {
                try {
                    $eventDate = Carbon::createFromFormat('d-m-Y', $label)->toDateString();
                } catch (\Throwable $e) {
                    $eventDate = null;
                }
            }

            $attributes = [
                'poll_id' => $question->poll_id,
                'label' => $label,
                'value' => $label,
                'position' => $index,
                'event_date' => $eventDate,
            ];

            if (isset($assigned[$index])) {
                $assigned[$index]->update($attributes);
            } else {
                $question->options()->create($attributes);
            }
        }

        // 3. Wat overblijft is echt uit de lijst gehaald.
        $free->each->delete();
    }

    public function exportCsv(Poll $poll): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        $filename = 'stemmen_' . \Illuminate\Support\Str::slug($poll->title, '_') . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($poll) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header rij
            $headers = ['Naam', 'Email', 'Leeftijd', 'Datum ingevuld'];
            foreach ($poll->questions as $question) {
                $headers[] = $question->question;
            }
            $headers[] = 'Status';

            fputcsv($file, $headers, ';');

            // Group votes by respondent
            $respondents = Vote::where('poll_id', $poll->id)
                ->get()
                ->groupBy('email')
                ->map(function ($group) {
                    return [
                        'respondent_name' => $group->first()->respondent_name,
                        'email' => $group->first()->email,
                        'age' => $group->first()->age,
                        'created_at' => $group->first()->created_at,
                        'votes' => $group,
                    ];
                });

            // Data rijen
            foreach ($respondents as $respondent) {
                $row = [
                    $respondent['respondent_name'],
                    $respondent['email'],
                    $respondent['age'],
                    $respondent['created_at']->format('d-m-Y H:i'),
                ];

                foreach ($poll->questions as $question) {
                    $row[] = \App\Models\Vote::antwoordTekst($respondent['votes'], $question->id) ?? '';
                }

                $row[] = $respondent['votes']->first()?->confirmed_at ? 'Bevestigd' : 'In afwachting';

                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sendEmailForm(Poll $poll): View
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        $respondents = Vote::where('poll_id', $poll->id)
            ->get(['email', 'respondent_name'])
            ->unique('email')
            ->map(fn($vote) => [
                'email' => $vote->email,
                'name' => $vote->respondent_name,
            ])
            ->sortBy('name')
            ->values();

        return view('admin.polls.send-email', [
            'poll' => $poll,
            'respondents' => $respondents,
        ]);
    }

    public function sendEmail(Poll $poll): RedirectResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        $validated = request()->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'recipient_type' => 'required|in:all,unconfirmed,specific',
            'specific_emails' => 'nullable|array',
            'specific_emails.*' => 'email',
        ]);

        $recipients = [];

        if ($validated['recipient_type'] === 'all') {
            $recipients = Vote::where('poll_id', $poll->id)
                ->get(['email', 'respondent_name'])
                ->unique('email')
                ->keyBy('email');
        } elseif ($validated['recipient_type'] === 'unconfirmed') {
            $recipients = Vote::where('poll_id', $poll->id)
                ->whereNull('confirmed_at')
                ->get(['email', 'respondent_name'])
                ->unique('email')
                ->keyBy('email');
        }

        if (!empty($validated['specific_emails'])) {
            $specificVotes = Vote::where('poll_id', $poll->id)
                ->whereIn('email', $validated['specific_emails'])
                ->get(['email', 'respondent_name'])
                ->unique('email')
                ->keyBy('email');
            $recipients = $specificVotes;
        }

        $subject = $validated['subject'];
        $message = $validated['message'];
        $successCount = 0;

        foreach ($recipients as $email => $vote) {
            try {
                Mail::raw($message, function ($m) use ($email, $vote, $subject, $poll) {
                    $m->to($email, $vote->respondent_name)
                      ->subject($subject);
                });
                $successCount++;
            } catch (\Throwable $e) {
                Log::error('Email verzenden mislukt', [
                    'poll_id' => $poll->id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', "✓ Email verzonden naar {$successCount} respondenten!");
    }

    public function reorderQuestions(Poll $poll): JsonResponse
    {
        if (! $poll->isManagedBy(auth()->user())) {
            abort(403);
        }

        $order = request()->input('order', []);

        if (!is_array($order)) {
            return response()->json(['error' => 'Ongeldige volgorde'], 400);
        }

        try {
            DB::transaction(function () use ($poll, $order) {
                foreach ($order as $position => $questionId) {
                    PollQuestion::query()
                        ->where('id', $questionId)
                        ->where('poll_id', $poll->id)
                        ->update(['position' => $position]);
                }
            });

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Volgorde bijwerken mislukt', [
                'poll_id' => $poll->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Fout bij bijwerken volgorde'], 500);
        }
    }
}

