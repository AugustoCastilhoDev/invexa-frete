
<?php $__env->startSection('title', 'Viagens'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Viagens</h4>
        <small class="text-muted">Gerencie todas as viagens</small>
    </div>
    <a href="<?php echo e(route('viagens.create')); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nova Viagem
    </a>
</div>


<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('viagens.index')); ?>">
            <div class="row g-3 align-items-end">

                
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="todas"            <?php echo e(request('status','todas') === 'todas'             ? 'selected' : ''); ?>>Todos</option>
                        <option value="aberta"           <?php echo e(request('status') === 'aberta'                   ? 'selected' : ''); ?>>Aberta</option>
                        <option value="em_andamento"     <?php echo e(request('status') === 'em_andamento'             ? 'selected' : ''); ?>>Em Andamento</option>
                        <option value="aguardando_acerto"<?php echo e(request('status') === 'aguardando_acerto'        ? 'selected' : ''); ?>>Aguard. Acerto</option>
                        <option value="encerrada"        <?php echo e(request('status') === 'encerrada'                ? 'selected' : ''); ?>>Encerrada</option>
                    </select>
                </div>

                
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Motorista</label>
                    <select name="motorista_id" class="form-select form-select-sm">
                        <option value="">Todos os motoristas</option>
                        <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>"
                                <?php echo e(request('motorista_id') == $m->id ? 'selected' : ''); ?>>
                                <?php echo e($m->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Veículo</label>
                    <select name="veiculo_id" class="form-select form-select-sm">
                        <option value="">Todos os veículos</option>
                        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v->id); ?>"
                                <?php echo e(request('veiculo_id') == $v->id ? 'selected' : ''); ?>>
                                <?php echo e($v->placa); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Saída de</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm"
                           value="<?php echo e(request('data_inicio')); ?>">
                </div>

                
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Saída até</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm"
                           value="<?php echo e(request('data_fim')); ?>">
                </div>

                
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="<?php echo e(route('viagens.index')); ?>" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>


<?php
    $filtrosAtivos = array_filter([
        request('status', 'todas') !== 'todas' ? request('status') : null,
        request('motorista_id'),
        request('veiculo_id'),
        request('data_inicio'),
        request('data_fim'),
    ]);
?>

<?php if(count($filtrosAtivos)): ?>
<div class="alert alert-info py-2 d-flex justify-content-between align-items-center">
    <span class="small">
        <i class="bi bi-funnel me-1"></i>
        Filtros ativos — exibindo <strong><?php echo e($viagens->total()); ?></strong> viagem(ns)
    </span>
    <a href="<?php echo e(route('viagens.index')); ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x me-1"></i> Limpar filtros
    </a>
</div>
<?php endif; ?>


<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Motorista</th>
                    <th>Veículo</th>
                    <th>Cliente</th>
                    <th>Origem / Destino</th>
                    <th>Saída</th>
                    <th>Frete</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $viagens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $viagem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="ps-4 text-muted">#<?php echo e($viagem->id); ?></td>
                    <td class="fw-semibold"><?php echo e($viagem->motorista->nome); ?></td>
                    <td><?php echo e($viagem->veiculo->placa); ?></td>
                    <td><?php echo e($viagem->cliente->nome ?? '-'); ?></td>
                    <td><?php echo e($viagem->origem); ?> → <?php echo e($viagem->destino); ?></td>
                    <td><?php echo e($viagem->data_saida->format('d/m/Y')); ?></td>
                    <td>R$ <?php echo e(number_format($viagem->valor_frete, 2, ',', '.')); ?></td>
                    <td>
                        <span class="badge badge-status-<?php echo e($viagem->status); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $viagem->status))); ?>

                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?php echo e(route('viagens.show', $viagem)); ?>"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if($viagem->status !== 'encerrada'): ?>
                        <a href="<?php echo e(route('viagens.edit', $viagem)); ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php endif; ?>
                        <form action="<?php echo e(route('viagens.destroy', $viagem)); ?>"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Confirma exclusão desta viagem?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-truck fs-3 d-block mb-2"></i>
                        Nenhuma viagem encontrada com os filtros selecionados.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($viagens->hasPages()): ?>
    <div class="card-footer"><?php echo e($viagens->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\casti\invexa-frete\resources\views/viagens/index.blade.php ENDPATH**/ ?>