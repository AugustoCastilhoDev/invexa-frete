
<?php $__env->startSection('title', 'Veículos'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Veículos</h4>
        <small class="text-muted">Gerencie os veículos cadastrados</small>
    </div>
    <a href="<?php echo e(route('veiculos.create')); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo Veículo
    </a>
</div>


<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?php echo e(route('veiculos.index')); ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="busca" class="form-control border-start-0"
                               placeholder="Buscar por placa, modelo ou marca..."
                               value="<?php echo e($busca ?? ''); ?>" autofocus>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="<?php echo e(route('veiculos.index')); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>


<?php if($busca): ?>
<div class="alert alert-info py-2 mb-3">
    <i class="bi bi-funnel me-1"></i>
    Resultado para <strong>"<?php echo e($busca); ?>"</strong> —
    <strong><?php echo e($veiculos->total()); ?></strong> veículo(s) encontrado(s).
    <a href="<?php echo e(route('veiculos.index')); ?>" class="ms-2 text-decoration-none">Limpar busca</a>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Placa</th>
                    <th>Modelo / Marca</th>
                    <th>Tipo</th>
                    <th>Ano</th>
                    <th>Capacidade</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?php echo e($veiculo->placa); ?></td>
                    <td><?php echo e($veiculo->modelo); ?><?php echo e($veiculo->marca ? ' / '.$veiculo->marca : ''); ?></td>
                    <td><?php echo e(ucfirst($veiculo->tipo)); ?></td>
                    <td><?php echo e($veiculo->ano ?? '-'); ?></td>
                    <td><?php echo e($veiculo->capacidade_kg ? number_format($veiculo->capacidade_kg, 0, ',', '.').' kg' : '-'); ?></td>
                    <td>
                        <?php
                            $badge = match($veiculo->status) {
                                'ativo'      => 'bg-success',
                                'inativo'    => 'bg-secondary',
                                'manutencao' => 'bg-warning text-dark',
                            };
                        ?>
                        <span class="badge <?php echo e($badge); ?>"><?php echo e(ucfirst($veiculo->status)); ?></span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?php echo e(route('veiculos.show', $veiculo)); ?>"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?php echo e(route('veiculos.edit', $veiculo)); ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="<?php echo e(route('veiculos.destroy', $veiculo)); ?>"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Confirma exclusão?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-car-front fs-3 d-block mb-2"></i>
                        <?php echo e($busca ? 'Nenhum veículo encontrado para "'.$busca.'".' : 'Nenhum veículo cadastrado.'); ?>

                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($veiculos->hasPages()): ?>
    <div class="card-footer"><?php echo e($veiculos->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\casti\invexa-frete\resources\views/veiculos/index.blade.php ENDPATH**/ ?>