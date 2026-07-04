<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\GeraComprovanteAcerto;
use App\Http\Controllers\Controller;
use App\Models\Viagem;
use Illuminate\Support\Facades\Auth;

class PortalViagensController extends Controller
{
    use GeraComprovanteAcerto;

    public function index()
    {
        $viagens = Auth::guard('motorista')->user()
            ->viagens()
            ->orderByDesc('data_saida')
            ->paginate(10);

        return view('portal.viagens.index', compact('viagens'));
    }

    public function show(Viagem $viagem)
    {
        $this->autorizar($viagem);

        $viagem->load(['veiculo', 'cliente', 'lancamentos', 'descontos', 'documentos']);

        return view('portal.viagens.show', compact('viagem'));
    }

    public function comprovante(Viagem $viagem)
    {
        $this->autorizar($viagem);

        return $this->streamComprovanteAcerto($viagem);
    }

    private function autorizar(Viagem $viagem): void
    {
        abort_unless($viagem->motorista_id === Auth::guard('motorista')->id(), 403);
    }
}
