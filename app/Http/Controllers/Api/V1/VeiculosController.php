<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VeiculoResource;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VeiculosController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $veiculos = Veiculo::when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('placa')
            ->paginate(20)
            ->withQueryString();

        return VeiculoResource::collection($veiculos);
    }

    public function show(Veiculo $veiculo): VeiculoResource
    {
        return new VeiculoResource($veiculo);
    }
}
