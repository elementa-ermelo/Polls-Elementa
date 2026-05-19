<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoteRequest;
use App\Models\Poll;
use App\Models\Vote;
use App\Support\PollType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\View\View;

class PollController extends Controller
{
    public function index(): RedirectResponse|View
    {
        Poll::archiveExpiredPolls();

        $firstPoll = Poll::query()
            ->where('status', '!=', 'archived')
            ->where('is_public', true)
            ->latest()
            ->first();

        if (!$firstPoll) {
            return view('polls.index', ['polls' => collect()]);
        }

        return redirect()->route('polls.show', $firstPoll);
    }

    public function show(Poll $poll): View|RedirectResponse
    {
        if (!$poll->is_public) {
            return redirect()->route('polls.index');
        }

        $poll->load(['questions.options', 'options']);
        
        $allPolls = Poll::query()
            ->where('is_public', true)
            ->where('status', '!=', 'archived')
            ->latest()
            ->get();

        // Check if poll is protected with access code and not verified yet
        $needsAccessCode = false;
        if ($poll->access_code) {
            $providedCode = session("poll_access_{$poll->id}");
            if (!$providedCode || $providedCode !== $poll->access_code) {
                $needsAccessCode = true;
            }
        }

        return view('polls.show', [
            'poll' => $poll,
            'allPolls' => $allPolls,
            'needsAccessCode' => $needsAccessCode,
        ]);
    }

    public function vote(StoreVoteRequest $request, Poll $poll): RedirectResponse
    {
        if (!$poll->is_public) {
            return redirect()->route('polls.index');
        }

        if (! $poll->isOpen()) {
            return back()->with('error', 'Deze poll is gesloten.');
        }

        // Check access code if poll has one
        if ($poll->access_code) {
            $providedCode = session("poll_access_{$poll->id}");
            if (!$providedCode || $providedCode !== $poll->access_code) {
                return back()->with('error', 'Voer eerst de juiste toegangscode in.');
            }
        }

        $questions = $poll->questions()->get();
        if ($questions->isEmpty()) {
            return back()->with('error', 'Deze poll bevat geen vragen.');
        }

        $respondentName = $request->string('respondent_name')->toString();
        $email          = $request->string('email')->toString();
        $phone          = $request->string('phone')->toString();
        $age            = $request->integer('age');
        $token          = Str::uuid()->toString();

        try {
            DB::transaction(function () use ($request, $poll, $respondentName, $email, $phone, $age, $token): void {
                $questions = $poll->questions()->get();
                $isFirstQuestion = true;

                // Clear old confirmation tokens for this email + poll (in case of re-submission)
                Vote::query()
                    ->where('poll_id', $poll->id)
                    ->where('email', $email)
                    ->whereNotNull('confirmation_token')
                    ->where('confirmed_at', null)
                    ->update(['confirmation_token' => null, 'confirmation_sent_at' => null]);

                foreach ($questions as $question) {
                    $isOpenType = PollType::isOpenTextType((string) $question->type);
                    $option     = null;
                    $openAnswer = null;

                    if (! $isOpenType) {
                        $optionId = $request->input("question_{$question->id}");
                        if ($optionId) {
                            $option = $question->options()->whereKey($optionId)->first();
                        }
                    } else {
                        $openAnswer = $request->string("question_{$question->id}")->toString();
                    }

                    // Only set confirmation token on first question (all votes share same token)
                    $updateData = [
                        'poll_option_id'  => $option?->id,
                        'respondent_name' => $respondentName,
                        'email'           => $email,
                        'phone'           => $phone,
                        'age'             => $age,
                        'numeric_value'   => $option && is_numeric($option->value) ? (int) $option->value : null,
                        'open_answer'     => $openAnswer,
                    ];

                    if ($isFirstQuestion) {
                        $updateData['confirmation_token']   = $token;
                        $updateData['confirmation_sent_at'] = now();
                        $updateData['confirmed_at']         = null;
                        $isFirstQuestion = false;
                    }

                    Vote::query()->updateOrCreate(
                        [
                            'poll_id'          => $poll->id,
                            'poll_question_id' => $question->id,
                            'email'            => $email,
                        ],
                        $updateData
                    );
                }
            });
        } catch (Throwable $e) {
            \Log::error('Vote save error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'poll_id' => $poll->id,
                'email' => $email,
            ]);
            return back()->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())->withInput();
        }

        // Stem is opgeslagen — mail apart versturen
        try {
            $confirmUrl = route('polls.confirm', $token);
            $message = "Hallo {$respondentName},\n\n";
            $message .= "Bedankt voor het invullen van de poll '{$poll->title}'.\n\n";
            $message .= "Bevestig je stem via deze link:\n";
            $message .= $confirmUrl . "\n\n";
            $message .= "Deze link verloopt over 24 uur.\n\n";
            $message .= "Met vriendelijke groeten,\n";
            $message .= "Polls Elementa";

            Mail::raw($message, function ($m) use ($email, $respondentName, $poll) {
                $m->to($email, $respondentName)
                  ->subject("⏳ Bevestig je stem: {$poll->title}");
            });

            \Log::info("Bevestigingsmail verzonden", [
                'email' => $email,
                'poll_id' => $poll->id,
                'token' => substr($token, 0, 8),
            ]);

            return redirect()->route('polls.show', $poll)
                ->with('success', '✓ Je stem is opgeslagen! Check je mailbox voor de bevestigingslink.');
        } catch (Throwable $e) {
            \Log::error("Mail verzenden mislukt: {$e->getMessage()}", [
                'email' => $email,
                'poll_id' => $poll->id,
            ]);

            return redirect()->route('polls.show', $poll)
                ->with('success', '✓ Je stem is opgeslagen, maar de bevestigingsmail kon niet worden verzonden. Neem contact op met de organisator.');
        }
    }

    public function confirm(string $token): RedirectResponse
    {
        $vote = Vote::query()->where('confirmation_token', $token)->first();

        if (! $vote) {
            return redirect()->route('polls.index')->with('error', 'Ongeldige of verlopen bevestigingslink.');
        }

        $poll = $vote->poll;

        if (! $poll || ! $poll->isOpen()) {
            return redirect()->route('polls.index')->with('error', 'De poll is gesloten; bevestigen is niet meer mogelijk.');
        }

        // Confirm all votes for this poll+email (they all share the same respondent)
        Vote::query()
            ->where('poll_id', $poll->id)
            ->where('email', $vote->email)
            ->update([
                'confirmed_at' => now(),
                'confirmation_token' => null,
            ]);

        return redirect()->route('polls.show', $poll)->with('success', 'Je stem is bevestigd en verwerkt.');
    }

    public function verifyAccessCode(Poll $poll): RedirectResponse
    {
        $code = strtoupper(str_replace(' ', '', request()->string('access_code')->toString()));

        if ($code !== $poll->access_code) {
            return back()->with('error', 'Ongeldige toegangscode.');
        }

        session(["poll_access_{$poll->id}" => $code]);

        return redirect()->route('polls.show', $poll);
    }

}

