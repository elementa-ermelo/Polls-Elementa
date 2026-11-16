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
            return back()->with('error', 'Deze vragenlijst is gesloten.');
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
            return back()->with('error', 'Deze vragenlijst bevat geen vragen.');
        }

        $respondentName = $request->string('respondent_name')->toString();
        $email          = mb_strtolower(trim($request->string('email')->toString()));
        $phone          = $request->string('phone')->toString();
        $age            = $request->integer('age');
        $token          = Str::uuid()->toString();
        $needsConfirm   = (bool) $poll->requires_confirmation;

        // Wie al (bevestigd) heeft ingevuld, kan niet overschreven worden door
        // iemand die alleen het e-mailadres weet. Vroeger kon dat wel: een tweede
        // inzending met hetzelfde adres verving de antwoorden van de eerste.
        $alreadyAnswered = Vote::query()
            ->where('poll_id', $poll->id)
            ->where('email', $email)
            ->whereNotNull('confirmed_at')
            ->exists();

        if ($alreadyAnswered) {
            return back()->withInput()->with('error', 'Met dit e-mailadres is deze vragenlijst al ingevuld.');
        }

        try {
            DB::transaction(function () use ($request, $poll, $respondentName, $email, $phone, $age, $token, $needsConfirm): void {
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
                        // Open vragen zijn niet verplicht: leeg opslaan als null, zodat het resultatenscherm het overslaat.
                        $openAnswer = trim($request->string("question_{$question->id}")->toString());
                        $openAnswer = $openAnswer === '' ? null : $openAnswer;
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

                    if (! $needsConfirm) {
                        // Geen e-mailbevestiging voor deze poll: telt direct mee.
                        $updateData['confirmation_token']   = null;
                        $updateData['confirmed_at']         = now();
                    } elseif ($isFirstQuestion) {
                        $updateData['confirmation_token']   = $token;
                        $updateData['confirmation_sent_at'] = now();
                        $updateData['confirmed_at']         = null;
                        $isFirstQuestion = false;
                    } else {
                        $updateData['confirmed_at']         = null;
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
            return back()->with('error', 'Uw antwoorden zijn niet opgeslagen. Probeert u het nog een keer.')->withInput();
        }

        if (! $needsConfirm) {
            return redirect()->route('polls.show', $poll)
                ->with('success', 'Bedankt. Uw antwoorden zijn opgeslagen.');
        }

        // Stem is opgeslagen — mail apart versturen
        try {
            $confirmUrl = route('polls.confirm', $token);
            $message = "Beste {$respondentName},\n\n";
            $message .= "Bedankt voor het invullen van de vragenlijst '{$poll->title}'.\n\n";
            $message .= "Uw antwoorden tellen mee zodra u ze bevestigt via deze link:\n";
            $message .= $confirmUrl . "\n\n";
            $message .= "Deze link is 7 dagen geldig.\n\n";
            $message .= "Met vriendelijke groeten,\n";
            $message .= "Polls Elementa";

            Mail::raw($message, function ($m) use ($email, $respondentName, $poll) {
                $m->to($email, $respondentName)
                  ->subject("Bevestig uw antwoorden: {$poll->title}");
            });

            \Log::info("Bevestigingsmail verzonden", [
                'email' => $email,
                'poll_id' => $poll->id,
                'token' => substr($token, 0, 8),
            ]);

            return redirect()->route('polls.show', $poll)
                ->with('success', 'Uw antwoorden zijn opgeslagen. U krijgt een e-mail met een bevestigingslink. Ziet u die niet, kijk dan ook bij de ongewenste e-mail.');
        } catch (Throwable $e) {
            \Log::error("Mail verzenden mislukt: {$e->getMessage()}", [
                'email' => $email,
                'poll_id' => $poll->id,
            ]);

            return redirect()->route('polls.show', $poll)
                ->with('success', 'Uw antwoorden zijn opgeslagen. De bevestigingsmail is niet verstuurd. Neemt u contact op met de organisator.');
        }
    }

    public function confirm(string $token): RedirectResponse
    {
        $vote = Vote::query()->where('confirmation_token', $token)->first();

        if (! $vote) {
            return redirect()->route('polls.index')->with('error', 'Deze bevestigingslink werkt niet of is verlopen.');
        }

        if ($vote->confirmation_sent_at && \Illuminate\Support\Carbon::parse($vote->confirmation_sent_at)->lt(now()->subDays(7))) {
            return redirect()->route('polls.index')->with('error', 'Deze bevestigingslink is verlopen. Vult u de vragenlijst opnieuw in, dan krijgt u een nieuwe link.');
        }

        $poll = $vote->poll;

        if (! $poll || ! $poll->isOpen()) {
            return redirect()->route('polls.index')->with('error', 'De vragenlijst is gesloten. Bevestigen kan niet meer.');
        }

        // Confirm all votes for this poll+email (they all share the same respondent)
        Vote::query()
            ->where('poll_id', $poll->id)
            ->where('email', $vote->email)
            ->update([
                'confirmed_at' => now(),
                'confirmation_token' => null,
            ]);

        return redirect()->route('polls.show', $poll)->with('success', 'Uw antwoorden zijn bevestigd en tellen mee.');
    }

    public function verifyAccessCode(Poll $poll): RedirectResponse
    {
        $code = strtoupper(str_replace(' ', '', request()->string('access_code')->toString()));

        if ($code !== $poll->access_code) {
            return back()->with('error', 'Deze toegangscode is niet goed.');
        }

        session(["poll_access_{$poll->id}" => $code]);

        return redirect()->route('polls.show', $poll);
    }

}

