<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Viagem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

trait GeraComprovanteAcerto
{
    /**
     * Depois de assinado, sempre serve o PDF gerado no instante da
     * assinatura (assinatura_motorista_comprovante_path) em vez de
     * recalcular do estado atual do banco — a viagem fica travada pra
     * edição financeira, mas se um admin reabrir e ajustar valores depois,
     * o comprovante já entregue ao motorista não pode silenciosamente
     * mudar de conteúdo. Antes de assinar, não existe comprovante
     * congelado ainda, então continua gerando ao vivo (é só uma prévia).
     */
    protected function streamComprovanteAcerto(Viagem $viagem)
    {
        if ($viagem->assinatura_motorista_comprovante_path) {
            $conteudo = Storage::disk(config('filesystems.uploads_disk'))
                ->get($viagem->assinatura_motorista_comprovante_path);

            if ($conteudo !== null) {
                return response($conteudo, 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="acerto-viagem-' . $viagem->id . '.pdf"',
                ]);
            }
        }

        return $this->gerarPdfAcerto($viagem)->stream('acerto-viagem-' . $viagem->id . '.pdf');
    }

    protected function gerarComprovanteAcertoBytes(Viagem $viagem): string
    {
        return $this->gerarPdfAcerto($viagem)->output();
    }

    private function gerarPdfAcerto(Viagem $viagem)
    {
        $viagem->load(['motorista', 'veiculo', 'lancamentos', 'descontos']);

        $assinaturaBase64 = null;
        if ($viagem->assinatura_motorista_path) {
            $conteudo = Storage::disk(config('filesystems.uploads_disk'))->get($viagem->assinatura_motorista_path);
            $assinaturaBase64 = $conteudo ? 'data:image/png;base64,' . base64_encode($conteudo) : null;
        }

        return Pdf::loadView('viagens.imprimir', compact('viagem', 'assinaturaBase64'))
            ->setPaper('a4', 'portrait');
    }
}
