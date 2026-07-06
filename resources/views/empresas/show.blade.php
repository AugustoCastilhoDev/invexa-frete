@extends('layouts.app')
@section('title', 'Empresa: ' . $empresa->nome)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $empresa->nome }}</h4>
        <small class="text-muted">
            CNPJ: {{ $empresa->cnpj ?? '-' }}
            <span class="badge {{ $empresa->status === 'ativo' ? 'bg-success' : 'bg-secondary' }} ms-1">
                {{ ucfirst($empresa->status) }}
            </span>
        </small>
    </div>
    <div>
        <form action="{{ route('empresas.suporte.iniciar', $empresa) }}"
              method="POST" class="d-inline"
              onsubmit="return confirm('Acessar o sistema como administrador de {{ $empresa->nome }}?')">
            @csrf
            <button class="btn btn-warning">
                <i class="bi bi-headset me-1"></i> Suporte
            </button>
        </form>
        <a href="{{ route('empresas.edit', $empresa) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

{{-- Resumo operacional --}}
<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold">{{ $resumo['motoristas'] }}</div>
                <small class="text-muted">Motoristas</small>
                <div class="text-muted" style="font-size:.7rem">{{ $resumo['motoristas_ativos'] }} ativos</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold">
                    {{ $resumo['veiculos'] }}{{ $empresa->limite_veiculos ? ' / ' . $empresa->limite_veiculos : '' }}
                </div>
                <small class="text-muted">Veículos</small>
                @if($empresa->limite_veiculos)
                    <div class="text-muted" style="font-size:.7rem">
                        {{ $empresa->limiteVeiculosAtingido() ? 'limite atingido' : 'do plano' }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold">{{ $resumo['clientes'] }}</div>
                <small class="text-muted">Clientes</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold">{{ $resumo['viagens'] }}</div>
                <small class="text-muted">Viagens</small>
                <div class="text-muted" style="font-size:.7rem">{{ $resumo['viagens_abertas'] }} em aberto</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold">{{ $resumo['despesas_gerais'] }}</div>
                <small class="text-muted">Despesas Gerais</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold">{{ $usuarios->count() }}</div>
                <small class="text-muted">Usuários</small>
            </div>
        </div>
    </div>
</div>

@if($empresa->criadoPor)
<div class="alert alert-light border py-2 mb-4">
    <i class="bi bi-info-circle me-1 text-muted"></i>
    Empresa cadastrada por <strong>{{ $empresa->criadoPor->name }}</strong> em {{ $empresa->created_at->format('d/m/Y H:i') }}
</div>
@endif

{{-- Cobrança --}}
<div class="card mb-4" id="cobranca">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-credit-card me-1"></i> Cobrança
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="text-muted" style="font-size:.75rem">Plano</div>
                <div class="fw-semibold">{{ $empresa->plano ? ucfirst($empresa->plano) : '-' }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-muted" style="font-size:.75rem">Ciclo</div>
                <div class="fw-semibold">{{ $empresa->ciclo_cobranca ? ucfirst($empresa->ciclo_cobranca) : '-' }}</div>
            </div>
            <div class="col-md-3 col-6">
                @php($cobranca = $empresa->situacaoCobranca())
                <div class="text-muted" style="font-size:.75rem">Status Asaas</div>
                <div class="fw-semibold">
                    <span class="badge {{ $cobranca['classe'] }}">
                        <i class="bi {{ $cobranca['icone'] }} me-1"></i>{{ $cobranca['label'] }}
                    </span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-muted" style="font-size:.75rem">Último evento</div>
                <div class="fw-semibold">{{ $empresa->asaas_last_event_at?->format('d/m/Y H:i') ?? '-' }}</div>
            </div>
        </div>
        @if($empresa->pagamentoEmAtraso())
        <div class="alert alert-danger py-2 mt-3 mb-0">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Pagamento em atraso — o acesso <strong>não</strong> é bloqueado automaticamente.
            Se quiser suspender esta empresa, use o botão de status na listagem de empresas.
        </div>
        @endif
        @if(! $empresa->asaas_subscription_id)
        <div class="border-top pt-3 mt-3">
            <div class="alert alert-warning py-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Esta empresa ainda não tem assinatura vinculada no Asaas
                @if($empresa->plano && $empresa->plano !== 'enterprise')
                    — confira se <code>ASAAS_API_KEY</code> está configurada, ou tente criar novamente abaixo.
                @endif
            </div>
            <form action="{{ route('empresas.assinatura.criar', $empresa) }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Plano</label>
                    <select name="plano" id="plano-assinatura" class="form-select form-select-sm" required>
                        <option value="">Selecione...</option>
                        @foreach(\App\Services\Asaas\PlanoPricing::tabela() as $val => $dados)
                            <option value="{{ $val }}" {{ old('plano', $empresa->plano) === $val ? 'selected' : '' }}>
                                {{ ucfirst($val) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" id="wrapper-ciclo-assinatura">
                    <label class="form-label small fw-semibold">Ciclo</label>
                    <select name="ciclo_cobranca" class="form-select form-select-sm">
                        <option value="mensal" {{ old('ciclo_cobranca', $empresa->ciclo_cobranca ?? 'mensal') === 'mensal' ? 'selected' : '' }}>Mensal</option>
                        <option value="anual" {{ old('ciclo_cobranca', $empresa->ciclo_cobranca) === 'anual' ? 'selected' : '' }}>Anual</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-credit-card me-1"></i> Criar Assinatura
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

<script>
    (function () {
        const plano = document.getElementById('plano-assinatura');
        const wrapperCiclo = document.getElementById('wrapper-ciclo-assinatura');
        if (! plano) return;

        function atualizar() {
            wrapperCiclo.style.display = plano.value === 'enterprise' ? 'none' : '';
        }

        plano.addEventListener('change', atualizar);
        atualizar();
    })();
</script>

{{-- Usuários da empresa --}}
<div class="card">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-people me-1"></i> Usuários desta empresa
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Nome</th>
                    <th>E-mail</th>
                    <th>Papel</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $usuario->name }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>
                        <span class="badge {{ $usuario->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                            {{ $usuario->role === 'admin' ? 'Administrador' : 'Operador' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $usuario->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($usuario->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        Nenhum usuário cadastrado nesta empresa.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
