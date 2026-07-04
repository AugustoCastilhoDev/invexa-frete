<?php

namespace App\Http\Requests\Portal;

use App\Models\Motorista;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PortalLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cpf'      => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $cpfDigits = preg_replace('/\D/', '', (string) $this->input('cpf'));

        $motorista = Motorista::whereRaw(
            "REPLACE(REPLACE(cpf, '.', ''), '-', '') = ?",
            [$cpfDigits]
        )->first();

        if (! $motorista || ! $motorista->password || ! Hash::check($this->input('password'), $motorista->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'cpf' => 'CPF ou senha inválidos.',
            ]);
        }

        if (! $motorista->hasPortalAtivo()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'cpf' => 'Seu acesso ao portal está desativado. Fale com a transportadora.',
            ]);
        }

        if ($motorista->empresa?->status !== 'ativo') {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'cpf' => 'O acesso da transportadora está temporariamente suspenso.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        Auth::guard('motorista')->login($motorista, $this->boolean('remember'));
        $this->session()->regenerate();
    }

    /**
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
            'cpf' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('cpf')).'|'.$this->ip());
    }
}
