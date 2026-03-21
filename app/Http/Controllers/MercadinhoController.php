<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;

class MercadinhoController extends Controller
{
    public function buscarFuncionario(Request $request)
    {
        $request->validate([
            'numero_folha' => 'required'
        ]);

        $funcionario = Funcionario::where('numero_folha', $request->numero_folha)
            ->where('ativo', true)
            ->first();

        if (!$funcionario) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador não encontrado ou inativo.'
            ]);
        }

        return response()->json([
            'success' => true,
            'funcionario' => [
                'id' => $funcionario->id,
                'nome' => $funcionario->nome,
                'numero_folha' => $funcionario->numero_folha,
                'cargo' => $funcionario->cargo,
                'foto' => $funcionario->foto,
            ]
        ]);
    }
}