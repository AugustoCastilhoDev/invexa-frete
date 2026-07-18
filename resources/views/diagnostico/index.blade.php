@extends('layouts.app')
@section('title', 'Diagnóstico do Sistema')

@php
    // Closures em vez de "function nome(...)": este bloco roda a cada render da
    // view, e uma função nomeada aqui quebraria com "Cannot redeclare" na segunda
    // vez que a view for renderizada no mesmo processo (ex.: testes).
    $formatarBytes = function (?int $bytes): string {
        if ($bytes === null) {
            return '—';
        }
        if ($bytes <= 0) {
            return '0 B';
        }
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($unidades) - 1);

        return number_format($bytes / (1024 ** $i), $i === 0 ? 0 : 1, ',', '.') . ' ' . $unidades[$i];
    };

    $formatarDuracao = function (?int $segundos): string {
        if ($segundos === null) {
            return '—';
        }
        $dias = intdiv($segundos, 86400);
        $horas = intdiv($segundos % 86400, 3600);

        return $dias > 0 ? "{$dias}d {$horas}h" : "{$horas}h";
    };
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Diagnóstico do Sistema</h4>
        <small class="text-muted">Saúde do servidor e volume de dados da plataforma</small>
    </div>
    <a href="{{ route('diagnostico.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-clockwise me-1"></i> Atualizar
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-start border-primary border-3 card-accent-blue">
            <div class="card-body">
                <div class="text-muted small">Uptime do servidor</div>
                <div class="fs-4 fw-semibold">{{ $formatarDuracao($servidor['uptime_segundos']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-warning border-3 card-accent-orange">
            <div class="card-body">
                <div class="text-muted small">Carga (1 / 5 / 15 min)</div>
                <div class="fs-4 fw-semibold">
                    @if($servidor['load'])
                        {{ implode(' / ', array_map(fn($l) => number_format($l, 2, ',', '.'), $servidor['load'])) }}
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        @php
            $memUsada = ($servidor['mem_total'] !== null && $servidor['mem_disponivel'] !== null)
                ? $servidor['mem_total'] - $servidor['mem_disponivel']
                : null;
        @endphp
        <div class="card h-100 border-start border-success border-3 card-accent-green">
            <div class="card-body">
                <div class="text-muted small">Memória usada</div>
                <div class="fs-4 fw-semibold">
                    {{ $servidor['mem_usada_percentual'] !== null ? number_format($servidor['mem_usada_percentual'], 1, ',', '.') . '%' : '—' }}
                </div>
                <small class="text-muted">{{ $formatarBytes($memUsada) }} de {{ $formatarBytes($servidor['mem_total']) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-3 card-accent-purple" style="border-color:#8b5cf6!important">
            <div class="card-body">
                <div class="text-muted small">Disco usado</div>
                <div class="fs-4 fw-semibold">
                    @php
                        $discoUsado = ($servidor['disco_total'] && $servidor['disco_livre'])
                            ? $servidor['disco_total'] - $servidor['disco_livre']
                            : null;
                        $discoPercentual = ($discoUsado !== null && $servidor['disco_total'])
                            ? round($discoUsado / $servidor['disco_total'] * 100, 1)
                            : null;
                    @endphp
                    {{ $discoPercentual !== null ? number_format($discoPercentual, 1, ',', '.') . '%' : '—' }}
                </div>
                <small class="text-muted">{{ $formatarBytes($discoUsado) }} de {{ $formatarBytes($servidor['disco_total']) }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-start border-primary border-3">
            <div class="card-body">
                <div class="text-muted small">Usuários online agora</div>
                <div class="fs-4 fw-semibold">{{ $aplicacao['usuarios_online'] }}</div>
                <small class="text-muted">últimos 15 min</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-secondary border-3">
            <div class="card-body">
                <div class="text-muted small">Ativos nas últimas 24h</div>
                <div class="fs-4 fw-semibold">{{ $aplicacao['usuarios_ativos_24h'] }}</div>
                <small class="text-muted">de {{ $aplicacao['usuarios_total'] }} usuário(s) cadastrado(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-success border-3">
            <div class="card-body">
                <div class="text-muted small">Empresas ativas</div>
                <div class="fs-4 fw-semibold">{{ $aplicacao['empresas_ativas'] }}</div>
                <small class="text-muted">de {{ $aplicacao['empresas_total'] }} cadastrada(s)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-start border-info border-3">
            <div class="card-body">
                <div class="text-muted small">Tamanho do banco</div>
                <div class="fs-4 fw-semibold">{{ $formatarBytes($aplicacao['tamanho_banco_bytes']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-start border-secondary border-3 mb-4">
    <div class="card-header bg-transparent">
        <i class="bi bi-database me-1"></i> Volume de registros (todas as empresas)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Cadastro</th>
                        <th class="text-end pe-4">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-4">Veículos</td>
                        <td class="text-end pe-4">{{ number_format($aplicacao['veiculos_total'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Motoristas</td>
                        <td class="text-end pe-4">{{ number_format($aplicacao['motoristas_total'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Clientes</td>
                        <td class="text-end pe-4">{{ number_format($aplicacao['clientes_total'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Viagens</td>
                        <td class="text-end pe-4">{{ number_format($aplicacao['viagens_total'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-start border-secondary border-3">
    <div class="card-body">
        <div class="row g-3 small text-muted">
            <div class="col-md-4">PHP {{ $servidor['php_version'] }}</div>
            <div class="col-md-4">Laravel {{ $servidor['laravel_version'] }}</div>
            <div class="col-md-4">Ambiente: {{ $servidor['ambiente'] }}</div>
        </div>
    </div>
</div>
@endsection
