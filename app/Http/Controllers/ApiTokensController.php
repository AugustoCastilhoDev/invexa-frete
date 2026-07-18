<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiTokensController extends Controller
{
    public function index(Request $request): View
    {
        $tokens = $request->user()->tokens()->orderByDesc('created_at')->get();

        return view('api-tokens.index', compact('tokens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $token = $request->user()->createToken($request->input('name'));

        return redirect()->route('api-tokens.index')
            ->with('token_gerado', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return redirect()->route('api-tokens.index')->with('success', 'Token revogado.');
    }
}
