<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class FuncionarioController extends Controller
{
    public function index(Request $request)
{
    $bancoSelecionado = $request->get('banco_id');

    $query = Funcionario::query();

    if ($bancoSelecionado !== null && $bancoSelecionado !== '') {
        if ($bancoSelecionado === 'manual') {
            $query->whereNull('banco_id');
        } else {
            $query->where('banco_id', $bancoSelecionado);
        }
    }

    $funcionarios = $query->orderBy('nome')->get();

    $bancos = Funcionario::select('banco_id')
        ->distinct()
        ->orderBy('banco_id')
        ->pluck('banco_id');

    return view('funcionarios.index', compact('funcionarios', 'bancos', 'bancoSelecionado'));
}

    public function create()
    {
        return view('funcionarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'numero_folha' => 'required|string|max:50|unique:funcionarios,numero_folha',
            'cargo' => 'nullable|string|max:255',
            'foto' => 'nullable|string',
        ]);

        Funcionario::create([
            'banco_id' => null,
            'secullum_id' => null,
            'nome' => $request->nome,
            'numero_folha' => $request->numero_folha,
            'cargo' => $request->cargo,
            'foto' => $this->normalizarFotoSaida($request->foto),
            'ativo' => 1,
            'demissao' => null,
        ]);

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $funcionario = Funcionario::findOrFail($id);

        return view('funcionarios.edit', compact('funcionario'));
    }

    public function update(Request $request, $id)
    {
        $funcionario = Funcionario::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'numero_folha' => 'required|string|max:50|unique:funcionarios,numero_folha,' . $funcionario->id,
            'cargo' => 'nullable|string|max:255',
            'foto' => 'nullable|string',
        ]);

        $funcionario->update([
            'nome' => $request->nome,
            'numero_folha' => $request->numero_folha,
            'cargo' => $request->cargo,
            'foto' => $this->normalizarFotoSaida($request->foto),
        ]);

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $funcionario = Funcionario::findOrFail($id);
        $funcionario->delete();

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário removido com sucesso.');
    }

    public function buscarPorNumeroFolha($numero_folha): JsonResponse
    {
        $funcionario = Funcionario::where('numero_folha', trim($numero_folha))
            ->where('ativo', 1)
            ->first();

        if (! $funcionario) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador não encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'funcionario' => [
                'id' => $funcionario->id,
                'nome' => $funcionario->nome,
                'numero_folha' => $funcionario->numero_folha,
                'cargo' => $funcionario->cargo ?? 'Não informado',
                'foto' => $this->normalizarFotoSaida($funcionario->foto),
            ]
        ]);
    }

    public function sincronizar()
    {
        try {
            Artisan::call('app:sincronizar-funcionarios-secullum');
            $saida = Artisan::output();

            $funcionariosSincronizados = 0;
            $fotosRecebidas = 0;
            $status = 'Sincronização concluída';

            if (preg_match('/Funcionários ativos sincronizados:\s*(\d+)/u', $saida, $match)) {
                $funcionariosSincronizados = (int) $match[1];
            }

            if (preg_match('/Funcionários com foto:\s*(\d+)/u', $saida, $match)) {
                $fotosRecebidas = (int) $match[1];
            }

            if (stripos($saida, 'erro') !== false) {
                $status = 'Concluída com avisos';
            }

            return redirect()
                ->route('funcionarios.index')
                ->with('success', 'Sincronização executada com sucesso.')
                ->with('sync_output', [
                    'status' => $status,
                    'funcionarios_sincronizados' => $funcionariosSincronizados,
                    'fotos_recebidas' => $fotosRecebidas,
                    'texto' => $saida,
                ]);
        } catch (\Throwable $e) {
            return redirect()
                ->route('funcionarios.index')
                ->with('error', 'Erro ao sincronizar funcionários: ' . $e->getMessage());
        }
    }

    private function normalizarFotoSaida(?string $foto): ?string
    {
        if (empty($foto)) {
            return null;
        }

        $foto = trim($foto);

        if ($foto === '' || strtolower($foto) === 'null') {
            return null;
        }

        for ($i = 0; $i < 3; $i++) {
            if (str_starts_with($foto, 'data:image')) {
                $partes = explode(',', $foto, 2);

                if (count($partes) !== 2 || empty($partes[1])) {
                    return null;
                }

                $cabecalho = $partes[0];
                $conteudo = trim($partes[1]);

                $decodificado = base64_decode($conteudo, true);

                if ($decodificado !== false) {
                    $decodificado = trim($decodificado);

                    if (str_starts_with($decodificado, 'data:image')) {
                        $foto = $decodificado;
                        continue;
                    }
                }

                return $cabecalho . ',' . $conteudo;
            }

            if ($this->pareceBase64($foto)) {
                $decodificado = base64_decode($foto, true);

                if ($decodificado === false) {
                    return null;
                }

                $decodificado = trim($decodificado);

                if (str_starts_with($decodificado, 'data:image')) {
                    $foto = $decodificado;
                    continue;
                }

                if ($this->ehImagemBinaria($decodificado)) {
                    return 'data:image/jpeg;base64,' . $foto;
                }

                $foto = $decodificado;
                continue;
            }

            if ($this->ehImagemBinaria($foto)) {
                return 'data:image/jpeg;base64,' . base64_encode($foto);
            }

            break;
        }

        return null;
    }

    private function pareceBase64(string $valor): bool
    {
        if ($valor === '' || preg_match('/\s/', $valor)) {
            return false;
        }

        $decodificado = base64_decode($valor, true);

        return $decodificado !== false && base64_encode($decodificado) === $valor;
    }

    private function ehImagemBinaria(string $conteudo): bool
    {
        if (str_starts_with($conteudo, "\xFF\xD8\xFF")) {
            return true;
        }

        if (str_starts_with($conteudo, "\x89PNG")) {
            return true;
        }

        if (str_starts_with($conteudo, "GIF87a") || str_starts_with($conteudo, "GIF89a")) {
            return true;
        }

        if (substr($conteudo, 0, 4) === "RIFF" && substr($conteudo, 8, 4) === "WEBP") {
            return true;
        }

        return false;
    }
}