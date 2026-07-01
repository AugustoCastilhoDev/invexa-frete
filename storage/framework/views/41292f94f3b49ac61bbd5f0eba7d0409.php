
<?php $__env->startSection('title', 'Nova Viagem'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Nova Viagem</h4>
        <small class="text-muted">Preencha os dados para abrir a viagem</small>
    </div>
    <a href="<?php echo e(route('viagens.index')); ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="<?php echo e(route('viagens.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-person-badge me-1"></i> Motorista e Veículo
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Motorista *</label>
                    <select name="motorista_id" id="motorista_id"
                            class="form-select <?php $__errorArgs = ['motorista_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                        <option value="">Selecione o motorista</option>
                        <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($motorista->id); ?>"
                                    data-comissao="<?php echo e($motorista->percentual_comissao); ?>"
                                    <?php echo e(old('motorista_id') == $motorista->id ? 'selected' : ''); ?>>
                                <?php echo e($motorista->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['motorista_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Veículo *</label>
                    <select name="veiculo_id"
                            class="form-select <?php $__errorArgs = ['veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                        <option value="">Selecione o veículo</option>
                        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($veiculo->id); ?>"
                                <?php echo e(old('veiculo_id') == $veiculo->id ? 'selected' : ''); ?>>
                                <?php echo e($veiculo->placa); ?> — <?php echo e($veiculo->modelo); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-map me-1"></i> Rota e Datas
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Origem *</label>
                    <input type="text" name="origem" class="form-control"
                           value="<?php echo e(old('origem')); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Destino *</label>
                    <input type="text" name="destino" class="form-control"
                           value="<?php echo e(old('destino')); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="cliente_id" class="form-select">
                        <option value="">Selecione o cliente</option>
                        <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cliente->id); ?>"
                                <?php echo e(old('cliente_id') == $cliente->id ? 'selected' : ''); ?>>
                                <?php echo e($cliente->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data de Saída *</label>
                    <input type="date" name="data_saida" class="form-control"
                           value="<?php echo e(old('data_saida', date('Y-m-d'))); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">KM Inicial</label>
                    <input type="number" name="km_inicial" class="form-control"
                           value="<?php echo e(old('km_inicial')); ?>" min="0">
                </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:1px">
                <i class="bi bi-cash-stack me-1"></i> Financeiro
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Valor do Frete *</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" name="valor_frete" id="valor_frete"
                               class="form-control <?php $__errorArgs = ['valor_frete'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               value="<?php echo e(old('valor_frete', '0.00')); ?>"
                               step="0.01" min="0" required>
                    </div>
                    <?php $__errorArgs = ['valor_frete'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">% Comissão Motorista *</label>
                    <div class="input-group">
                        <input type="number" name="percentual_motorista" id="percentual_motorista"
                               class="form-control" step="0.01" min="0" max="100"
                               value="<?php echo e(old('percentual_motorista', '0.00')); ?>" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Valor do Motorista</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" id="valor_motorista_preview"
                               class="form-control bg-light" readonly value="0,00">
                    </div>
                    <small class="text-muted">Calculado automaticamente</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Adiantamento (Vale-Viagem)</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" name="valor_adiantamento"
                            class="form-control"
                            value="<?php echo e(old('valor_adiantamento', '0.00')); ?>"
                            step="0.01" min="0">
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox"
                            name="adiantamento_descontavel"
                            id="adiantamento_descontavel"
                            value="1"
                            <?php echo e(old('adiantamento_descontavel', true) ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-semibold" for="adiantamento_descontavel">
                            Descontar do motorista?
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3"><?php echo e(old('observacoes')); ?></textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-truck me-1"></i> Abrir Viagem
                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Preenche % comissão automaticamente ao selecionar motorista
    document.getElementById('motorista_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const comissao = selected.dataset.comissao || 0;
        document.getElementById('percentual_motorista').value = comissao;
        calcularMotorista();
    });

    function calcularMotorista() {
        const frete     = parseFloat(document.getElementById('valor_frete').value) || 0;
        const percentual = parseFloat(document.getElementById('percentual_motorista').value) || 0;
        const valor     = (frete * percentual / 100).toFixed(2);
        document.getElementById('valor_motorista_preview').value =
            parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    }

    document.getElementById('valor_frete').addEventListener('input', calcularMotorista);
    document.getElementById('percentual_motorista').addEventListener('input', calcularMotorista);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\casti\invexa-frete\resources\views/viagens/create.blade.php ENDPATH**/ ?>