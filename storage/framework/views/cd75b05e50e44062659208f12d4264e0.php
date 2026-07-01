
<?php $__env->startSection('title', 'Relatório Financeiro'); ?>

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Relatório Financeiro</h4>
        <small class="text-muted">Análise por período</small>
    </div>
    <a href="<?php echo e(route('relatorios.pdf', request()->query())); ?>"
       target="_blank" class="btn btn-outline-dark">
        <i class="bi bi-printer me-1"></i> Exportar PDF
    </a>
</div>


<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('relatorios.index')); ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm"
                           value="<?php echo e($dataInicio); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm"
                           value="<?php echo e($dataFim); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Motorista</label>
                    <select name="motorista_id" class="form-select form-select-sm">
                        <option value="">Todos os motoristas</option>
                        <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>"
                                <?php echo e($motoristaSel == $m->id ? 'selected' : ''); ?>>
                                <?php echo e($m->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="veiculo_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v->id); ?>"
                                <?php echo e($veiculoSel == $v->id ? 'selected' : ''); ?>>
                                <?php echo e($v->placa); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="todos"            <?php echo e($statusSel === 'todos'             ? 'selected' : ''); ?>>Todos</option>
                        <option value="encerrada"        <?php echo e($statusSel === 'encerrada'         ? 'selected' : ''); ?>>Encerradas</option>
                        <option value="aberta"           <?php echo e($statusSel === 'aberta'            ? 'selected' : ''); ?>>Abertas</option>
                        <option value="em_andamento"     <?php echo e($statusSel === 'em_andamento'      ? 'selected' : ''); ?>>Em Andamento</option>
                        <option value="aguardando_acerto"<?php echo e($statusSel === 'aguardando_acerto' ? 'selected' : ''); ?>>Aguard. Acerto</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card text-center border-start border-primary border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Total Viagens</div>
                <div class="fs-3 fw-bold text-primary"><?php echo e($totais['total_viagens']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-3" style="border-color:#f97316!important">
            <div class="card-body py-3">
                <div class="text-muted small">Faturamento</div>
                <div class="fw-bold" style="color:#f97316">
                    R$ <?php echo e(number_format($totais['frete'], 2, ',', '.')); ?>

                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-warning border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Comissões</div>
                <div class="fw-bold text-warning">
                    R$ <?php echo e(number_format($totais['motoristas'], 2, ',', '.')); ?>

                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-danger border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Despesas</div>
                <div class="fw-bold text-danger">
                    R$ <?php echo e(number_format($totais['combustivel'] + $totais['manutencao'], 2, ',', '.')); ?>

                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-success border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Lucro Líquido</div>
                <div class="fw-bold text-success">
                    R$ <?php echo e(number_format($totais['lucro'], 2, ',', '.')); ?>

                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-start border-secondary border-3">
            <div class="card-body py-3">
                <div class="text-muted small">Saldo Motoristas</div>
                <div class="fw-bold text-secondary">
                    R$ <?php echo e(number_format($totais['saldo_motorista'], 2, ',', '.')); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    
<div class="col-md-5">
    <div class="card h-100 border-start border-primary border-3">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-person-badge me-2 text-primary"></i>Resumo por Motorista
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Motorista</th>
                        <th class="text-center">Viagens</th>
                        <th class="text-end">Frete</th>
                        <th class="text-end pe-3">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $porMotorista; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="ps-3 fw-semibold"><?php echo e($item['nome']); ?></td>
                        <td class="text-center"><?php echo e($item['viagens']); ?></td>
                        <td class="text-end">R$ <?php echo e(number_format($item['frete'], 2, ',', '.')); ?></td>
                        <td class="text-end pe-3
                            <?php echo e($item['saldo'] >= 0 ? 'text-success' : 'text-danger'); ?>">
                            R$ <?php echo e(number_format($item['saldo'], 2, ',', '.')); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">
                            Nenhum dado encontrado.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($porMotorista->hasPages()): ?>
        <div class="card-footer bg-white py-2">
            <?php echo e($porMotorista->onEachSide(1)->links('pagination::bootstrap-5')); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

    
    <div class="col-md-7">
        <div class="card h-100 border-start border-3" style="border-color:#8b5cf6!important">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Composição das Despesas
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="width:180px;height:180px">
                    <canvas id="graficoDespesas"></canvas>
                </div>

                
                <div class="mt-3 w-100" style="max-width:320px">
                    <?php
                        $despesasInfo = [
                            ['label' => 'Combustível',          'cor' => '#f59e0b', 'valor' => $totais['combustivel']],
                            ['label' => 'Manutenção',           'cor' => '#ef4444', 'valor' => $totais['manutencao']],
                            ['label' => 'Comissões Motoristas', 'cor' => '#8b5cf6', 'valor' => $totais['motoristas']],
                            ['label' => 'Lucro Transportadora', 'cor' => '#10b981', 'valor' => $totais['lucro']],
                        ];
                        $totalGeral = collect($despesasInfo)->sum('valor');
                    ?>

                    <?php $__currentLoopData = $despesasInfo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="d-flex align-items-center justify-content-between py-1"
                             style="border-bottom:1px solid #f0f0f0">
                            <div class="d-flex align-items-center gap-2">
                                <span style="display:inline-block;width:12px;height:12px;
                                             border-radius:3px;background:<?php echo e($info['cor']); ?>;
                                             flex-shrink:0"></span>
                                <span style="font-size:.8rem"><?php echo e($info['label']); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold" style="font-size:.85rem">
                                    R$ <?php echo e(number_format($info['valor'], 2, ',', '.')); ?>

                                </span>
                                <?php if($totalGeral > 0): ?>
                                <span class="text-muted" style="font-size:.75rem;min-width:35px;text-align:right">
                                    <?php echo e(number_format(($info['valor'] / $totalGeral) * 100, 0)); ?>%
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>

</div>


<div class="card border-start border-secondary border-3">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-list-ul me-2 text-primary"></i>
        Detalhamento das Viagens
        <span class="badge bg-secondary ms-2"><?php echo e($totais['total_viagens']); ?></span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Motorista</th>
                    <th>Veículo</th>
                    <th>Rota</th>
                    <th>Saída</th>
                    <th class="text-end">Frete</th>
                    <th class="text-end">Combustível</th>
                    <th class="text-end">Manutenção</th>
                    <th class="text-end">Comissão</th>
                    <th class="text-end">Lucro</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $viagens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $viagem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr onclick="window.location='<?php echo e(route('viagens.show', $viagem)); ?>'"
                    style="cursor:pointer">
                    <td class="ps-3 text-muted">#<?php echo e($viagem->id); ?></td>
                    <td><?php echo e($viagem->motorista->nome); ?></td>
                    <td><?php echo e($viagem->veiculo->placa); ?></td>
                    <td class="small"><?php echo e($viagem->origem); ?> → <?php echo e($viagem->destino); ?></td>
                    <td><?php echo e($viagem->data_saida->format('d/m/Y')); ?></td>
                    <td class="text-end">R$ <?php echo e(number_format($viagem->valor_frete, 2, ',', '.')); ?></td>
                    <td class="text-end text-warning">R$ <?php echo e(number_format($viagem->total_combustivel, 2, ',', '.')); ?></td>
                    <td class="text-end text-danger">R$ <?php echo e(number_format($viagem->total_manutencao, 2, ',', '.')); ?></td>
                    <td class="text-end">R$ <?php echo e(number_format($viagem->valor_motorista, 2, ',', '.')); ?></td>
                    <td class="text-end fw-semibold <?php echo e($viagem->lucro_transportadora >= 0 ? 'text-success' : 'text-danger'); ?>">
                        R$ <?php echo e(number_format($viagem->lucro_transportadora, 2, ',', '.')); ?>

                    </td>
                    <td>
                        <span class="badge badge-status-<?php echo e($viagem->status); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $viagem->status))); ?>

                        </span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        Nenhuma viagem encontrada no período selecionado.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
            <?php if($viagens->count() > 0): ?>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="ps-3">Totais</td>
                    <td class="text-end">R$ <?php echo e(number_format($totais['frete'], 2, ',', '.')); ?></td>
                    <td class="text-end text-warning">R$ <?php echo e(number_format($totais['combustivel'], 2, ',', '.')); ?></td>
                    <td class="text-end text-danger">R$ <?php echo e(number_format($totais['manutencao'], 2, ',', '.')); ?></td>
                    <td class="text-end">R$ <?php echo e(number_format($totais['motoristas'], 2, ',', '.')); ?></td>
                    <td class="text-end <?php echo e($totais['lucro'] >= 0 ? 'text-success' : 'text-danger'); ?>">
                        R$ <?php echo e(number_format($totais['lucro'], 2, ',', '.')); ?>

                    </td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('graficoDespesas'), {
        type: 'doughnut',
        data: {
            labels: ['Combustível', 'Manutenção', 'Comissões Motoristas', 'Lucro Transportadora'],
            datasets: [{
                data: [
                    <?php echo e($totais['combustivel']); ?>,
                    <?php echo e($totais['manutencao']); ?>,
                    <?php echo e($totais['motoristas']); ?>,
                    <?php echo e($totais['lucro']); ?>,
                ],
                backgroundColor: [
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                    '#10b981',
                ],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\casti\invexa-frete\resources\views/relatorios/index.blade.php ENDPATH**/ ?>