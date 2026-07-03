<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return back();
        }

        $google2fa = new Google2FA();

        $user->forceFill([
            'two_factor_secret' => $google2fa->generateSecretKey(),
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'two-factor-authentication-setup-started');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            return back()->withErrors(['code' => 'Nenhuma configuração de 2FA pendente. Inicie a ativação novamente.']);
        }

        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Código inválido. Confira o app autenticador e tente novamente.']);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $this->gerarCodigosRecuperacao(),
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'two-factor-authentication-confirmed');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('twoFactorDisable', [
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'two-factor-authentication-disabled');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return back();
        }

        $user->forceFill([
            'two_factor_recovery_codes' => $this->gerarCodigosRecuperacao(),
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'two-factor-recovery-codes-regenerated');
    }

    private function gerarCodigosRecuperacao(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5) . '-' . Str::random(5)))
            ->all();
    }
}
