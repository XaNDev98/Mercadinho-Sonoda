<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function index()
    {
        $produtos = Produto::with('categoria')
            ->where('ativo', 1)
            ->where('estoque', '>', 0)
            ->orderBy('nome')
            ->get();

        $categoriasUnicas = [];

        foreach ($produtos as $produto) {
            $nomeCategoria = null;

            if (isset($produto->categoria) && is_object($produto->categoria) && !empty($produto->categoria->nome)) {
                $nomeCategoria = $produto->categoria->nome;
            } elseif (!empty($produto->categoria) && is_string($produto->categoria)) {
                $nomeCategoria = $produto->categoria;
            }

            if ($nomeCategoria) {
                $categoriasUnicas[] = $nomeCategoria;
            }
        }

        $categoriasUnicas = array_values(array_unique($categoriasUnicas));
        sort($categoriasUnicas);

        return view('kiosk.index', [
            'produtos' => $produtos,
            'categoriasUnicas' => $categoriasUnicas,
            'modoFixo' => session('kiosk_modo_fixo', false),
        ]);
    }

    public function ativarModoFixo(Request $request)
    {
        session(['kiosk_modo_fixo' => true]);

        return redirect()->route('kiosk');
    }

    public function desativarModoFixo(Request $request)
    {
        $request->validate([
            'senha' => ['required', 'string'],
        ]);

        $senhaKiosk = env('KIOSK_PASSWORD');

        if ($request->senha !== $senhaKiosk) {
            return redirect()
                ->route('kiosk')
                ->with('error', 'Senha inválida para sair do modo fixo.');
        }

        session()->forget('kiosk_modo_fixo');

        return redirect()
            ->route('retiradas.create')
            ->with('success', 'Modo fixo desativado com sucesso.');
    }
}