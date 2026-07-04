<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Viagem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

trait GeraComprovanteAcerto
{
    protected function streamComprovanteAcerto(Viagem $viagem)
    {
        $viagem->load(['motorista', 'veiculo', 'lancamentos', 'descontos']);

        $assinaturaBase64 = null;
        if ($viagem->assinatura_motorista_path) {
            $conteudo = Storage::disk(config('filesystems.uploads_disk'))->get($viagem->assinatura_motorista_path);
            $assinaturaBase64 = $conteudo ? 'data:image/png;base64,' . base64_encode($conteudo) : null;
        }

        $pdf = Pdf::loadView('viagens.imprimir', compact('viagem', 'assinaturaBase64'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('acerto-viagem-' . $viagem->id . '.pdf');
    }
}
