<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-lg font-semibold text-gray-800">Portal do Motorista</h1>
        <p class="text-sm text-gray-500 mt-1">Acompanhe suas viagens e acertos.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('portal.login') }}">
        @csrf

        <div>
            <x-input-label for="cpf" value="CPF" />
            <x-text-input id="cpf" class="block mt-1 w-full" type="text" name="cpf"
                          :value="old('cpf')" required autofocus placeholder="000.000.000-00" />
            <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Senha" />

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

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Lembrar-me</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Entrar
            </x-primary-button>
        </div>
    </form>

    <script>
        document.getElementById('cpf').addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = v;
        });
    </script>
</x-guest-layout>
