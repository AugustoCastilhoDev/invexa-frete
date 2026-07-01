
<?php $__env->startSection('title', 'Novo Motorista'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Novo Motorista</h4>
        <small class="text-muted">Preencha os dados do motorista</small>
    </div>
    <a href="<?php echo e(route('motoristas.index')); ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="<?php echo e(route('motoristas.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input type="text" name="nome" class="form-control <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('nome')); ?>" required>
                    <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CPF *</label>
                    <input type="text" name="cpf" id="cpf"
                           class="form-control <?php $__errorArgs = ['cpf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           value="<?php echo e(old('cpf')); ?>" placeholder="000.000.000-00" required>
                    <?php $__errorArgs = ['cpf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">CNH</label>
                    <input type="text" name="cnh" class="form-control"
                           value="<?php echo e(old('cnh')); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Categoria CNH</label>
                    <select name="categoria_cnh" class="form-select">
                        <option value="">Selecione</option>
                        <?php $__currentLoopData = ['A','B','C','D','E','AB','AC','AD','AE']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat); ?>" <?php echo e(old('categoria_cnh') == $cat ? 'selected' : ''); ?>>
                                <?php echo e($cat); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Validade CNH</label>
                    <input type="date" name="validade_cnh" class="form-control"
                           value="<?php echo e(old('validade_cnh')); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Telefone</label>
                    <input type="text" name="telefone" id="telefone" class="form-control"
                           value="<?php echo e(old('telefone')); ?>" placeholder="(00) 00000-0000">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">E-mail</label>
                    <input type="email" name="email" class="form-control"
                           value="<?php echo e(old('email')); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">% Comissão sobre Frete *</label>
                    <div class="input-group">
                        <input type="number" name="percentual_comissao" step="0.01" min="0" max="100"
                               class="form-control <?php $__errorArgs = ['percentual_comissao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               value="<?php echo e(old('percentual_comissao', 0)); ?>" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <?php $__errorArgs = ['percentual_comissao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="ativo" <?php echo e(old('status') == 'ativo' ? 'selected' : ''); ?>>Ativo</option>
                        <option value="inativo" <?php echo e(old('status') == 'inativo' ? 'selected' : ''); ?>>Inativo</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Salvar Motorista
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Máscara de CPF
    document.getElementById('cpf').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = v;
    });

    // Máscara de Telefone
    document.getElementById('telefone').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '($1) $2');
        v = v.replace(/(\d{5})(\d{4})$/, '$1-$2');
        this.value = v;
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\casti\invexa-frete\resources\views/motoristas/create.blade.php ENDPATH**/ ?>