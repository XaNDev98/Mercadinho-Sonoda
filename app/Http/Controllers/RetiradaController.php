<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\Retirada;
use App\Models\RetiradaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class RetiradaController extends Controller
{
    public function index(Request $request)
    {
        $query = Retirada::with(['funcionario', 'itens.produto']);

        if ($request->filled('nome')) {
            $nome = trim($request->nome);

            $query->whereHas('funcionario', function ($q) use ($nome) {
                $q->where('nome', 'like', '%' . $nome . '%');
            });
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_hora', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_hora', '<=', $request->data_fim);
        }

        $retiradas = $query
            ->orderByDesc('data_hora')
            ->orderByDesc('id')
            ->get();

        $retiradasAgrupadas = $retiradas->groupBy('numero_folha')->map(function ($grupo) {
            $primeiraRetirada = $grupo->first();
            $funcionario = $primeiraRetirada->funcionario;

            $itensAgrupados = [];

            foreach ($grupo as $retirada) {
                foreach ($retirada->itens as $item) {
                    $produtoId = $item->produto_id;
                    $produto = $item->produto;

                    $nomeProduto = $produto->nome ?? 'Produto não encontrado';
                    $imagemProduto = !empty(optional($produto)->imagem) ? trim($produto->imagem) : '';

                    if (!isset($itensAgrupados[$produtoId])) {
                        $itensAgrupados[$produtoId] = (object) [
                            'produto_id' => $produtoId,
                            'nome' => $nomeProduto,
                            'imagem' => $imagemProduto,
                            'quantidade' => 0,
                            'valor_unitario' => (float) $item->valor_unitario,
                            'subtotal' => 0,
                        ];
                    }

                    $itensAgrupados[$produtoId]->quantidade += (int) $item->quantidade;
                    $itensAgrupados[$produtoId]->subtotal += (float) $item->subtotal;
                }
            }

            return (object) [
                'funcionario' => $funcionario,
                'numero_folha' => $primeiraRetirada->numero_folha,
                'valor_total' => (float) $grupo->sum('valor_total'),
                'data_hora' => $grupo->max('data_hora'),
                'total_pedidos' => $grupo->count(),
                'itens' => collect(array_values($itensAgrupados))->sortBy('nome')->values(),
            ];
        })->sortByDesc('data_hora')->values();

        $topCompradores = $retiradas->groupBy('numero_folha')->map(function ($grupo) {
            $primeiraRetirada = $grupo->first();
            $funcionario = $primeiraRetirada->funcionario;

            return (object) [
                'id' => $funcionario->id ?? null,
                'nome' => $funcionario->nome ?? 'Funcionário não encontrado',
                'foto' => $funcionario->foto ?? null,
                'numero_folha' => $primeiraRetirada->numero_folha,
                'total_pedidos' => $grupo->count(),
                'total_gasto' => (float) $grupo->sum('valor_total'),
            ];
        })->sortBy([
            ['total_gasto', 'desc'],
            ['total_pedidos', 'desc'],
        ])->take(3)->values();

        $topItens = DB::table('retirada_itens')
            ->join('produtos', 'produtos.id', '=', 'retirada_itens.produto_id')
            ->join('retiradas', 'retiradas.id', '=', 'retirada_itens.retirada_id')
            ->when($request->filled('nome'), function ($q) use ($request) {
                $q->join('funcionarios', 'funcionarios.id', '=', 'retiradas.funcionario_id')
                    ->where('funcionarios.nome', 'like', '%' . trim($request->nome) . '%');
            })
            ->when($request->filled('data_inicio'), function ($q) use ($request) {
                $q->whereDate('retiradas.data_hora', '>=', $request->data_inicio);
            })
            ->when($request->filled('data_fim'), function ($q) use ($request) {
                $q->whereDate('retiradas.data_hora', '<=', $request->data_fim);
            })
            ->select(
                'produtos.id',
                'produtos.nome',
                'produtos.imagem',
                DB::raw('SUM(retirada_itens.quantidade) as total_quantidade'),
                DB::raw('SUM(retirada_itens.subtotal) as total_valor')
            )
            ->groupBy('produtos.id', 'produtos.nome', 'produtos.imagem')
            ->orderByDesc('total_quantidade')
            ->orderByDesc('total_valor')
            ->limit(3)
            ->get();

        return view('retiradas.index', [
            'retiradas' => $retiradasAgrupadas,
            'topCompradores' => $topCompradores,
            'topItens' => $topItens,
        ]);
    }

    public function create()
    {
        $funcionarios = Funcionario::where('ativo', true)
            ->orderBy('nome')
            ->get();

        $produtos = Produto::with('categoria')
            ->where('ativo', true)
            ->where('estoque', '>', 0)
            ->orderBy('nome')
            ->get();

        return view('retiradas.create', compact('funcionarios', 'produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_folha' => 'required|string',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:0',
        ]);

        $funcionario = Funcionario::where('numero_folha', $request->numero_folha)
            ->where('ativo', true)
            ->first();

        if (!$funcionario) {
            return back()
                ->withInput()
                ->with('error', 'Funcionário não encontrado pelo número da folha.');
        }

        DB::beginTransaction();

        try {
            $agora = now();

            $retirada = Retirada::create([
                'funcionario_id' => $funcionario->id,
                'numero_folha' => $funcionario->numero_folha,
                'valor_total' => 0,
                'data_hora' => $agora,
            ]);

            $teveItemValido = false;

            foreach ($request->itens as $item) {
                $quantidade = (int) ($item['quantidade'] ?? 0);

                if ($quantidade <= 0) {
                    continue;
                }

                $produto = Produto::where('id', $item['produto_id'])
                    ->where('ativo', true)
                    ->lockForUpdate()
                    ->first();

                if (!$produto) {
                    throw new \Exception('Produto não encontrado ou inativo.');
                }

                if ($produto->estoque < $quantidade) {
                    throw new \Exception("Estoque insuficiente para o produto {$produto->nome}");
                }

                $valorUnitario = (float) $produto->preco;
                $subtotal = $valorUnitario * $quantidade;

                RetiradaItem::create([
                    'retirada_id' => $retirada->id,
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'subtotal' => $subtotal,
                ]);

                $produto->decrement('estoque', $quantidade);
                $teveItemValido = true;
            }

            if (!$teveItemValido) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->with('error', 'Informe ao menos um produto com quantidade maior que zero.');
            }

            $valorTotal = RetiradaItem::where('retirada_id', $retirada->id)->sum('subtotal');

            $retirada->update([
                'valor_total' => $valorTotal,
                'data_hora' => $agora,
            ]);

            DB::commit();

            if (session('kiosk_modo_fixo', false)) {
                return redirect()
                    ->route('kiosk')
                    ->with('success', 'Retirada registrada com sucesso.');
            }

            return redirect()
                ->route('retiradas.index')
                ->with('success', 'Retirada registrada com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function exportar(Request $request)
    {
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $nome = trim((string) $request->input('nome'));

        if (empty($dataInicio) || empty($dataFim)) {
            return redirect()
                ->route('retiradas.index')
                ->with('error', 'Selecione a data inicial e a data final para baixar o TXT.');
        }

        $query = Retirada::join('funcionarios', 'retiradas.funcionario_id', '=', 'funcionarios.id')
            ->select(
                'funcionarios.nome as colaborador_nome',
                'retiradas.numero_folha',
                DB::raw('SUM(retiradas.valor_total) as gasto_total')
            )
            ->whereBetween('retiradas.data_hora', [
                $dataInicio . ' 00:00:00',
                $dataFim . ' 23:59:59',
            ])
            ->groupBy('funcionarios.nome', 'retiradas.numero_folha')
            ->orderBy('funcionarios.nome');

        if ($nome !== '') {
            $query->where('funcionarios.nome', 'like', '%' . $nome . '%');
        }

        $dados = $query->get();

        $linhas = [];
        $linhas[] = 'COLABORADOR;FOLHA;GASTO';

        foreach ($dados as $item) {
            $linhas[] =
                $this->limparTextoParaTxt($item->colaborador_nome) . ';' .
                $this->limparTextoParaTxt($item->numero_folha) . ';' .
                number_format((float) $item->gasto_total, 2, ',', '.');
        }

        if ($dados->isEmpty()) {
            $linhas[] = 'NENHUM REGISTRO ENCONTRADO NO PERIODO SELECIONADO';
        }

        $conteudo = implode(PHP_EOL, $linhas);
        $nomeArquivo = 'retiradas_periodo_' . now()->format('Ymd_His') . '.txt';

        return Response::make($conteudo, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
        ]);
    }

    private function limparTextoParaTxt($valor): string
    {
        $texto = trim((string) $valor);
        $texto = str_replace(["\r", "\n", ";"], [' ', ' ', ','], $texto);

        return $texto;
    }
}
