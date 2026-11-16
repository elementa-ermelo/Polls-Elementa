<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Inloggen met wachtwoord, daarna een inlogcode per e-mail (2FA), naar het model van werklijsten
 * (7 aug 2026): geen half-ingelogde toestand, de code staat gehasht in de sessie, 10 minuten geldig,
 * hooguit 5 pogingen en 3 keer opnieuw sturen. "Vertrouw dit apparaat" geeft 10 dagen geen code.
 * Accounts zonder e-mailadres (alleen een gebruikersnaam) hebben two_factor_enabled = 0.
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/admin/polls';

    private const CODE_MINUTEN = 10;
    private const VERTROUWD_DAGEN = 10;
    private const COOKIE = 'polls_td';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $user = User::query()->where('email', $request->input('email'))->first();
        if (! $user || ! Hash::check((string) $request->input('password'), $user->password)) {
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request);
        }
        $this->clearLoginAttempts($request);

        if ($user->two_factor_enabled && str_contains((string) $user->email, '@') && ! $this->vertrouwdApparaat($request, $user)) {
            $this->stuurCode($request, $user, $request->boolean('remember'));
            return redirect()->route('login.code');
        }

        return $this->rondAf($request, $user, $request->boolean('remember'));
    }

    public function codeScherm(Request $request): View|RedirectResponse
    {
        $p = $request->session()->get('2fa');
        if (! $p) {
            return redirect()->route('login');
        }
        return view('auth.verify-code', ['adres' => $this->verhul((string) User::find($p['user'])?->email)]);
    }

    public function codeControle(Request $request): RedirectResponse
    {
        $p = $request->session()->get('2fa');
        if (! $p) {
            return redirect()->route('login');
        }
        if (time() > $p['tot']) {
            $request->session()->forget('2fa');
            return redirect()->route('login')->withErrors(['email' => 'De code is verlopen. Log opnieuw in.']);
        }
        if ($p['pogingen'] >= 5) {
            $request->session()->forget('2fa');
            return redirect()->route('login')->withErrors(['email' => 'Te vaak een verkeerde code. Log opnieuw in.']);
        }
        $code = preg_replace('~\D~', '', (string) $request->input('code'));
        if (! hash_equals($p['hash'], $this->hashCode($code))) {
            $p['pogingen']++;
            $request->session()->put('2fa', $p);
            return back()->withErrors(['code' => 'Deze code klopt niet.']);
        }

        $user = User::find($p['user']);
        $request->session()->forget('2fa');
        if (! $user) {
            return redirect()->route('login');
        }
        if ($request->boolean('vertrouw')) {
            $token = Str::random(48);
            TrustedDevice::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $token),
                'ua' => mb_substr((string) $request->userAgent(), 0, 255),
                'ip' => $request->ip(),
                'expires_at' => now()->addDays(self::VERTROUWD_DAGEN),
            ]);
            Cookie::queue(Cookie::make(self::COOKIE, $token, 60 * 24 * self::VERTROUWD_DAGEN, '/', null, true, true, false, 'Lax'));
        }
        return $this->rondAf($request, $user, (bool) $p['remember']);
    }

    public function codeOpnieuw(Request $request): RedirectResponse
    {
        $p = $request->session()->get('2fa');
        if (! $p) {
            return redirect()->route('login');
        }
        if ($p['opnieuw'] >= 3 || time() - $p['laatst'] < 30) {
            return back()->withErrors(['code' => 'Wacht even voordat u een nieuwe code vraagt.']);
        }
        $user = User::find($p['user']);
        if (! $user) {
            return redirect()->route('login');
        }
        $this->stuurCode($request, $user, (bool) $p['remember'], $p['opnieuw'] + 1);
        return back()->with('status', 'Er is een nieuwe code verstuurd.');
    }

    private function stuurCode(Request $request, User $user, bool $remember, int $opnieuw = 0): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $request->session()->put('2fa', [
            'user' => $user->id,
            'hash' => $this->hashCode($code),
            'tot' => time() + 60 * self::CODE_MINUTEN,
            'pogingen' => 0,
            'opnieuw' => $opnieuw,
            'laatst' => time(),
            'remember' => $remember,
        ]);
        $tekst = "Uw inlogcode voor Polls Elementa is:\n\n    {$code}\n\n"
            . 'De code is ' . self::CODE_MINUTEN . " minuten geldig.\n\n"
            . "Heeft u niet geprobeerd in te loggen? Dan kunt u deze mail negeren. Wie de code niet heeft, komt niet binnen.\n";
        try {
            Mail::raw($tekst, fn ($m) => $m->to($user->email)->subject('Uw inlogcode voor Polls Elementa'));
        } catch (\Throwable $e) {
            \Log::error('Inlogcode niet verstuurd: ' . $e->getMessage(), ['user_id' => $user->id]);
        }
    }

    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    private function vertrouwdApparaat(Request $request, User $user): bool
    {
        $token = $request->cookie(self::COOKIE);
        if (! is_string($token) || strlen($token) < 20) {
            return false;
        }
        return TrustedDevice::query()
            ->where('user_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->exists();
    }

    private function rondAf(Request $request, User $user, bool $remember): RedirectResponse
    {
        Auth::login($user, $remember);
        $request->session()->regenerate();
        return redirect()->intended($this->redirectPath());
    }

    private function verhul(string $adres): string
    {
        if (! str_contains($adres, '@')) {
            return '';
        }
        [$naam, $domein] = explode('@', $adres, 2);
        return mb_substr($naam, 0, 2) . str_repeat('*', max(1, mb_strlen($naam) - 2)) . '@' . $domein;
    }
}
