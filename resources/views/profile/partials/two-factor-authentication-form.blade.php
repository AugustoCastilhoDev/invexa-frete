<div>
    <h5 class="mb-1">Autenticação em Dois Fatores (2FA)</h5>
    <p class="text-muted small mb-4">
        Adicione uma camada extra de segurança à sua conta exigindo um código do seu
        aplicativo autenticador (Google Authenticator, Authy etc.) a cada login.
    </p>

    @if ($user->hasEnabledTwoFactorAuthentication())
        {{-- Ativado --}}
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="bi bi-shield-check fs-5"></i>
            <div>2FA está <strong>ativado</strong> na sua conta.</div>
        </div>

        @if (session('status') === 'two-factor-recovery-codes-regenerated')
            <div class="alert alert-warning">
                <strong>Guarde estes novos códigos de recuperação</strong> — os antigos deixaram de funcionar.
                <ul class="mb-0 mt-2 font-monospace">
                    @foreach ($user->two_factor_recovery_codes as $code)
                        <li>{{ $code }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <p class="small text-muted">
                Você tem <strong>{{ count($user->two_factor_recovery_codes ?? []) }}</strong> código(s) de recuperação restante(s).
                Use-os se perder acesso ao aplicativo autenticador.
            </p>
        @endif

        <div class="d-flex gap-2">
            <form action="{{ route('two-factor.recovery-codes') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Gerar novos códigos de recuperação
                </button>
            </form>

            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmTwoFactorDisable">
                <i class="bi bi-shield-slash me-1"></i> Desativar 2FA
            </button>
        </div>

        <div class="modal fade" id="confirmTwoFactorDisable" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="{{ route('two-factor.disable') }}">
                        @csrf
                        @method('delete')
                        <div class="modal-body p-4">
                            <h5 class="mb-2">Desativar autenticação em dois fatores?</h5>
                            <p class="text-muted small">Digite sua senha para confirmar.</p>
                            <label for="password_2fa" class="visually-hidden">Senha</label>
                            <input id="password_2fa" name="password" type="password"
                                   class="form-control @error('password', 'twoFactorDisable') is-invalid @enderror"
                                   placeholder="Senha">
                            @error('password', 'twoFactorDisable')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Desativar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($errors->twoFactorDisable->isNotEmpty())
            @push('scripts')
            <script>
                new bootstrap.Modal(document.getElementById('confirmTwoFactorDisable')).show();
            </script>
            @endpush
        @endif

    @elseif ($user->two_factor_secret)
        {{-- Configuração pendente de confirmação --}}
        <div class="alert alert-warning">
            Escaneie o QR Code abaixo no seu aplicativo autenticador e digite o código gerado para confirmar.
        </div>

        <div class="mb-3" style="max-width:220px">
            {!! $qrCodeSvg !!}
        </div>

        <form action="{{ route('two-factor.confirm') }}" method="POST" class="row g-2 align-items-start">
            @csrf
            <div class="col-auto">
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                       placeholder="Código de 6 dígitos" inputmode="numeric" autocomplete="one-time-code" autofocus>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Confirmar
                </button>
            </div>
        </form>

        <form action="{{ route('two-factor.enable') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-link btn-sm text-muted p-0">Gerar um novo QR Code</button>
        </form>

    @else
        {{-- Desativado --}}
        <form action="{{ route('two-factor.enable') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-shield-lock me-1"></i> Ativar 2FA
            </button>
        </form>
    @endif
</div>
