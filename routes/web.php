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
use App\Http\Controllers\ProgramacoesViagemController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ManutencoesController;
use App\Http\Controllers\DespesasGeraisController;
use App\Http\Controllers\DreController;
use App\Http\Controllers\MotoristaPortalAccessController;
use App\Http\Controllers\NotificacoesController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\EmissoesFiscaisController;
use App\Http\Controllers\Webhooks\AsaasWebhookController;
use App\Http\Controllers\Webhooks\FocusNfeWebhookController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;

Route::post('webhooks/asaas', AsaasWebhookController::class)->name('webhooks.asaas');
Route::post('webhooks/focus-nfe', FocusNfeWebhookController::class)->name('webhooks.focus-nfe');

Route::get('/', function () {
    if (auth('web')->check()) {
        return redirect()->route('dashboard');
    }

    if (auth('motorista')->check()) {
        return redirect()->route('portal.viagens.index');
    }

    return view('landing');
})->name('landing');

Route::view('/termos-de-uso', 'legal.termos')->name('legal.termos');
Route::view('/politica-de-privacidade', 'legal.privacidade')->name('legal.privacidade');

Route::middleware(['auth'])->group(function () {

    // Perfil do usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->middleware('admin')->name('profile.destroy');

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

    // Encerrar o modo suporte (o usuário autenticado aqui é o admin representado, não o super admin)
    Route::post('suporte/encerrar', [EmpresasController::class, 'encerrarSuporte'])
        ->name('suporte.encerrar');
});

// Gestão de empresas (tenants) — restrita ao super admin da plataforma
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::resource('empresas', EmpresasController::class)->except(['destroy']);
    Route::patch('empresas/{empresa}/status', [EmpresasController::class, 'toggleStatus'])
        ->name('empresas.toggle-status');
    Route::post('empresas/{empresa}/suporte', [EmpresasController::class, 'iniciarSuporte'])
        ->name('empresas.suporte.iniciar');
    Route::post('empresas/{empresa}/assinatura', [EmpresasController::class, 'criarAssinatura'])
        ->name('empresas.assinatura.criar');
    Route::patch('empresas/{empresa}/dados-fiscais', [EmpresasController::class, 'atualizarDadosFiscais'])
        ->name('empresas.dados-fiscais.atualizar');
    Route::post('empresas/{empresa}/focus-nfe/ativar', [EmpresasController::class, 'ativarFocusNfe'])
        ->name('empresas.focus-nfe.ativar');
    Route::patch('empresas/{empresa}/focus-nfe/desativar', [EmpresasController::class, 'desativarFocusNfe'])
        ->name('empresas.focus-nfe.desativar');
});

// Área operacional — escopada por empresa, o super admin (sem empresa) não acessa
Route::middleware(['auth', 'not_super_admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Despesas gerais (administrativas) — restrito a admin: custo interno, não é operacional
    Route::resource('despesas-gerais', DespesasGeraisController::class)
        ->except(['show'])
        ->parameters(['despesas-gerais' => 'despesaGeral'])
        ->middleware('admin');

    // Usuários do sistema (apenas admin)
    Route::resource('users', UsersController::class)
        ->except(['show'])
        ->middleware('admin');

    // Motoristas
    Route::get('motoristas/importar', [MotoristasController::class, 'importar'])->name('motoristas.importar');
    Route::get('motoristas/importar/modelo', [MotoristasController::class, 'importarTemplate'])->name('motoristas.importar.modelo');
    Route::post('motoristas/importar', [MotoristasController::class, 'importarStore'])->name('motoristas.importar.store');
    Route::resource('motoristas', MotoristasController::class)->except(['destroy']);
    Route::delete('motoristas/{motorista}', [MotoristasController::class, 'destroy'])
        ->middleware('admin')->name('motoristas.destroy');

    Route::post('motoristas/{motorista}/portal', [MotoristaPortalAccessController::class, 'store'])
        ->name('motoristas.portal.store');
    Route::delete('motoristas/{motorista}/portal', [MotoristaPortalAccessController::class, 'destroy'])
        ->name('motoristas.portal.destroy');

    // Veículos
    Route::get('veiculos/importar', [VeiculosController::class, 'importar'])->name('veiculos.importar');
    Route::get('veiculos/importar/modelo', [VeiculosController::class, 'importarTemplate'])->name('veiculos.importar.modelo');
    Route::post('veiculos/importar', [VeiculosController::class, 'importarStore'])->name('veiculos.importar.store');
    Route::resource('veiculos', VeiculosController::class)->except(['destroy']);
    Route::delete('veiculos/{veiculo}', [VeiculosController::class, 'destroy'])
        ->middleware('admin')->name('veiculos.destroy');

    // Manutenções (aninhadas no veículo, + histórico consolidado da frota)
    Route::get('manutencoes', [ManutencoesController::class, 'index'])->name('manutencoes.index');
    Route::get('manutencoes/csv', [ManutencoesController::class, 'csv'])->name('manutencoes.csv');
    Route::post('veiculos/{veiculo}/manutencoes', [ManutencoesController::class, 'store'])
        ->name('manutencoes.store');
    Route::patch('manutencoes/{manutencao}', [ManutencoesController::class, 'update'])
        ->name('manutencoes.update');
    Route::delete('manutencoes/{manutencao}', [ManutencoesController::class, 'destroy'])
        ->middleware('admin')->name('manutencoes.destroy');

    // Viagens
    Route::get('viagens/csv', [ViagensController::class, 'csv'])->name('viagens.csv');
    Route::resource('viagens', ViagensController::class)->except(['destroy'])->parameters([
    'viagens' => 'viagem']);
    Route::delete('viagens/{viagem}', [ViagensController::class, 'destroy'])
        ->middleware('admin')->name('viagens.destroy');

    Route::patch('viagens/{viagem}/avancar-status', [ViagensController::class, 'avancarStatus'])
        ->name('viagens.avancar-status');

    Route::patch('viagens/{viagem}/encerrar', [ViagensController::class, 'encerrar'])
        ->name('viagens.encerrar');

    Route::patch('viagens/{viagem}/recebimento', [ViagensController::class, 'marcarRecebimento'])
        ->name('viagens.recebimento');

    Route::patch('viagens/{viagem}/assinatura', [ViagensController::class, 'assinar'])
        ->name('viagens.assinar');

    Route::get('viagens/{viagem}/imprimir', [ViagensController::class, 'imprimir'])
    ->name('viagens.imprimir');

    // Programação de Frota (próxima viagem planejada)
    Route::resource('programacoes', ProgramacoesViagemController::class)
        ->except(['destroy', 'show'])
        ->parameters(['programacoes' => 'programacao']);
    Route::delete('programacoes/{programacao}', [ProgramacoesViagemController::class, 'destroy'])
        ->middleware('admin')->name('programacoes.destroy');

    // Lançamentos (aninhados na viagem)
    Route::post('viagens/{viagem}/lancamentos', [LancamentosController::class, 'store'])
        ->name('lancamentos.store');
    Route::patch('lancamentos/{lancamento}/aprovar', [LancamentosController::class, 'aprovar'])
        ->name('lancamentos.aprovar');
    Route::patch('lancamentos/{lancamento}/rejeitar', [LancamentosController::class, 'rejeitar'])
        ->name('lancamentos.rejeitar');
    Route::delete('lancamentos/{lancamento}', [LancamentosController::class, 'destroy'])
        ->middleware('admin')->name('lancamentos.destroy');

    // Descontos (aninhados na viagem)
    Route::post('viagens/{viagem}/descontos', [DescontosController::class, 'store'])
        ->name('descontos.store');
    Route::delete('descontos/{desconto}', [DescontosController::class, 'destroy'])
        ->middleware('admin')->name('descontos.destroy');

    // Relatórios (financeiro/estratégico) — restrito a admin
    Route::middleware('admin')->group(function () {
        Route::get('/relatorios', [RelatorioController::class, 'index'])
            ->name('relatorios.index');

        Route::get('/relatorios/pdf', [RelatorioController::class, 'pdf'])
            ->name('relatorios.pdf');

        Route::get('/relatorios/csv', [RelatorioController::class, 'csv'])
            ->name('relatorios.csv');

        // DRE (Demonstrativo de Resultado)
        Route::get('/dre', [DreController::class, 'index'])->name('dre.index');
        Route::get('/dre/pdf', [DreController::class, 'pdf'])->name('dre.pdf');
    });

    Route::post('viagens/{viagem}/documentos', [DocumentosController::class, 'store'])
        ->name('documentos.store');
    Route::patch('documentos/{documento}', [DocumentosController::class, 'update'])
        ->name('documentos.update');
    Route::delete('documentos/{documento}', [DocumentosController::class, 'destroy'])
        ->middleware('admin')->name('documentos.destroy');

    Route::post('viagens/{viagem}/emissoes-fiscais/{tipo}', [EmissoesFiscaisController::class, 'store'])
        ->whereIn('tipo', ['cte', 'mdfe'])
        ->name('viagens.emissoes-fiscais.store');
    Route::get('emissoes-fiscais', [EmissoesFiscaisController::class, 'index'])
        ->name('emissoes-fiscais.index');
    Route::get('emissoes-fiscais/csv', [EmissoesFiscaisController::class, 'csv'])
        ->name('emissoes-fiscais.csv');
    Route::post('emissoes-fiscais/{emissaoFiscal}/atualizar-status', [EmissoesFiscaisController::class, 'atualizarStatus'])
        ->name('emissoes-fiscais.atualizar-status');
    Route::post('emissoes-fiscais/{emissaoFiscal}/encerrar', [EmissoesFiscaisController::class, 'encerrar'])
        ->name('emissoes-fiscais.encerrar');

    Route::get('/dashboard/grafico', [DashboardController::class, 'grafico'])
    ->name('dashboard.grafico');

    // Clientes
    Route::get('clientes/importar', [ClientesController::class, 'importar'])->name('clientes.importar');
    Route::get('clientes/importar/modelo', [ClientesController::class, 'importarTemplate'])->name('clientes.importar.modelo');
    Route::post('clientes/importar', [ClientesController::class, 'importarStore'])->name('clientes.importar.store');
    Route::resource('clientes', ClientesController::class)->except(['destroy']);
    Route::delete('clientes/{cliente}', [ClientesController::class, 'destroy'])
        ->middleware('admin')->name('clientes.destroy');

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