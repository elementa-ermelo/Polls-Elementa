<?php

namespace App\Support;

use App\Models\Poll;

/**
 * Slimme sprong: een vraag met stop_bij (bijv. "Hoe vaak komt u in het centrum?" = "Nooit") maakt de
 * vragen erna overbodig. Die zijn dan niet verplicht, worden niet opgeslagen en krijgen ook geen
 * standaardantwoord. Kiest de invuller "toch alle vragen invullen", dan worden ingevulde antwoorden
 * gewoon bewaard.
 */
class Stopregel
{
    /** Positie van de vraag waarbij de invuller het stopantwoord koos, of null. */
    public static function positie(Poll $poll, array $invoer): ?int
    {
        foreach ($poll->questions as $q) {
            if (! $q->stop_bij) {
                continue;
            }
            $gekozen = $invoer['question_' . $q->id] ?? null;
            if (! $gekozen || ! ctype_digit((string) $gekozen)) {
                continue;
            }
            $opt = $q->options->firstWhere('id', (int) $gekozen);
            if ($opt && $opt->label === $q->stop_bij) {
                return (int) $q->position;
            }
        }
        return null;
    }
}
