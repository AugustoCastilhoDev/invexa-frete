<div>
    <h5 class="mb-1 text-danger">Excluir Conta</h5>
    <p class="text-muted small mb-3">
        Ao excluir sua conta, seu acesso ao sistema é encerrado. Os registros que você criou
        permanecem no histórico para fins de auditoria.
    </p>

    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
        <i class="bi bi-person-x me-1"></i> Excluir Conta
    </button>

    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    <div class="modal-body p-4">
                        <h5 class="mb-2">Tem certeza que deseja excluir sua conta?</h5>
                        <p class="text-muted small">
                            Digite sua senha para confirmar a exclusão da sua conta.
                        </p>

                        <label for="password" class="visually-hidden">Senha</label>
                        <input id="password" name="password" type="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Senha">
                        @error('password', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir Conta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    @push('scripts')
    <script>
        new bootstrap.Modal(document.getElementById('confirmUserDeletion')).show();
    </script>
    @endpush
@endif
