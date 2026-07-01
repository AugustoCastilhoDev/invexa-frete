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

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Motoristas
    Route::resource('motoristas', MotoristasController::class);

    // Veículos
    Route::resource('veiculos', VeiculosController::class);

    // Viagens
    Route::resource('viagens', ViagensController::class)->parameters([
    'viagens' => 'viagem']);
    
    Route::patch('viagens/{viagem}/encerrar', [ViagensController::class, 'encerrar'])
        ->name('viagens.encerrar');

    Route::get('viagens/{viagem}/imprimir', [ViagensController::class, 'imprimir'])
    ->name('viagens.imprimir');

    // Lançamentos (aninhados na viagem)
    Route::post('viagens/{viagem}/lancamentos', [LancamentosController::class, 'store'])
        ->name('lancamentos.store');
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

});

require __DIR__.'/auth.php';