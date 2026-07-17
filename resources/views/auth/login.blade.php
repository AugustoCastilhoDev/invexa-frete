<x-guest-layout>
    <div class="flex mb-6 rounded-lg bg-gray-100 p-1 text-sm font-medium">
        <span class="flex-1 text-center py-1.5 rounded-md bg-white text-orange-600 shadow-sm">
            Operador / Admin
        </span>
        <a href="{{ route('portal.login') }}"
           class="flex-1 text-center py-1.5 rounded-md text-gray-500 hover:text-gray-700 no-underline">
            Portal do Motorista
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-lg font-semibold text-gray-800">Bem-vindo de volta</h1>
        <p class="text-sm text-gray-500 mt-1">Entre com suas credenciais para acessar o sistema.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1" x-data="{ mostrarSenha: false }">
                <x-text-input id="password" class="block w-full pr-10"
                                type="password"
                                x-bind:type="mostrarSenha ? 'text' : 'password'"
                                name="password"
                                required autocomplete="current-password" />
                <button type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-orange-600"
                        x-on:click="mostrarSenha = ! mostrarSenha"
                        :aria-label="mostrarSenha ? 'Esconder senha' : 'Mostrar senha'">
                    <i class="bi" x-bind:class="mostrarSenha ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-orange-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
