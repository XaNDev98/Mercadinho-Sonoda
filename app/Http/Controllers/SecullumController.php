<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class SecullumController extends Controller
{
    private function autenticar()
    {
        $auth = Http::asForm()->post('https://autenticador.secullum.com.br/Token', [
            'grant_type' => 'password',
            'username'   => 'alexandre.mastrandea@sonodaponto.com.br',
            'password'   => '483123',
            'client_id'  => '3',
        ]);

        return $auth->json()['access_token'] ?? null;
    }

    public function funcionarios()
    {
        $token = $this->autenticar();

        if (!$token) {
            return response()->json([
                'erro' => 'Não foi possível gerar o token'
            ], 500);
        }

        $response = Http::withToken($token)
            ->withHeaders([
                'secullumidbancoselecionado' => '139285'
            ])
            ->get('https://pontowebintegracaoexterna.secullum.com.br/IntegracaoExterna/Funcionarios');

        return response()->json([
            'status' => $response->status(),
            'body' => $response->json(),
            'body_raw' => $response->body(),
        ]);
    }  

    public function buscarFuncionario($folha)
    {
        $token = $this->autenticar();

        $response = Http::withToken($token)
            ->withHeaders([
                'secullumidbancoselecionado' => '139285'
            ])
            ->get('https://pontowebintegracaoexterna.secullum.com.br/IntegracaoExterna/Funcionarios');

        $funcionarios = $response->json();

        // procurar pela folha
        $funcionario = collect($funcionarios)->firstWhere('NumeroFolha', $folha);

        if (!$funcionario) {
            return response()->json([
                'erro' => 'Funcionário não encontrado'
            ], 404);
        }

        return response()->json([
            'id' => $funcionario['Id'],
            'nome' => $funcionario['Nome'],
            'folha' => $funcionario['NumeroFolha'],
            'cargo' => $funcionario['Funcao']['Descricao'] ?? null
        ]);
    }
} 
