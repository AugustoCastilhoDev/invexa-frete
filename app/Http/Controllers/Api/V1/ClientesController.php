<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientesController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $clientes = Cliente::when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return ClienteResource::collection($clientes);
    }

    public function show(Cliente $cliente): ClienteResource
    {
        return new ClienteResource($cliente);
    }
}
