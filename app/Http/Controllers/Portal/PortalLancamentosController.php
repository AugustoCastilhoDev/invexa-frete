<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Lancamento;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalLancamentosController extends Controller
{
    public function store(Request $request, Viagem $viagem)
    {
        abort_unless($viagem->motorista_id === Auth::guard('motorista')->id(), 403);
        abort_if($viagem->status === 'encerrada', 400, 'Esta viagem já foi encerrada.');

        $request->validate([
            'tipo'            => 'required|in:combustivel,manutencao,outros',
            'descricao'       => 'required|string|max:255',
            'valor'           => 'required|numeric|min:0',
            'data_lancamento' => 'required|date',
            'comprovante'     => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $lancamento = new Lancamento($request->only(['tipo', 'descricao', 'valor', 'data_lancamento']));
        $lancamento->viagem_id = $viagem->id;
        $lancamento->status = 'pendente';
        $lancamento->comprovante = $request->file('comprovante')
            ->store('comprovantes', config('filesystems.uploads_disk'));
        $lancamento->save();

        return redirect()->route('portal.viagens.show', $viagem)
            ->with('success', 'Lançamento enviado! Ele ficará pendente até ser aprovado pela transportadora.');
    }
}
