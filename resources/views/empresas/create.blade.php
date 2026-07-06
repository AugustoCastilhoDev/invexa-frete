@extends('layouts.app')
@section('title', 'Nova Empresa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Nova Empresa</h4>
        <small class="text-muted">Cadastre a empresa e o administrador inicial dela</small>
    </div>
    <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('empresas.store') }}" method="POST">
            @csrf
            <h6 class="fw-semibold mb-3">Dados da empresa</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                           value="{{ old('nome') }}" required>
                    @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CNPJ</label>
                    <input type="text" name="cnpj" class="form-control @error('cnpj') is-invalid @enderror"
                           value="{{ old('cnpj') }}">
                    @error('cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Plano *</label>
                    <select name="plano" id="plano" class="form-select @error('plano') is-invalid @enderror" required>
                        <option value="">Selecione...</option>
                        @foreach(\App\Services\Asaas\PlanoPricing::tabela() as $val => $dados)
                            <option value="{{ $val }}"
                                data-limite="{{ $dados['limite_veiculos'] }}"
                                {{ old('plano') === $val ? 'selected' : '' }}>
                                {{ ucfirst($val) }}
                                @if($dados['mensal'])
                                    — R$ {{ number_format($dados['mensal'], 2, ',', '.') }}/mês
                                @else
                                    — sob consulta
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('plano')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6" id="wrapper-ciclo-cobranca">
                    <label class="form-label fw-semibold">Ciclo de Cobrança *</label>
                    <select name="ciclo_cobranca" class="form-select @error('ciclo_cobranca') is-invalid @enderror">
                        <option value="mensal" {{ old('ciclo_cobranca', 'mensal') === 'mensal' ? 'selected' : '' }}>Mensal</option>
                        <option value="anual" {{ old('ciclo_cobranca') === 'anual' ? 'selected' : '' }}>Anual (pague 10, leve 12)</option>
                    </select>
                    @error('ciclo_cobranca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Limite de Veículos</label>
                    <input type="number" name="limite_veiculos" id="limite_veiculos" min="1"
                           class="form-control @error('limite_veiculos') is-invalid @enderror"
                           value="{{ old('limite_veiculos') }}" placeholder="Deixe em branco para ilimitado">
                    @error('limite_veiculos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Preenchido automaticamente pelo plano — pode ajustar se for um limite negociado à parte.</div>
                </div>
            </div>

            <div class="alert alert-info py-2 mb-4" id="aviso-asaas" style="display:none">
                <i class="bi bi-info-circle me-1"></i>
                Ao salvar, uma assinatura recorrente é criada automaticamente no Asaas, com 14 dias de trial antes da primeira cobrança.
            </div>

            <h6 class="fw-semibold mb-3">Administrador inicial</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror"
                           value="{{ old('admin_name') }}" required>
                    @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">E-mail *</label>
                    <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror"
                           value="{{ old('admin_email') }}" required>
                    @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Senha *</label>
                    <input type="password" name="admin_password"
                           class="form-control @error('admin_password') is-invalid @enderror" required>
                    @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirmar senha *</label>
                    <input type="password" name="admin_password_confirmation" class="form-control" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Salvar Empresa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const plano = document.getElementById('plano');
        const limite = document.getElementById('limite_veiculos');
        const wrapperCiclo = document.getElementById('wrapper-ciclo-cobranca');
        const avisoAsaas = document.getElementById('aviso-asaas');

        function atualizar() {
            const selected = plano.options[plano.selectedIndex];
            const isEnterprise = plano.value === 'enterprise';

            if (plano.value && selected.dataset.limite) {
                limite.value = selected.dataset.limite;
            }

            wrapperCiclo.style.display = isEnterprise ? 'none' : '';
            avisoAsaas.style.display = plano.value && ! isEnterprise ? '' : 'none';
        }

        plano.addEventListener('change', atualizar);
        atualizar();
    })();
</script>
@endsection
