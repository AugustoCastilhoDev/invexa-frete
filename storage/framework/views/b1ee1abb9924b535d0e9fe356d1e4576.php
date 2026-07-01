
<?php $__env->startSection('title', 'Clientes'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Clientes</h4>
        <small class="text-muted">Gerencie os clientes cadastrados</small>
    </div>
    <a href="<?php echo e(route('clientes.create')); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo Cliente
    </a>
</div>


<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?php echo e(route('clientes.index')); ?>">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="busca" class="form-control border-start-0"
                               placeholder="Buscar por nome, razão social, CNPJ/CPF, cidade ou telefone..."
                               value="<?php echo e($busca ?? ''); ?>" autofocus>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-outline-secondary w-100">
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
    <strong><?php echo e($clientes->total()); ?></strong> cliente(s) encontrado(s).
    <a href="<?php echo e(route('clientes.index')); ?>" class="ms-2 text-decoration-none">Limpar busca</a>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Nome / Razão Social</th>
                    <th>CPF/CNPJ</th>
                    <th>Cidade</th>
                    <th>Telefone</th>
                    <th>Contato</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-semibold"><?php echo e($cliente->nome); ?></div>
                        <?php if($cliente->razao_social): ?>
                            <small class="text-muted"><?php echo e($cliente->razao_social); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <?php echo e($cliente->tipo_pessoa === 'juridica' ? 'PJ' : 'PF'); ?>

                        </span>
                        <?php echo e($cliente->documento_formatado); ?>

                    </td>
                    <td><?php echo e($cliente->cidade ?? '-'); ?><?php echo e($cliente->estado ? '/'.$cliente->estado : ''); ?></td>
                    <td><?php echo e($cliente->telefone ?? $cliente->celular ?? '-'); ?></td>
                    <td><?php echo e($cliente->contato ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo e($cliente->status === 'ativo' ? 'bg-success' : 'bg-secondary'); ?>">
                            <?php echo e(ucfirst($cliente->status)); ?>

                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?php echo e(route('clientes.show', $cliente)); ?>"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?php echo e(route('clientes.edit', $cliente)); ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="<?php echo e(route('clientes.destroy', $cliente)); ?>"
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
                        <i class="bi bi-building fs-3 d-block mb-2"></i>
                        <?php echo e($busca ? 'Nenhum cliente encontrado para "'.$busca.'".' : 'Nenhum cliente cadastrado.'); ?>

                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($clientes->hasPages()): ?>
    <div class="card-footer"><?php echo e($clientes->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\casti\invexa-frete\resources\views/clientes/index.blade.php ENDPATH**/ ?>