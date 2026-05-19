<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePollRequest;
use App\Models\Poll;
use App\Models\PollQuestion;
use App\Models\Vote;
use App\Support\PollType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        // Only show own polls unless admin
        if (!auth()->check() || !auth()->user()->is_admin) {
            $query->where('user_id', auth()->id());
        }

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
            'type' => 'single',
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
                $isPublic = (bool) $request->boolean('is_public');
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
                    'user_id' => auth()->id(),
                ]);

                Log::info('Poll aangemaakt in database', [
                    'poll_id' => $poll->id,
                    'title' => $poll->title,
                    'user_id' => auth()->id(),
                ]);

                // Process questions from JSON
                $questionsJson = $request->input('questions_json', '{}');
                try {
                    $questions = json_decode($questionsJson, true) ?? [];
                    
                    foreach ($questions as $qData) {
                        $question = $poll->questions()->create([
                            'question' => $qData['question'] ?? '',
                            'type' => $qData['type'] ?? $poll->type,
                            'position' => $qData['position'] ?? 0,
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

            return redirect()->back()->withErrors(['error' => 'Fout bij aanmaken poll: ' . $e->getMessage()]);
        }
    }

    public function show(Poll $poll): View
    {
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        $poll->load([
            'questions.options',
            'questions.votes',
            'options.votes',
            'votes' => fn ($query) => $query->latest()->with(['option', 'question']),
        ]);

        $totalRespondents = $poll->votes()->distinct('email')->count('email');
        $confirmedRespondents = $poll->confirmedVotes()->distinct('email')->count('email');

        return view('admin.polls.show', [
            'poll' => $poll,
            'totalRespondents' => $totalRespondents,
            'confirmedRespondents' => $confirmedRespondents,
        ]);
    }

    public function edit(Poll $poll): View
    {
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        if ($poll->is_public) {
            abort(403, 'Je kunt een actieve poll niet bewerken.');
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
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        if ($poll->is_public) {
            abort(403, 'Je kunt een actieve poll niet bewerken.');
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
                'is_public' => $status !== 'archived',
                'access_code' => $accessCode,
            ]);

            return redirect()->route('admin.polls.show', $poll)->with('success', 'Poll bijgewerkt.');
        });
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        $poll->delete();

        return redirect()->route('admin.polls.index')->with('success', 'Poll verwijderd.');
    }

    public function archive(Poll $poll): RedirectResponse
    {
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
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
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
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
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        if ((int) $vote->poll_id !== (int) $poll->id) {
            abort(404);
        }

        $vote->delete();

        return back()->with('success', 'Stem verwijderd.');
    }

    public function createQuestion(Poll $poll): View
    {
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
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
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        return DB::transaction(function () use ($poll) {
            $position = $poll->questions()->max('position') ?? -1;

            $question = $poll->questions()->create([
                'question' => request()->string('question')->toString(),
                'type' => request()->string('type')->toString(),
                'position' => $position + 1,
            ]);

            $optionsText = request()->input('options_text', '');
            $this->syncQuestionOptions($question, (string) $optionsText);

            return redirect()->route('admin.polls.show', $poll)->with('success', 'Vraag toegevoegd.');
        });
    }

    public function editQuestion(Poll $poll, PollQuestion $question): View
    {
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
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
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        if ((int) $question->poll_id !== (int) $poll->id) {
            abort(404);
        }

        return DB::transaction(function () use ($poll, $question) {
            $question->update([
                'question' => request()->string('question')->toString(),
                'type' => request()->string('type')->toString(),
            ]);

            $optionsText = request()->input('options_text', '');
            $this->syncQuestionOptions($question, (string) $optionsText);

            return redirect()->route('admin.polls.show', $poll)->with('success', 'Vraag bijgewerkt.');
        });
    }

    public function destroyQuestion(Poll $poll, PollQuestion $question): RedirectResponse
    {
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        if ((int) $question->poll_id !== (int) $poll->id) {
            abort(404);
        }

        $question->delete();

        return back()->with('success', 'Vraag verwijderd.');
    }

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

        $question->options()->delete();

        foreach ($options->values() as $index => $label) {
            $eventDate = null;

            if (PollType::needsDateParsing($question->type)) {
                try {
                    $eventDate = Carbon::createFromFormat('d-m-Y', $label)->toDateString();
                } catch (\Throwable $e) {
                    $eventDate = null;
                }
            }

            $question->options()->create([
                'poll_id' => $question->poll_id,
                'label' => $label,
                'value' => $label,
                'position' => $index,
                'event_date' => $eventDate,
            ]);
        }
    }

    public function exportCsv(Poll $poll): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        $filename = "stemmen_" . $poll->title . "_" . now()->format('Y-m-d') . ".csv";

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
                    $vote = $respondent['votes']->firstWhere('poll_question_id', $question->id);
                    if ($vote) {
                        $row[] = $vote->option?->label ?? $vote->open_answer ?? '';
                    } else {
                        $row[] = '';
                    }
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
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        $respondents = Vote::where('poll_id', $poll->id)
            ->distinct('email')
            ->get(['email', 'respondent_name'])
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
        if (!auth()->user()->is_admin && $poll->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = request()->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'recipient_type' => 'required|in:all,unconfirmed',
            'specific_emails' => 'nullable|array',
            'specific_emails.*' => 'email',
        ]);

        $recipients = [];

        if ($validated['recipient_type'] === 'all') {
            $recipients = Vote::where('poll_id', $poll->id)
                ->distinct('email')
                ->get(['email', 'respondent_name'])
                ->keyBy('email');
        } elseif ($validated['recipient_type'] === 'unconfirmed') {
            $recipients = Vote::where('poll_id', $poll->id)
                ->whereNull('confirmed_at')
                ->distinct('email')
                ->get(['email', 'respondent_name'])
                ->keyBy('email');
        }

        if (!empty($validated['specific_emails'])) {
            $specificVotes = Vote::where('poll_id', $poll->id)
                ->whereIn('email', $validated['specific_emails'])
                ->distinct('email')
                ->get(['email', 'respondent_name'])
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
}

