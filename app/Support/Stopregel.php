<?php

namespace App\Support;

use App\Models\Poll;

/**
 * Slimme sprongen. Twee soorten, allebei per vraag in het beheer in te stellen (op de tekst van de optie):
 *  - stop_bij: bij dit antwoord zijn ALLE vragen erna niet nodig (bijv. "Hoe vaak komt u?" = "Nooit");
 *  - overslaan_bij: bij dit antwoord valt alleen de VOLGENDE vraag weg (bijv. boodschappen "Ja, alles");
 *  - vervolg_bij: de VOLGENDE vraag verschijnt alleen bij dit antwoord (bijv. "Kunt u voor alles terecht?" = "Nee" -> "Wat mist u?").
 * Overgeslagen vragen zijn niet verplicht, worden niet opgeslagen en krijgen geen standaardantwoord.
 * Vult de invuller ze toch in ("toch alle vragen invullen"), dan worden die antwoorden gewoon bewaard.
 */
class Stopregel
{
    private static function gekozen(\App\Models\PollQuestion $q, array $invoer, ?string $label): bool
    {
        if (! $label) {
            return false;
        }
        $gekozen = $invoer['question_' . $q->id] ?? null;
        if (! $gekozen || ! ctype_digit((string) $gekozen)) {
            return false;
        }
        $opt = $q->options->firstWhere('id', (int) $gekozen);
        return $opt && $opt->label === $label;
    }

    /** Positie van de vraag waarbij de invuller het stopantwoord koos, of null. */
    public static function positie(Poll $poll, array $invoer): ?int
    {
        $verborgen = self::verborgen($poll, $invoer);
        foreach ($poll->questions as $q) {
            if (in_array($q->id, $verborgen, true)) {
                continue; // een verborgen vervolgvraag kan niet laten stoppen
            }
            if (self::gekozen($q, $invoer, $q->stop_bij)) {
                return (int) $q->position;
            }
        }
        return null;
    }

    /** @return int[] id's van de vragen die voor deze invuller wegvallen. */
    public static function overgeslagen(Poll $poll, array $invoer): array
    {
        $vragen = $poll->questions->sortBy('position')->values();
        $stop = self::positie($poll, $invoer);
        $weg = [];
        foreach ($vragen as $i => $q) {
            if ($stop !== null && (int) $q->position > $stop) {
                $weg[] = $q->id;
            }
            if (self::gekozen($q, $invoer, $q->overslaan_bij) && isset($vragen[$i + 1])) {
                $weg[] = $vragen[$i + 1]->id;
            }
            // vervolgvraag: de volgende vraag alleen bij dit ene antwoord
            if ($q->vervolg_bij && ! self::gekozen($q, $invoer, $q->vervolg_bij) && isset($vragen[$i + 1])) {
                $weg[] = $vragen[$i + 1]->id;
            }
        }
        return array_values(array_unique($weg));
    }

    /**
     * @return int[] id's van vragen die verborgen zijn door "volgende overslaan" of een vervolgvraag.
     * Daar valt ook een eerder getypt antwoord weg (de invuller zag de vraag niet meer); bij een stopsprong
     * niet, want daar kan de invuller met "toch alle vragen invullen" bewust doorgaan.
     */
    public static function verborgen(Poll $poll, array $invoer): array
    {
        $vragen = $poll->questions->sortBy('position')->values();
        $weg = [];
        foreach ($vragen as $i => $q) {
            if (! isset($vragen[$i + 1])) {
                continue;
            }
            if (self::gekozen($q, $invoer, $q->overslaan_bij)
                || ($q->vervolg_bij && ! self::gekozen($q, $invoer, $q->vervolg_bij))) {
                $weg[] = $vragen[$i + 1]->id;
            }
        }
        return array_values(array_unique($weg));
    }
}
