<?php

use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalLancamentosController;
use App\Http\Controllers\Portal\PortalSenhaController;
use App\Http\Controllers\Portal\PortalViagensController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('login', [PortalAuthController::class, 'create'])->name('login');
    Route::post('login', [PortalAuthController::class, 'store']);

    Route::middleware('auth:motorista')->group(function () {
        Route::post('logout', [PortalAuthController::class, 'destroy'])->name('logout');

        Route::get('/', [PortalViagensController::class, 'index'])->name('viagens.index');
        Route::get('viagens/{viagem}', [PortalViagensController::class, 'show'])->name('viagens.show');
        Route::get('viagens/{viagem}/comprovante', [PortalViagensController::class, 'comprovante'])->name('viagens.comprovante');

        Route::post('viagens/{viagem}/lancamentos', [PortalLancamentosController::class, 'store'])
            ->name('lancamentos.store');

        Route::get('senha', [PortalSenhaController::class, 'edit'])->name('senha.edit');
        Route::put('senha', [PortalSenhaController::class, 'update'])->name('senha.update');
    });
});
