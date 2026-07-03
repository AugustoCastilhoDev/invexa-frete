<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Confirme o acesso digitando o código de 6 dígitos do seu aplicativo autenticador,
        ou use um dos seus códigos de recuperação.
    </div>

    <form method="POST" action="{{ route('two-factor.login') }}" x-data="{ recovery: false }">
        @csrf

        <div x-show="! recovery">
            <x-input-label for="code" value="Código de verificação" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" inputmode="numeric"
                          name="code" autofocus autocomplete="one-time-code" x-bind:disabled="recovery" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div x-show="recovery" style="display: none;">
            <x-input-label for="recovery_code" value="Código de recuperação" />
            <x-text-input id="recovery_code" class="block mt-1 w-full" type="text"
                          name="recovery_code" autocomplete="one-time-code" x-bind:disabled="! recovery" />
        </div>

        <div class="flex items-center justify-end mt-4 gap-3">
            <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900"
                    x-on:click="recovery = ! recovery">
                <span x-show="! recovery">Usar um código de recuperação</span>
                <span x-show="recovery" style="display: none;">Usar o app autenticador</span>
            </button>

            <x-primary-button>
                Confirmar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
