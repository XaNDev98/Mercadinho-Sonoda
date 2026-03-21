<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome',
        ]);

        Categoria::create([
            'nome' => trim($request->nome),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Categoria cadastrada com sucesso.');
    }
}