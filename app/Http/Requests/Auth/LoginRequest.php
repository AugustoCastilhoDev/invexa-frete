<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * Não usa Auth::attempt() diretamente porque, quando o usuário tem 2FA
     * ativado, o login só deve ser efetivado depois de validar o segundo
     * fator (ver requiresTwoFactorChallenge()).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::validate($this->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::getProvider()->retrieveByCredentials($this->only('email', 'password'));

        if ($user->status !== 'ativo') {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Este usuário está inativo. Contate um administrador.',
            ]);
        }

        if ($user->empresa_id && $user->empresa?->status !== 'ativo') {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'O acesso da sua empresa está temporariamente suspenso. Contate o suporte.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $this->session()->put('login.id', $user->id);
            $this->session()->put('login.remember', $this->boolean('remember'));

            return;
        }

        Auth::login($user, $this->boolean('remember'));
    }

    /**
     * Indica se o login ficou pendente de segundo fator (2FA).
     */
    public function requiresTwoFactorChallenge(): bool
    {
        return $this->session()->has('login.id');
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
