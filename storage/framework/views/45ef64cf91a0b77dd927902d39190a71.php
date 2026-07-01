
<?php $__env->startSection('title', 'Acertos por Motorista'); ?>

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Acertos por Motorista</h4>
        <small class="text-muted">Histórico financeiro individual</small>
    </div>
    <?php if($motoristaSel && $viagens->count() > 0): ?>
    <a href="<?php echo e(route('acertos.pdf', request()->query())); ?>"
       target="_blank" class="btn btn-outline-dark">
        <i class="bi bi-printer me-1"></i> Exportar PDF
    </a>
    <?php endif; ?>
</div>


<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('acertos.index')); ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Motorista *</label>
                    <select name="motorista_id" class="form-select" required>
                        <option value="">Selecione o motorista</option>
                        <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>"
                                <?php echo e($motoristaSel == $m->id ? 'selected' : ''); ?>>
                                <?php echo e($m->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Período de</label>
                    <input type="date" name="data_inicio" class="form-control"
                           value="<?php echo e($dataInicio); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Até</label>
                    <input type="date" name="data_fim" class="form-control"
                           value="<?php echo e($dataFim); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if($motorista): ?>

    
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <div style="width:55px;height:55px;border-radius:50%;background:linear-gradient(135deg,#1a1a2e,#f97316);
                                display:flex;align-items:center;justify-content:center;margin:0 auto">
                        <i class="bi bi-person-fill text-white fs-4"></i>
                    </div>
                </div>
                <div class="col-md-5">
                    <h5 class="mb-0 fw-bold"><?php echo e($motorista->nome); ?></h5>
                    <small class="text-muted">
                        CPF: <?php echo e($motorista->cpf); ?>

                        <?php if($motorista->cnh): ?> | CNH: <?php echo e($motorista->cnh); ?> (<?php echo e($motorista->categoria_cnh); ?>) <?php endif; ?>
                    </small>
                </div>
                <div class="col-md-6">
                    <div class="row g-2 text-center">
                        <div class="col-3">
                            <div class="text-muted small">Comissão Padrão</div>
                            <div class="fw-bold text-primary"><?php echo e(number_format($motorista->percentual_comissao,2,',','.')); ?>%</div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Telefone</div>
                            <div class="fw-bold"><?php echo e($motorista->telefone ?? '-'); ?></div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Validade CNH</div>
                            <div class="fw-bold <?php echo e($motorista->validade_cnh?->isPast() ? 'text-danger' : ''); ?>">
                                <?php echo e($motorista->validade_cnh?->format('d/m/Y') ?? '-'); ?>

                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Status</div>
                            <span class="badge <?php echo e($motorista->status === 'ativo' ? 'bg-success' : 'bg-secondary'); ?>">
                                <?php echo e(ucfirst($motorista->status)); ?>

                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($viagens->count() > 0): ?>

        
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card text-center border-start border-primary border-3">
                    <div class="card-body py-3">
                        <div class="text-muted small">Viagens</div>
                        <div class="fs-3 fw-bold text-primary"><?php echo e($totais['total_viagens']); ?></div>
                        <div class="text-muted" style="font-size:.7rem">
                            <?php echo e($totais['viagens_encerradas']); ?> enc. /
                            <?php echo e($totais['viagens_abertas']); ?> abertas
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-start border-3" style="border-color:#f97316!important">
                    <div class="card-body py-3">
                        <div class="text-muted small">Total Frete</div>
                        <div class="fw-bold" style="color:#f97316;font-size:.9rem">
                            R$ <?php echo e(number_format($totais['total_frete'], 2, ',', '.')); ?>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-start border-warning border-3">
                    <div class="card-body py-3">
                        <div class="text-muted small">Comissão</div>
                        <div class="fw-bold text-warning" style="font-size:.9rem">
                            R$ <?php echo e(number_format($totais['total_comissao'], 2, ',', '.')); ?>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-start border-danger border-3">
                    <div class="card-body py-3">
                        <div class="text-muted small">Descontos</div>
                        <div class="fw-bold text-danger" style="font-size:.9rem">
                            R$ <?php echo e(number_format($totais['total_descontos'], 2, ',', '.')); ?>

                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-md-2">
                <div class="card text-center border-start border-warning border-3"
                    style="background:linear-gradient(135deg,#fffbeb,#fef3c7)">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                            <i class="bi bi-clock-history text-warning" style="font-size:.8rem"></i>
                            <span class="text-muted small">Saldo a Pagar</span>
                        </div>
                        <div class="fw-bold text-warning fs-6">
                            R$ <?php echo e(number_format($totais['saldo_a_pagar'], 2, ',', '.')); ?>

                        </div>
                        <div class="text-muted" style="font-size:.7rem">
                            <?php echo e($totais['viagens_abertas']); ?>

                            <?php echo e($totais['viagens_abertas'] === 1 ? 'viagem aberta' : 'viagens abertas'); ?>

                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-md-2">
                <div class="card text-center border-start border-success border-3"
                    style="background:linear-gradient(135deg,#f0fdf4,#dcfce7)">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:.8rem"></i>
                            <span class="text-muted small">Total Pago</span>
                        </div>
                        <div class="fw-bold text-success fs-6">
                            R$ <?php echo e(number_format($totais['saldo_pago'], 2, ',', '.')); ?>

                        </div>
                        <div class="text-muted" style="font-size:.7rem">
                            <?php echo e($totais['viagens_encerradas']); ?>

                            <?php echo e($totais['viagens_encerradas'] === 1 ? 'viagem encerrada' : 'viagens encerradas'); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-list-ul me-2 text-primary"></i>Viagens no Período</span>
                <?php if($totais['total_km'] > 0): ?>
                <span class="text-muted small">
                    <i class="bi bi-speedometer me-1"></i>
                    Total: <?php echo e(number_format($totais['total_km'], 0, ',', '.')); ?> km rodados
                </span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Veículo</th>
                            <th>Cliente</th>
                            <th>Rota</th>
                            <th>Saída</th>
                            <th class="text-end">Frete</th>
                            <th class="text-end">Comissão</th>
                            <th class="text-end">Descontos</th>
                            <th class="text-end">Saldo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $viagens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $viagem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr onclick="window.location='<?php echo e(route('viagens.show', $viagem)); ?>'"
                            style="cursor:pointer">
                            <td class="ps-3 text-muted">#<?php echo e($viagem->id); ?></td>
                            <td><?php echo e($viagem->veiculo->placa); ?></td>
                            <td><?php echo e($viagem->cliente->nome ?? '-'); ?></td>
                            <td class="small"><?php echo e($viagem->origem); ?> → <?php echo e($viagem->destino); ?></td>
                            <td><?php echo e($viagem->data_saida->format('d/m/Y')); ?></td>
                            <td class="text-end">R$ <?php echo e(number_format($viagem->valor_frete, 2, ',', '.')); ?></td>
                            <td class="text-end text-warning">R$ <?php echo e(number_format($viagem->valor_motorista, 2, ',', '.')); ?></td>
                            <td class="text-end text-danger">R$ <?php echo e(number_format($viagem->total_descontos, 2, ',', '.')); ?></td>
                            <td class="text-end fw-semibold <?php echo e($viagem->saldo_motorista >= 0 ? 'text-success' : 'text-danger'); ?>">
                                R$ <?php echo e(number_format($viagem->saldo_motorista, 2, ',', '.')); ?>

                            </td>
                            <td>
                                <span class="badge badge-status-<?php echo e($viagem->status); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $viagem->status))); ?>

                                </span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="ps-3">Totais do Período</td>
                            <td class="text-end">R$ <?php echo e(number_format($totais['total_frete'], 2, ',', '.')); ?></td>
                            <td class="text-end text-warning">R$ <?php echo e(number_format($totais['total_comissao'], 2, ',', '.')); ?></td>
                            <td class="text-end text-danger">R$ <?php echo e(number_format($totais['total_descontos'], 2, ',', '.')); ?></td>
                            <td class="text-end <?php echo e($totais['total_saldo'] >= 0 ? 'text-success' : 'text-danger'); ?>">
                                R$ <?php echo e(number_format($totais['total_saldo'], 2, ',', '.')); ?>

                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Nenhuma viagem encontrada para <strong><?php echo e($motorista->nome); ?></strong>
            no período de <?php echo e(\Carbon\Carbon::parse($dataInicio)->format('d/m/Y')); ?>

            a <?php echo e(\Carbon\Carbon::parse($dataFim)->format('d/m/Y')); ?>.
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-person-check fs-1 d-block mb-3"></i>
            <h5>Selecione um motorista para ver o histórico de acertos</h5>
            <p class="small">Escolha o motorista e o período desejado no filtro acima.</p>
        </div>
    </div>
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\casti\invexa-frete\resources\views/acertos/index.blade.php ENDPATH**/ ?>