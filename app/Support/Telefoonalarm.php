<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Trilalarm op de telefoon van IJme via de eigen meldserver ntfy.elementa.com (app "ntfy").
 * Prioriteit 5 = dringend: lang trillen en een melding bovenin, ook als het scherm uit is.
 * Instellingen in .env (NTFY_SERVER, NTFY_TOPIC, NTFY_TOKEN); ontbreken ze, dan gebeurt er niets.
 * Faalt stil (alleen een logregel): een alarm mag het opslaan van antwoorden nooit tegenhouden.
 */
class Telefoonalarm
{
    public static function stuur(string $titel, string $tekst, int $prioriteit = 5): bool
    {
        $server = rtrim((string) config('services.ntfy.server'), '/');
        $kanaal = (string) config('services.ntfy.topic');
        $token = (string) config('services.ntfy.token');
        if ($server === '' || $kanaal === '' || $token === '') {
            return false;
        }
        try {
            $antwoord = Http::timeout(5)->withToken($token)->post($server, [
                'topic' => $kanaal,
                'title' => $titel,
                'message' => $tekst,
                'priority' => $prioriteit,
                'tags' => ['rotating_light'],
            ]);
            if (! $antwoord->successful()) {
                \Log::error('Telefoonalarm niet aangenomen: HTTP ' . $antwoord->status());
                return false;
            }
            return true;
        } catch (Throwable $e) {
            \Log::error('Telefoonalarm mislukt: ' . $e->getMessage());
            return false;
        }
    }
}
