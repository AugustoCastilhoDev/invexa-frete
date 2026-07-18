<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ViagemResource;
use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ViagensController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $viagens = Viagem::with(['motorista', 'veiculo', 'cliente'])
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('data_saida')
            ->paginate(20)
            ->withQueryString();

        return ViagemResource::collection($viagens);
    }

    public function show(Viagem $viagem): ViagemResource
    {
        return new ViagemResource($viagem->load(['motorista', 'veiculo', 'cliente']));
    }
}
