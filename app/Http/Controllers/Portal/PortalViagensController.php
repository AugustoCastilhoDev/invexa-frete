<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\GeraComprovanteAcerto;
use App\Http\Controllers\Controller;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalViagensController extends Controller
{
    use GeraComprovanteAcerto;

    public function index()
    {
        $motorista = Auth::guard('motorista')->user();

        $viagens = $motorista->viagens()
            ->orderByDesc('data_saida')
            ->paginate(10);

        $programacoesPendentes = $motorista->programacoes()
            ->where('status', 'pendente')
            ->orderBy('data_prevista')
            ->get();

        return view('portal.viagens.index', compact('viagens', 'programacoesPendentes'));
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

    public function iniciar(Request $request, Viagem $viagem)
    {
        $this->autorizar($viagem);

        abort_unless($viagem->podeSerIniciadaPeloMotorista(), 400, 'Esta viagem já foi iniciada.');

        $dados = $request->validate([
            'km_inicial' => 'nullable|integer|min:0',
        ]);

        $viagem->iniciar($dados['km_inicial'] ?? null);

        return redirect()->route('portal.viagens.show', $viagem)
            ->with('success', 'Viagem iniciada com sucesso!');
    }

    private function autorizar(Viagem $viagem): void
    {
        abort_unless($viagem->motorista_id === Auth::guard('motorista')->id(), 403);
    }
}
