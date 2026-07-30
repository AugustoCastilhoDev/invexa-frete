@extends('layouts.app')
@section('title', 'Exportar Dados')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Exportar Dados</h4>
    <small class="text-muted">Todos os dados da sua empresa, em CSV — sem depender de nós pra sair do sistema</small>
</div>

<div class="card">
    <div class="card-body">
        <p class="mb-3">
            O arquivo <code>.zip</code> gerado inclui um CSV por cadastro: clientes, motoristas, veículos,
            viagens, lançamentos, descontos, documentos, emissões fiscais, manutenções, despesas gerais,
            tabelas de frete por rota e programações de viagem — todos referentes apenas à sua empresa.
        </p>
        <p class="text-muted small mb-4">
            Uso recomendado: portabilidade dos seus dados pessoais e da sua operação (LGPD, art. 18) ou
            simplesmente uma cópia de segurança fora do sistema. Os arquivos anexados (comprovantes, XML,
            PDF) não entram neste export — ficam disponíveis individualmente em cada tela.
        </p>
        <a href="{{ route('exportacao-dados.baixar') }}" class="btn btn-primary">
            <i class="bi bi-download me-1"></i> Baixar Exportação Completa (.zip)
        </a>
    </div>
</div>
@endsection
