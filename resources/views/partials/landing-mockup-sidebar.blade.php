{{-- Sidebar ilustrativa reaproveitada pelos mockups da landing page — espelha
     a mesma estrutura de seções do menu real (layouts/app.blade.php), com o
     item de $active destacado. --}}
@php
    $mockupSecoes = [
        'Principal' => [
            ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'key' => 'dashboard'],
            ['icon' => 'bi-truck', 'label' => 'Viagens', 'key' => 'viagens'],
            ['icon' => 'bi-signpost-2', 'label' => 'Programação de Frota', 'key' => 'programacao'],
            ['icon' => 'bi-person-check', 'label' => 'Acertos', 'key' => 'acertos'],
        ],
        'Cadastros' => [
            ['icon' => 'bi-person-badge', 'label' => 'Motoristas', 'key' => 'motoristas'],
            ['icon' => 'bi-car-front', 'label' => 'Veículos', 'key' => 'veiculos'],
            ['icon' => 'bi-building', 'label' => 'Clientes', 'key' => 'clientes'],
            ['icon' => 'bi-tools', 'label' => 'Manutenções', 'key' => 'manutencoes'],
        ],
        'Fiscal' => [
            ['icon' => 'bi-file-earmark-text', 'label' => 'CT-e', 'key' => 'cte'],
            ['icon' => 'bi-file-earmark-check', 'label' => 'MDF-e', 'key' => 'mdfe'],
        ],
        'Administração' => [
            ['icon' => 'bi-bar-chart-line', 'label' => 'Financeiro', 'key' => 'financeiro'],
            ['icon' => 'bi-clipboard-data', 'label' => 'DRE', 'key' => 'dre'],
        ],
    ];
@endphp
<div class="d-none d-md-flex flex-column" style="width:190px; flex-shrink:0; background:linear-gradient(180deg,#1a1a2e 0%,#16213e 100%); padding:16px 12px">
    <div class="d-flex align-items-center gap-2" style="padding:0 6px 14px">
        <div class="invexa-logo-badge rounded-2 d-flex align-items-center justify-content-center" style="width:26px;height:26px;flex-shrink:0">
            <i class="bi bi-truck-front-fill text-white" style="font-size:.7rem"></i>
        </div>
        <span class="text-white fw-bold" style="font-size:.85rem">Invexa Frete</span>
    </div>
    @foreach($mockupSecoes as $secao => $itens)
        <div class="invexa-mockup-navsection">{{ $secao }}</div>
        @foreach($itens as $item)
            <div class="invexa-mockup-navitem {{ $item['key'] === ($active ?? null) ? 'is-active' : '' }}"><i class="bi {{ $item['icon'] }}"></i>{{ $item['label'] }}</div>
        @endforeach
    @endforeach
</div>
