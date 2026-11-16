<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoteRequest;
use App\Models\Poll;
use App\Models\Vote;
use App\Support\PollType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
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

        // Herkomst onthouden: de site waar de bezoeker vandaan kwam, of ?bron=... (bijv. op een flyer-QR).
        $bron = $this->herkomstUitVerzoek();
        if ($bron !== null) {
            session(["poll_herkomst_{$poll->id}" => $bron]);
        }

        $deviceUsed = $poll->anonymous ? $this->deviceSlotsUsed($poll) : 0;
        $deviceRoom = $deviceUsed < self::PER_APPARAAT;

        return view('polls.show', [
            'poll' => $poll,
            'allPolls' => $allPolls,
            'needsAccessCode' => $needsAccessCode,
            'alreadyOnDevice' => $deviceUsed > 0 && ! ($deviceRoom && request()->boolean('nog')),
            'deviceRoom' => $deviceRoom,
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

        // Spamrem (lokveld, robotbrowser, kaartje, vrije tekst, StopForumSpam, plafond): zie Formulierrem.
        $rem = \App\Support\Formulierrem::controleer($request, $poll);
        if ($rem === 'verlopen') {
            return back()->withInput()->with('error', 'Deze pagina stond te lang open. Uw antwoorden staan er nog; wilt u ze opnieuw versturen?');
        }
        if ($rem === 'stil') {
            return redirect()->route('polls.show', $poll)->with('success', 'Bedankt. Uw antwoorden zijn opgeslagen.');
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

        // Anoniem: geen persoonsgegevens. Het apparaatkenmerk (cookie) staat in de
        // e-mailkolom, zodat de bestaande controle "al ingevuld" en de groepering in
        // de resultaten per inzender gewoon blijven werken.
        $anonLimitKey = null;
        if ($poll->anonymous) {
            $slot = $this->deviceSlotsUsed($poll);
            if ($slot >= self::PER_APPARAAT) {
                return redirect()->route('polls.show', $poll);
            }
            $anonLimitKey = 'anon-vote:' . $poll->id . ':' . sha1((string) $request->ip());
            if (RateLimiter::tooManyAttempts($anonLimitKey, 15)) {
                \App\Support\Formulierrem::log('rem', (string) $request->ip());
                return back()->withInput()->with('error', 'Vanaf deze internetaansluiting is de vragenlijst vandaag al vaak ingevuld. Probeert u het morgen nog eens.');
            }
            $respondentName = 'Anoniem';
            $email          = $this->deviceKey($poll, $this->deviceId(true), $slot);
            $phone          = '';
            $age            = 0;
            $needsConfirm   = false;
        }

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
            $wegIds = \App\Support\Stopregel::overgeslagen($poll, $request->all());
            DB::transaction(function () use ($request, $poll, $respondentName, $email, $phone, $age, $token, $needsConfirm, $wegIds): void {
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
                    $overgeslagen = in_array($question->id, $wegIds, true);
                    $isOpenType = PollType::isOpenTextType((string) $question->type);
                    $option     = null;
                    $openAnswer = null;

                    if (! $isOpenType) {
                        $optionId = $request->input("question_{$question->id}");
                        if ($optionId) {
                            $option = $question->options()->whereKey($optionId)->first();
                        }
                        // Leeg gelaten en de vraag heeft een standaardantwoord: dat telt.
                        if (! $option && $question->standaard_antwoord && ! $overgeslagen) {
                            $option = $question->options()->where('label', $question->standaard_antwoord)->first();
                        }
                        // Toelichting bij een cijfervraag (niet verplicht) in hetzelfde antwoordveld.
                        $toelichting = trim($request->string("toelichting_{$question->id}")->toString());
                        $openAnswer = $toelichting === '' ? null : $toelichting;
                    } else {
                        // Open vragen zijn niet verplicht: leeg opslaan als null, zodat het resultatenscherm het overslaat.
                        $openAnswer = trim($request->string("question_{$question->id}")->toString());
                        $openAnswer = $openAnswer === '' ? null : $openAnswer;
                    }

                    // Na een slimme sprong: wat niet is ingevuld, wordt ook niet opgeslagen.
                    if ($overgeslagen && ! $option && $openAnswer === null) {
                        continue;
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
                        'herkomst'        => session("poll_herkomst_{$poll->id}", 'direct'),
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

        if ($anonLimitKey) {
            RateLimiter::hit($anonLimitKey, 86400);
        }

        \App\Support\Formulierrem::geteld($poll);
        $this->piekMelding($poll);

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

    /** Anoniem apparaatkenmerk uit de cookie; maakt er een aan als $create waar is. */
    private function deviceId(bool $create = false): ?string
    {
        $id = request()->cookie('polls_apparaat');
        if (is_string($id) && preg_match('/^[0-9a-f-]{36}$/', $id)) {
            return $id;
        }
        if (! $create) {
            return null;
        }
        $id = Str::uuid()->toString();
        Cookie::queue(Cookie::make('polls_apparaat', $id, 60 * 24 * 365 * 2, '/', null, true, true, false, 'Lax'));
        return $id;
    }

    /** Hoeveel mensen mogen op één apparaat invullen (echtpaar met één tablet). */
    private const PER_APPARAAT = 3;

    /** Inzenders per uur waarboven de eigenaar een piekmelding krijgt (hooguit eens per 3 uur). */
    private const PIEK_PER_UUR = 25;

    private function deviceKey(Poll $poll, string $deviceId, int $slot = 0): string
    {
        $basis = $poll->id . '|' . $deviceId . ($slot > 0 ? '|' . $slot : '');
        return 'apparaat-' . substr(hash('sha256', $basis), 0, 32) . '@anoniem.invalid';
    }

    /** Aantal inzendingen dat al vanaf dit apparaat is gedaan (0 t/m PER_APPARAAT). */
    private function deviceSlotsUsed(Poll $poll): int
    {
        $id = $this->deviceId();
        if ($id === null) {
            return 0;
        }
        $used = 0;
        for ($slot = 0; $slot < self::PER_APPARAAT; $slot++) {
            $bestaat = Vote::query()
                ->where('poll_id', $poll->id)
                ->where('email', $this->deviceKey($poll, $id, $slot))
                ->exists();
            if (! $bestaat) {
                break;
            }
            $used++;
        }
        return $used;
    }

    /** Domein van de verwijzende site of ?bron=..., zonder www./m./l.; null als het van deze site zelf komt. */
    private function herkomstUitVerzoek(): ?string
    {
        $bron = trim(mb_strtolower(request()->string('bron')->toString()));
        if ($bron !== '') {
            return mb_substr(preg_replace('/[^a-z0-9._-]/', '', $bron), 0, 100) ?: null;
        }
        $host = parse_url((string) request()->headers->get('referer'), PHP_URL_HOST);
        if (! is_string($host) || $host === '' || $host === request()->getHost()) {
            return null;
        }
        $host = mb_strtolower(preg_replace('/^(www|m|l|lm|mobile)\./', '', $host));
        return mb_substr($host, 0, 100);
    }

    /** Mail aan de eigenaar als er ineens veel inzendingen binnenkomen (teken van een oproep in een groep). */
    private function piekMelding(Poll $poll): void
    {
        try {
            $sinds = now()->subHour();
            $aantal = Vote::query()->where('poll_id', $poll->id)->where('created_at', '>=', $sinds)
                ->distinct('email')->count('email');
            if ($aantal < self::PIEK_PER_UUR || ! Cache::add("piekmelding:{$poll->id}", 1, now()->addHours(3))) {
                return;
            }
            $ontvanger = $poll->user?->email;
            if (! $ontvanger || ! str_contains($ontvanger, '@')) {
                return;
            }
            $perBron = Vote::query()->where('poll_id', $poll->id)->where('created_at', '>=', $sinds)
                ->selectRaw("COALESCE(herkomst, 'onbekend') AS bron, COUNT(DISTINCT email) AS n")
                ->groupBy('bron')->orderByDesc('n')->get()
                ->map(fn ($r) => "- {$r->bron}: {$r->n}")->implode("\n");
            $tekst = "Bij de vragenlijst '{$poll->title}' kwamen het afgelopen uur {$aantal} inzendingen binnen.\n\n"
                . "Herkomst:\n{$perBron}\n\n"
                . "In het beheer kunt u bij Resultaten op herkomst filteren.\n"
                . route('admin.polls.show', $poll) . "\n\n"
                . "U krijgt deze melding hooguit eens per drie uur.";
            Mail::raw($tekst, fn ($m) => $m->to($ontvanger)->subject("Veel inzendingen: {$poll->title}"));
            \App\Support\Telefoonalarm::stuur("Veel inzendingen: {$poll->title}", "{$aantal} inzendingen in het afgelopen uur.\n{$perBron}");
            \Log::info('Piekmelding verstuurd', ['poll_id' => $poll->id, 'aantal' => $aantal]);
        } catch (Throwable $e) {
            \Log::error('Piekmelding mislukt: ' . $e->getMessage(), ['poll_id' => $poll->id]);
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

