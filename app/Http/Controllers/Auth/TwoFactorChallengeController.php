<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $userId = $request->session()->get('login.id');

        if (! $userId) {
            return redirect()->route('login');
        }

        // Código de 6 dígitos: sem limite de tentativas seria força-bruta trivial
        // (só 1 milhão de combinações). Mesma janela usada no login por senha.
        $throttleKey = 'two-factor|' . $userId;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            event(new Lockout($request));

            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'code' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $user = User::findOrFail($userId);

        if ($request->filled('recovery_code')) {
            $valido = $this->consumirCodigoRecuperacao($user, $request->string('recovery_code'));
        } elseif ($request->filled('code')) {
            $valido = (new Google2FA())->verifyKey($user->two_factor_secret, $request->string('code'));
        } else {
            $valido = false;
        }

        if (! $valido) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'code' => 'Código inválido.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, (bool) $request->session()->pull('login.remember', false));

        $request->session()->forget('login.id');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function consumirCodigoRecuperacao(User $user, string $codigo): bool
    {
        $codigos = $user->two_factor_recovery_codes ?? [];
        $codigo = Str::upper($codigo);

        if (! in_array($codigo, $codigos, true)) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => array_values(array_diff($codigos, [$codigo])),
        ])->save();

        return true;
    }
}
