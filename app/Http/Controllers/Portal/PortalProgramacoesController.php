<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ProgramacaoViagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalProgramacoesController extends Controller
{
    public function chegada(Request $request, ProgramacaoViagem $programacao)
    {
        abort_unless($programacao->motorista_id === Auth::guard('motorista')->id(), 403);
        abort_if(! $programacao->estaPendente(), 400, 'Esta programação já foi confirmada.');

        $request->validate(['horario' => 'nullable|date_format:H:i']);

        $programacao->marcarChegada($request->input('horario') ?: now()->format('H:i'));

        return redirect()->route('portal.viagens.index')
            ->with('success', 'Chegada no local de coleta informada com sucesso!');
    }
}
