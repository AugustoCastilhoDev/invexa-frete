<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MotoristasController;
use App\Http\Controllers\VeiculosController;
use App\Http\Controllers\ViagensController;
use App\Http\Controllers\LancamentosController;
use App\Http\Controllers\DescontosController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\DocumentosController;
use App\Http\Controllers\AcertosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ManutencoesController;
use App\Http\Controllers\DespesasGeraisController;
use App\Http\Controllers\DreController;
use App\Http\Controllers\MotoristaPortalAccessController;
use App\Http\Controllers\NotificacoesController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Perfil do usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Autenticação em dois fatores (2FA)
    Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
        ->name('two-factor.enable');
    Route::post('/user/confirmed-two-factor-authentication', [TwoFactorAuthenticationController::class, 'confirm'])
        ->name('two-factor.confirm');
    Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
        ->name('two-factor.disable');
    Route::post('/user/two-factor-recovery-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])
        ->name('two-factor.recovery-codes');

    // Notificações
    Route::post('notificacoes/{notificacao}/ler', [NotificacoesController::class, 'marcarComoLida'])
        ->name('notificacoes.ler');
    Route::post('notificacoes/ler-todas', [NotificacoesController::class, 'marcarTodasComoLidas'])
        ->name('notificacoes.ler-todas');
});

// Gestão de empresas (tenants) — restrita ao super admin da plataforma
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::resource('empresas', EmpresasController::class)->except(['destroy']);
    Route::patch('empresas/{empresa}/status', [EmpresasController::class, 'toggleStatus'])
        ->name('empresas.toggle-status');
});

// Área operacional — escopada por empresa, o super admin (sem empresa) não acessa
Route::middleware(['auth', 'not_super_admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Despesas gerais (administrativas)
    Route::resource('despesas-gerais', DespesasGeraisController::class)
        ->except(['show'])
        ->parameters(['despesas-gerais' => 'despesaGeral']);

    // Usuários do sistema (apenas admin)
    Route::resource('users', UsersController::class)
        ->except(['show'])
        ->middleware('admin');

    // Motoristas
    Route::resource('motoristas', MotoristasController::class);

    Route::post('motoristas/{motorista}/portal', [MotoristaPortalAccessController::class, 'store'])
        ->name('motoristas.portal.store');
    Route::delete('motoristas/{motorista}/portal', [MotoristaPortalAccessController::class, 'destroy'])
        ->name('motoristas.portal.destroy');

    // Veículos
    Route::resource('veiculos', VeiculosController::class);

    // Manutenções (aninhadas no veículo)
    Route::post('veiculos/{veiculo}/manutencoes', [ManutencoesController::class, 'store'])
        ->name('manutencoes.store');
    Route::patch('manutencoes/{manutencao}', [ManutencoesController::class, 'update'])
        ->name('manutencoes.update');
    Route::delete('manutencoes/{manutencao}', [ManutencoesController::class, 'destroy'])
        ->name('manutencoes.destroy');

    // Viagens
    Route::resource('viagens', ViagensController::class)->parameters([
    'viagens' => 'viagem']);
    
    Route::patch('viagens/{viagem}/avancar-status', [ViagensController::class, 'avancarStatus'])
        ->name('viagens.avancar-status');

    Route::patch('viagens/{viagem}/encerrar', [ViagensController::class, 'encerrar'])
        ->name('viagens.encerrar');

    Route::patch('viagens/{viagem}/assinatura', [ViagensController::class, 'assinar'])
        ->name('viagens.assinar');

    Route::get('viagens/{viagem}/imprimir', [ViagensController::class, 'imprimir'])
    ->name('viagens.imprimir');

    // Lançamentos (aninhados na viagem)
    Route::post('viagens/{viagem}/lancamentos', [LancamentosController::class, 'store'])
        ->name('lancamentos.store');
    Route::patch('lancamentos/{lancamento}/aprovar', [LancamentosController::class, 'aprovar'])
        ->name('lancamentos.aprovar');
    Route::patch('lancamentos/{lancamento}/rejeitar', [LancamentosController::class, 'rejeitar'])
        ->name('lancamentos.rejeitar');
    Route::delete('lancamentos/{lancamento}', [LancamentosController::class, 'destroy'])
        ->name('lancamentos.destroy');

    // Descontos (aninhados na viagem)
    Route::post('viagens/{viagem}/descontos', [DescontosController::class, 'store'])
        ->name('descontos.store');
    Route::delete('descontos/{desconto}', [DescontosController::class, 'destroy'])
        ->name('descontos.destroy');

    // Relatórios
    Route::get('/relatorios', [RelatorioController::class, 'index'])
        ->name('relatorios.index');

    Route::get('/relatorios/pdf', [RelatorioController::class, 'pdf'])
        ->name('relatorios.pdf');

    Route::get('/relatorios/csv', [RelatorioController::class, 'csv'])
        ->name('relatorios.csv');

    // DRE (Demonstrativo de Resultado)
    Route::get('/dre', [DreController::class, 'index'])->name('dre.index');
    Route::get('/dre/pdf', [DreController::class, 'pdf'])->name('dre.pdf');


    Route::post('viagens/{viagem}/documentos', [DocumentosController::class, 'store'])
        ->name('documentos.store');
    Route::patch('documentos/{documento}', [DocumentosController::class, 'update'])
        ->name('documentos.update');
    Route::delete('documentos/{documento}', [DocumentosController::class, 'destroy'])
        ->name('documentos.destroy');

    Route::get('/dashboard/grafico', [DashboardController::class, 'grafico'])
    ->name('dashboard.grafico');

    // Clientes
    Route::resource('clientes', ClientesController::class);

    // Acertos
    Route::get('/acertos', [AcertosController::class, 'index'])
    ->name('acertos.index');
    Route::get('/acertos/pdf', [AcertosController::class, 'pdf'])
    ->name('acertos.pdf');
    Route::get('/acertos/csv', [AcertosController::class, 'csv'])
    ->name('acertos.csv');

});

require __DIR__.'/auth.php';
require __DIR__.'/portal.php';