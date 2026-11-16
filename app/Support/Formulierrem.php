<?php

namespace App\Support;

use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Spamrem voor het stemformulier, volgens de vaste regel "elk formulier krijgt meteen de
 * volledige spamrem" (skill elementa-server, sites-formulieren.md, 25 okt 2026). Overgenomen
 * van nieuw.martinadema.nl (formhulp.php), met twee bewuste afwijkingen voor een lange vragenlijst:
 *  - ander IP-adres dan bij het openen wordt alleen gelogd, niet geweigerd: wie 36 vragen op de
 *    telefoon invult, wisselt makkelijk van wifi naar 4G.
 *  - de rem per IP blijft 15 per dag (anoniem) in plaats van 5 per uur: drie mensen per huis
 *    mogen meedoen, en een gezin deelt één aansluiting.
 *
 * Elke weigering komt als regel "polls formulier: ..." in de php-log, die /root/check-formulieren.sh leest.
 */
class Formulierrem
{
    /** Inzendingen per uur over alle bezoekers samen; daarboven stil weg (en een mail via check-formulieren). */
    public const PLAFOND_PER_UUR = 120;

    /** Minimale tijd tussen openen en versturen, en hoe lang een geopende pagina geldig blijft. */
    private const MIN_SECONDEN = 3;
    private const MAX_SECONDEN = 7200;

    private static ?string $sleutel = null;

    private static function sleutel(): string
    {
        if (self::$sleutel !== null) {
            return self::$sleutel;
        }
        $pad = storage_path('app/formrem-sleutel.bin');
        if (is_readable($pad)) {
            $s = (string) file_get_contents($pad);
            if (strlen($s) >= 32) {
                return self::$sleutel = $s;
            }
        }
        $s = random_bytes(32);
        @file_put_contents($pad, $s, LOCK_EX);
        @chmod($pad, 0600);
        return self::$sleutel = $s;
    }

    /** Onherkenbaar kenmerk van het IP-adres; bij IPv6 alleen het /64-netwerk. */
    private static function ipKenmerk(string $ip): string
    {
        $b = @inet_pton($ip);
        if ($b === false) {
            return 'onbekend';
        }
        if (strlen($b) === 16) {
            $b = substr($b, 0, 8);
        }
        return substr(hash_hmac('sha256', $b, self::sleutel()), 0, 8);
    }

    /** Waarde voor het verborgen veld "geopend": tijd.ipkenmerk.handtekening. */
    public static function kaartje(string $ip): string
    {
        $t = time();
        $ipk = self::ipKenmerk($ip);
        return $t . '.' . $ipk . '.' . substr(hash_hmac('sha256', $t . '.' . $ipk, self::sleutel()), 0, 16);
    }

    /** @return array{tijd:int, zelfde_ip:bool, sig:string}|null */
    private static function leesKaartje(string $tok, string $ip): ?array
    {
        $d = explode('.', $tok);
        if (count($d) !== 3 || ! ctype_digit($d[0])) {
            return null;
        }
        [$t, $ipk, $sig] = $d;
        if (! hash_equals(substr(hash_hmac('sha256', $t . '.' . $ipk, self::sleutel()), 0, 16), $sig)) {
            return null;
        }
        return ['tijd' => (int) $t, 'zelfde_ip' => hash_equals($ipk, self::ipKenmerk($ip)), 'sig' => $sig];
    }

    public static function robotUa(string $ua): bool
    {
        $ua = trim($ua);
        if ($ua === '' || $ua === '-' || $ua[0] === '"' || $ua[0] === "'") {
            return true;
        }
        return (bool) preg_match(
            '~HeadlessChrome|Headless|PhantomJS|SlimerJS|Selenium|WebDriver|Puppeteer|Playwright|Cypress|Nightmare'
            . '|Electron/|obsidian/'
            . '|python|aiohttp|httpx|scrapy|mechanize|curl/|wget|libwww|lwp-|Go-http-client|okhttp|Java/'
            . '|Apache-HttpClient|node-fetch|undici|axios|got \(|Postman|insomnia|HTTrack|zgrab|masscan'
            . '~i',
            $ua
        );
    }

    private static function nederlands(string $tekst): bool
    {
        static $woorden = null;
        if ($woorden === null) {
            $woorden = array_flip(explode(' ',
                'de het een ik je jij u uw wij we mij mijn onze ons en dat dit deze te voor niet van in op met er is '
              . 'zijn bent heb hebt heeft hebben maar om ook als bij nog kan kunt kunnen wil wilt willen moet moeten '
              . 'graag hoe wat wanneer waar waarom welke veel weinig meer minder goed beter slecht mooi leuk gezellig '
              . 'centrum dorp ermelo winkel winkels winkelen straat stationsstraat parkeren parkeerplaats fiets fietsen '
              . 'auto bus markt terras horeca groen bomen bloemen kinderen speelplek leegstand panden verkeer '
              . 'vrachtverkeer veilig overlast jongeren bankjes toiletten mis mist missen geen wel nu dag zou worden wordt '
              . 'gaat komt kom komen doen te vaak altijd soms nooit erg echt jammer prima prettig'
            ));
        }
        $schoon = mb_strtolower(strtr($tekst, ['ë' => 'e', 'é' => 'e', 'è' => 'e', 'ï' => 'i', "'" => '', '’' => '']));
        foreach (preg_split('~[^a-z]+~', $schoon, -1, PREG_SPLIT_NO_EMPTY) as $w) {
            if (isset($woorden[$w])) {
                return true;
            }
        }
        return false;
    }

    private static function handelsfrase(string $tekst): bool
    {
        return (bool) preg_match(
            '~product range|range of products|latest catalog|your catalog|product catalog|price ?list|best price'
            . '|interested in your products?|zoom or (microsoft )?teams|(short|quick|brief) (meeting|call)'
            . '|suitable time|purchase order|bulk order|minimum order|\bMOQ\b|request for quot|\bRFQ\b'
            . '|send (us )?(a |your )?quotation|business proposal|we are a (leading|professional)'
            . '|\bcasino\b|\bviagra\b|\bcrypto\b|\bSEO services\b~i',
            $tekst
        );
    }

    /** StopForumSpam op IP (en e-mail als die er is). Faalt open: geen antwoord = doorlaten. */
    private static function sfsSpam(string $ip, string $email = ''): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        $url = 'https://api.stopforumspam.org/api?json&ip=' . urlencode($ip)
            . ($email !== '' && str_contains($email, '@') ? '&email=' . urlencode($email) : '');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_USERAGENT => 'polls-elementa-formulier',
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        $d = is_string($res) ? json_decode($res, true) : null;
        if (! is_array($d) || empty($d['success'])) {
            return false;
        }
        $ipHit = ! empty($d['ip']['appears']) && (int) ($d['ip']['frequency'] ?? 0) >= 5;
        $mailHit = ! empty($d['email']['appears']);
        return $ipHit || $mailHit;
    }

    public static function log(string $soort, string $ip, string $reden = ''): void
    {
        $regel = match ($soort) {
            'rem' => "polls formulier: rem, vanaf {$ip}",
            'plafond' => "polls formulier: plafond, vanaf {$ip}",
            'let op' => "polls formulier: let op vanaf {$ip}: {$reden}",
            default => "polls formulier: geweigerd vanaf {$ip}: {$reden}",
        };
        error_log($regel);
    }

    /**
     * Controleert een inzending. Geeft null (doorlaten), 'verlopen' (zichtbaar opnieuw laten versturen,
     * antwoorden blijven staan) of 'stil' (doen alsof het gelukt is, niets opslaan).
     */
    public static function controleer(Request $request, Poll $poll): ?string
    {
        $ip = (string) $request->ip();

        // 1. lokveld: mensen zien het niet en laten het leeg
        if (trim((string) $request->input('website', '')) !== '') {
            self::log('geweigerd', $ip, 'lokveld');
            return 'stil';
        }

        // 2. script of geautomatiseerde browser
        if (self::robotUa((string) $request->userAgent())) {
            self::log('geweigerd', $ip, 'robotbrowser');
            return 'stil';
        }

        // 3. ondertekend kaartje
        $tok = (string) $request->input('geopend', '');
        if ($tok === '') {
            self::log('geweigerd', $ip, 'geen-kaartje');
            return 'verlopen';
        }
        $k = self::leesKaartje($tok, $ip);
        if ($k === null) {
            self::log('geweigerd', $ip, 'vervalst-kaartje');
            return 'stil';
        }
        $leeftijd = time() - $k['tijd'];
        if ($leeftijd < self::MIN_SECONDEN) {
            self::log('geweigerd', $ip, 'te-snel');
            return 'stil';
        }
        if ($leeftijd > self::MAX_SECONDEN) {
            self::log('geweigerd', $ip, 'verlopen');
            return 'verlopen';
        }
        if (! $k['zelfde_ip']) {
            self::log('let op', $ip, 'ander-ip (doorgelaten)');
        }

        // 5. korte velden zonder webadres
        foreach (['respondent_name', 'phone'] as $veld) {
            if (preg_match('~https?:|://|www\.~i', (string) $request->input($veld, ''))) {
                self::log('geweigerd', $ip, 'webadres-in-kort-veld');
                return 'stil';
            }
        }

        // 6. vrije tekst: open antwoorden en toelichtingen samen
        $vrij = collect($request->all())
            ->filter(fn ($v, $k) => is_string($v) && (str_starts_with($k, 'toelichting_')
                || ($poll->questions->firstWhere('id', (int) substr($k, 9))?->type === PollType::OPEN_TEXT && str_starts_with($k, 'question_'))))
            ->implode("\n");
        if ($vrij !== '') {
            if (preg_match_all('~https?://|www\.~i', $vrij) >= 3) {
                self::log('geweigerd', $ip, 'links');
                return 'stil';
            }
            if (self::handelsfrase($vrij)) {
                self::log('geweigerd', $ip, 'handelsfrase');
                return 'stil';
            }
            if (str_word_count(preg_replace('~[^\pL\s]~u', ' ', $vrij)) >= 6 && ! self::nederlands($vrij)) {
                self::log('geweigerd', $ip, 'geen-nederlands');
                return 'stil';
            }
        }

        // 7. bekende spammer
        if (self::sfsSpam($ip, (string) $request->input('email', ''))) {
            self::log('geweigerd', $ip, 'stopforumspam');
            return 'stil';
        }

        // 8. plafond over alle bezoekers samen
        if (RateLimiter::tooManyAttempts('polls-plafond:' . $poll->id, self::PLAFOND_PER_UUR)) {
            self::log('plafond', $ip);
            return 'stil';
        }

        // 4. elk kaartje hooguit één keer, als laatste vlak vóór het opslaan
        if (! Cache::add('formkaartje:' . $k['sig'], 1, self::MAX_SECONDEN + 3600)) {
            self::log('geweigerd', $ip, 'kaartje-hergebruikt');
            return 'stil';
        }

        return null;
    }

    /** Na een geslaagde inzending: telt mee voor het uurplafond. */
    public static function geteld(Poll $poll): void
    {
        RateLimiter::hit('polls-plafond:' . $poll->id, 3600);
    }
}
