<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MotoristaResource;
use App\Models\Motorista;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MotoristasController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $motoristas = Motorista::when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return MotoristaResource::collection($motoristas);
    }

    public function show(Motorista $motorista): MotoristaResource
    {
        return new MotoristaResource($motorista);
    }
}
