<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::with('categoria')->orderBy('nome');

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $produtos = $query->get();
        $categorias = Categoria::orderBy('nome')->get();

        return view('produtos.index', compact('produtos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nome')->get();

        return view('produtos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'preco' => 'required|numeric|min:0',
            'estoque' => 'required|integer|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $caminhoImagem = null;

        if ($request->hasFile('imagem')) {
            $caminhoImagem = $request->file('imagem')->store('produtos', 'public');
        }

        Produto::create([
            'nome' => $request->nome,
            'preco' => $request->preco,
            'estoque' => $request->estoque,
            'categoria_id' => $request->categoria_id,
            'imagem' => $caminhoImagem,
            'ativo' => 1,
        ]);

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $produto = Produto::findOrFail($id);
        $categorias = Categoria::orderBy('nome')->get();

        return view('produtos.edit', compact('produto', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'preco' => 'required|numeric|min:0',
            'estoque' => 'required|integer|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'ativo' => 'nullable|boolean',
        ]);

        $dados = [
            'nome' => $request->nome,
            'preco' => $request->preco,
            'estoque' => $request->estoque,
            'categoria_id' => $request->categoria_id,
            'ativo' => $request->has('ativo') ? (int) $request->ativo : $produto->ativo,
        ];

        if ($request->hasFile('imagem')) {
            if (!empty($produto->imagem) && Storage::disk('public')->exists($produto->imagem)) {
                Storage::disk('public')->delete($produto->imagem);
            }

            $dados['imagem'] = $request->file('imagem')->store('produtos', 'public');
        }

        $produto->update($dados);

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);

        if (!empty($produto->imagem) && Storage::disk('public')->exists($produto->imagem)) {
            Storage::disk('public')->delete($produto->imagem);
        }

        $produto->delete();

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto removido com sucesso.');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'categoria_id' => 'nullable|exists:categorias,id',
            'arquivo_csv' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        if (!$request->hasFile('arquivo_csv')) {
            return redirect()
                ->route('produtos.index')
                ->with('error', 'Nenhum arquivo foi enviado.');
        }

        $arquivo = $request->file('arquivo_csv');
        $conteudo = file_get_contents($arquivo->getRealPath());

        if ($conteudo === false || trim($conteudo) === '') {
            return redirect()
                ->route('produtos.index')
                ->with('error', 'O arquivo enviado está vazio ou não pôde ser lido.');
        }

        $linhas = preg_split('/\r\n|\r|\n/', trim($conteudo));
        $categoriaId = $request->categoria_id;

        if (empty($linhas)) {
            return redirect()
                ->route('produtos.index')
                ->with('error', 'Nenhum dado encontrado no arquivo para importação.');
        }

        DB::beginTransaction();

        try {
            $importados = 0;
            $atualizados = 0;

            foreach ($linhas as $numeroLinha => $linha) {
                $linha = trim($linha);

                if ($linha === '') {
                    continue;
                }

                $partes = array_map('trim', explode(';', $linha));

                if (count($partes) < 4) {
                    throw new \Exception(
                        'Linha ' . ($numeroLinha + 1) . ' inválida. Use o formato: id;nome;preco;quantidade'
                    );
                }

                $id = $partes[0];
                $nome = $partes[1];
                $preco = str_replace(',', '.', $partes[2]);
                $quantidade = $partes[3];

                if ($id === '' || $nome === '') {
                    throw new \Exception(
                        'Linha ' . ($numeroLinha + 1) . ' inválida. ID e nome são obrigatórios.'
                    );
                }

                if (!is_numeric($id) || (int) $id <= 0) {
                    throw new \Exception(
                        'Linha ' . ($numeroLinha + 1) . ' inválida. O ID deve ser numérico e maior que zero.'
                    );
                }

                if (!is_numeric($preco)) {
                    throw new \Exception(
                        'Linha ' . ($numeroLinha + 1) . ' inválida. O preço deve ser numérico.'
                    );
                }

                if (!is_numeric($quantidade) || (int) $quantidade < 0) {
                    throw new \Exception(
                        'Linha ' . ($numeroLinha + 1) . ' inválida. A quantidade deve ser numérica e não pode ser negativa.'
                    );
                }

                $id = (int) $id;
                $preco = (float) $preco;
                $quantidade = (int) $quantidade;

                $produto = Produto::find($id);

                if ($produto) {
                    $produto->update([
                        'nome' => $nome,
                        'preco' => $preco,
                        'estoque' => $quantidade,
                        'categoria_id' => $categoriaId ?: $produto->categoria_id,
                        'ativo' => 1,
                    ]);

                    $atualizados++;
                } else {
                    Produto::create([
                        'id' => $id,
                        'nome' => $nome,
                        'preco' => $preco,
                        'estoque' => $quantidade,
                        'categoria_id' => $categoriaId,
                        'imagem' => null,
                        'ativo' => 1,
                    ]);

                    $importados++;
                }
            }

            DB::commit();

            return redirect()
                ->route('produtos.index')
                ->with(
                    'success',
                    'Importação concluída com sucesso. Novos: ' . $importados . ' | Atualizados: ' . $atualizados
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('produtos.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}