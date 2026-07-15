<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use App\Models\EmissaoFiscal;
use App\Models\Motorista;
use App\Models\ProgramacaoViagem;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Http\Controllers\Concerns\GeraComprovanteAcerto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViagensController extends Controller
{
    use GeraComprovanteAcerto;

    public function index()
{
    $viagens    = $this->viagensFiltradas()->paginate(15)->withQueryString();
    $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
    $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();

    return view('viagens.index', compact(
        'viagens',
        'motoristas',
        'veiculos'
    ));
}

    public function csv(): StreamedResponse
    {
        $viagens = $this->viagensFiltradas()->get();

        return response()->streamDownload(function () use ($viagens) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF");

            fputcsv($saida, [
                'Viagem', 'Motorista', 'Veículo', 'Cliente', 'Origem', 'Destino', 'Saída',
                'Frete', 'Status', 'Frete Recebido', 'Data Recebimento',
            ], ';');

            foreach ($viagens as $viagem) {
                fputcsv($saida, [
                    $viagem->id,
                    $viagem->motorista->nome,
                    $viagem->veiculo->placa,
                    $viagem->cliente->nome ?? '-',
                    $viagem->origem,
                    $viagem->destino,
                    $viagem->data_saida->format('d/m/Y'),
                    number_format($viagem->valor_frete, 2, ',', ''),
                    ucfirst(str_replace('_', ' ', $viagem->status)),
                    $viagem->frete_recebido ? 'Sim' : 'Não',
                    $viagem->data_recebimento_frete?->format('d/m/Y') ?? '-',
                ], ';');
            }

            fclose($saida);
        }, 'viagens-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function viagensFiltradas()
    {
        $status       = request('status', 'todas');
        $motorista    = request('motorista_id');
        $veiculo      = request('veiculo_id');
        $dataInicio   = request('data_inicio');
        $dataFim      = request('data_fim');
        $recebimento  = request('recebimento', 'todos');

        $query = Viagem::with(['motorista', 'veiculo', 'cliente'])
            ->orderByDesc('created_at');

        if ($status !== 'todas') {
            $query->where('status', $status);
        }

        if ($motorista) {
            $query->where('motorista_id', $motorista);
        }

        if ($veiculo) {
            $query->where('veiculo_id', $veiculo);
        }

        if ($dataInicio) {
            $query->whereDate('data_saida', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->whereDate('data_saida', '<=', $dataFim);
        }

        if ($recebimento === 'recebido') {
            $query->where('frete_recebido', true);
        } elseif ($recebimento === 'pendente') {
            $query->where('frete_recebido', false);
        }

        return $query;
    }

    public function marcarRecebimento(Viagem $viagem)
    {
        $viagem->forceFill($viagem->frete_recebido
            ? ['frete_recebido' => false, 'data_recebimento_frete' => null]
            : ['frete_recebido' => true, 'data_recebimento_frete' => now()]
        )->save();

        return redirect()->back()
            ->with('success', $viagem->frete_recebido
                ? 'Recebimento do frete confirmado!'
                : 'Recebimento do frete desfeito.');
    }

    public function create(Request $request)
{
    $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
    $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();
    $clientes   = Cliente::where('status', 'ativo')->orderBy('nome')->get();

    $programacao = null;
    if ($request->filled('programacao_id')) {
        $programacao = ProgramacaoViagem::where('status', 'pendente')
            ->find($request->input('programacao_id'));
    }

    $veiculosBloqueados = EmissaoFiscal::where('tipo', 'mdfe')->where('status', 'autorizado')
        ->whereNull('encerrado_em')->with('viagem:id,veiculo_id')->get()
        ->pluck('viagem.veiculo_id')->filter()->unique();

    return view('viagens.create', compact('motoristas', 'veiculos', 'clientes', 'programacao', 'veiculosBloqueados'));
}

    public function store(Request $request)
    {
        $request->validate([
            'motorista_id'         => 'required|exists:motoristas,id',
            'veiculo_id'           => 'required|exists:veiculos,id',
            'origem'               => 'required|string|max:255',
            'origem_uf'            => 'nullable|string|max:2',
            'origem_codigo_municipio' => 'nullable|string|max:7',
            'destino'              => 'required|string|max:255',
            'destino_uf'           => 'nullable|string|max:2',
            'destino_codigo_municipio' => 'nullable|string|max:7',
            'descricao_carga'      => 'nullable|string|max:255',
            'cliente_id'           => 'nullable|exists:clientes,id',
            'data_saida'           => 'required|date',
            'km_inicial'           => 'nullable|integer|min:0',
            'valor_frete'          => 'required|numeric|min:0',
            'percentual_motorista' => 'required|numeric|min:0|max:100',
            'valor_adiantamento'   => 'nullable|numeric|min:0',
            'observacoes'          => 'nullable|string',
        ]);

        if (EmissaoFiscal::mdfeAbertoDoVeiculo((int) $request->input('veiculo_id'))->exists()) {
            return back()->withInput()->withErrors([
                'veiculo_id' => 'Este veículo possui um MDF-e autorizado e ainda não encerrado. Encerre o manifesto da viagem anterior antes de abrir uma nova.',
            ]);
        }

        $data = $request->only([
            'motorista_id', 'veiculo_id',
            'origem', 'origem_uf', 'origem_codigo_municipio',
            'destino', 'destino_uf', 'destino_codigo_municipio',
            'descricao_carga', 'cliente_id',
            'data_saida', 'km_inicial', 'valor_frete', 'percentual_motorista',
            'observacoes',
        ]);

        $data['valor_adiantamento'] = $request->input('valor_adiantamento', 0) ?? 0;
        $data['adiantamento_descontavel'] = $request->has('adiantamento_descontavel');

        $viagem = Viagem::create($data);

        // Recalcula com a mesma lógica usada em toda atualização, garantindo
        // que o saldo já nasça respeitando adiantamento_descontavel.
        $viagem->recalcularTotais();

        if ($request->filled('programacao_id')) {
            ProgramacaoViagem::where('status', 'pendente')
                ->find($request->input('programacao_id'))
                ?->forceFill(['status' => 'confirmada', 'viagem_id' => $viagem->id])
                ->save();
        }

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Viagem aberta com sucesso!');
    }

    public function show(Viagem $viagem)
    {
        $viagem->load([
            'motorista', 'veiculo', 'cliente', 'empresa',
            'criadoPor', 'atualizadoPor',
            'lancamentos.criadoPor', 'descontos.criadoPor', 'documentos.criadoPor',
            'emissoesFiscais',
        ]);

        $programacaoPendente = ProgramacaoViagem::where('status', 'pendente')
            ->where('veiculo_id', $viagem->veiculo_id)
            ->first();

        return view('viagens.show', compact('viagem', 'programacaoPendente'));
    }

    public function edit(Viagem $viagem)
{
    $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
    $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();
    $clientes   = Cliente::where('status', 'ativo')->orderBy('nome')->get();
    return view('viagens.edit', compact('viagem', 'motoristas', 'veiculos', 'clientes'));
}

    public function update(Request $request, Viagem $viagem)
    {
        $request->validate([
            'motorista_id'         => 'required|exists:motoristas,id',
            'veiculo_id'           => 'required|exists:veiculos,id',
            'origem'               => 'required|string|max:255',
            'origem_uf'            => 'nullable|string|max:2',
            'origem_codigo_municipio' => 'nullable|string|max:7',
            'destino'              => 'required|string|max:255',
            'destino_uf'           => 'nullable|string|max:2',
            'destino_codigo_municipio' => 'nullable|string|max:7',
            'descricao_carga'      => 'nullable|string|max:255',
            'cliente_id'           => 'nullable|exists:clientes,id',
            'data_saida'           => 'required|date',
            'data_retorno'         => 'nullable|date|after_or_equal:data_saida',
            'km_inicial'           => 'nullable|integer|min:0',
            'km_final'             => 'nullable|integer|min:0',
            'valor_frete'          => 'required|numeric|min:0',
            'percentual_motorista' => 'required|numeric|min:0|max:100',
            'valor_adiantamento'   => 'nullable|numeric|min:0',
            'status'               => 'required|in:aberta,em_andamento,aguardando_acerto,encerrada',
            'observacoes'          => 'nullable|string',
        ]);

        $data = $request->all();

        $data['valor_adiantamento'] = $request->input('valor_adiantamento', 0) ?? 0;
        $data['adiantamento_descontavel'] = $request->has('adiantamento_descontavel');

        $viagem->update($data);
        $viagem->recalcularTotais();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Viagem atualizada com sucesso!');
    }

    public function destroy(Viagem $viagem)
    {
        $viagem->delete();
        return redirect()->route('viagens.index')
            ->with('success', 'Viagem removida com sucesso!');
    }

    public function avancarStatus(Viagem $viagem)
    {
        $proximo = $viagem->proximo_status;

        abort_if(! $proximo, 400, 'Não há um próximo status para esta viagem.');

        $viagem->update(['status' => $proximo]);
        $viagem->recalcularTotais();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Status da viagem atualizado com sucesso!');
    }

    public function encerrar(Viagem $viagem)
    {
        $viagem->update([
            'status'       => 'encerrada',
            'data_retorno' => now(),
        ]);
        $viagem->recalcularTotais();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Viagem encerrada com sucesso!');
    }

    public function assinar(Request $request, Viagem $viagem)
    {
        abort_unless($viagem->podeSerAssinada(), 400, 'Esta viagem ainda não está pronta para acerto.');

        $request->validate([
            'assinatura' => ['required', 'string', 'regex:/^data:image\/png;base64,/'],
        ]);

        $base64  = substr($request->input('assinatura'), strlen('data:image/png;base64,'));
        $binario = base64_decode($base64, true);

        if ($binario === false || strlen($binario) > 500 * 1024 || @getimagesizefromstring($binario) === false) {
            throw ValidationException::withMessages(['assinatura' => 'Assinatura inválida.']);
        }

        $disco   = config('filesystems.uploads_disk');
        $caminho = 'assinaturas/viagem-' . $viagem->id . '-' . (string) Str::uuid() . '.png';

        Storage::disk($disco)->put($caminho, $binario);

        if ($viagem->assinatura_motorista_path) {
            Storage::disk($disco)->delete($viagem->assinatura_motorista_path);
        }

        $viagem->forceFill([
            'assinatura_motorista_path' => $caminho,
            'assinatura_motorista_em'   => now(),
        ])->save();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Assinatura do motorista registrada com sucesso!');
    }

    public function imprimir(Viagem $viagem)
    {
        return $this->streamComprovanteAcerto($viagem);
    }
}