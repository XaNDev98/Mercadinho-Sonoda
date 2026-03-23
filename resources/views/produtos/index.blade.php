@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/produtos-index.css') }}">
@endpush

@section('content')
<div class="produtos-page">
    <div class="produtos-shell">

        <div class="produtos-header">
            <div>
                <h2 class="produtos-title">Produtos</h2>
                <p class="produtos-subtitle">
                    Gerencie os produtos disponíveis no sistema.
                </p>
            </div>

            <div class="produtos-header-actions">
                <button
                    type="button"
                    onclick="abrirModalImportar()"
                    class="btn btn-warning"
                >
                    Importar TXT/CSV
                </button>

                <a href="{{ route('produtos.create') }}" class="btn btn-success">
                    + Cadastrar produto
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-box alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-box alert-warning">
                @foreach($errors->all() as $erro)
                    <div>{{ $erro }}</div>
                @endforeach
            </div>
        @endif

        <div class="produtos-filter-card">
            <form method="GET" action="{{ route('produtos.index') }}" class="produtos-filter-form">
                <div class="filter-field">
                    <label class="form-label">Filtrar por categoria</label>
                    <select name="categoria_id" class="form-input">
                        <option value="">Todas as categorias</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ (string) request('categoria_id') === (string) $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Filtrar
                </button>

                <a href="{{ route('produtos.index') }}" class="btn btn-light">
                    Limpar
                </a>
            </form>
        </div>

        <div class="produtos-table-card">
            <div class="produtos-table-wrap">
                <table class="produtos-table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th class="text-right">Preço</th>
                            <th class="text-center">Estoque</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($produtos as $produto)
                            <tr>
                                <td>
                                    @if(!empty($produto->imagem))
                                        <img
                                            src="{{ asset('storage/produtos/' . $produto->imagem) }}"
                                            alt="{{ $produto->nome }}"
                                            class="produto-thumb"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >
                                        <div class="produto-thumb produto-thumb-fallback" style="display:none;">
                                            S/I
                                        </div>
                                    @else
                                        <div class="produto-thumb produto-thumb-fallback">
                                            S/I
                                        </div>
                                    @endif
                                </td>

                                <td class="produto-code">
                                    {{ $produto->id }}
                                </td>

                                <td class="produto-name">
                                    {{ $produto->nome }}
                                </td>

                                <td>
                                    <span class="badge badge-category">
                                        {{ $produto->categoria->nome ?? 'Sem categoria' }}
                                    </span>
                                </td>

                                <td class="text-right produto-price">
                                    R$ {{ number_format($produto->preco, 2, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    <span class="badge {{ $produto->estoque > 0 ? 'badge-stock-ok' : 'badge-stock-zero' }}">
                                        {{ $produto->estoque }}
                                    </span>
                                </td>

                                <td>
                                    <div class="acoes-wrap">
                                        <button
                                            type="button"
                                            class="btn-editar-produto btn btn-sm btn-primary"
                                            data-id="{{ $produto->id }}"
                                            data-codigo="{{ $produto->id }}"
                                            data-nome="{{ $produto->nome }}"
                                            data-preco="{{ number_format((float) $produto->preco, 2, '.', '') }}"
                                            data-estoque="{{ $produto->estoque }}"
                                            data-categoria-id="{{ $produto->categoria_id }}"
                                            data-ativo="{{ $produto->ativo }}"
                                        >
                                            Editar
                                        </button>

                                        <form action="{{ route('produtos.destroy', $produto->id) }}" method="POST" onsubmit="return confirm('Deseja excluir este produto?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                🗑
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-row">
                                    Nenhum produto cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div id="modal-editar-produto" class="modal-overlay" style="display:none;" onclick="fecharModalEditar(event)">
    <div class="modal-dialog modal-dialog-md" onclick="event.stopPropagation()">
        <div class="modal-header modal-header-blue">
            <div>
                <h3 class="modal-title">Editar Produto</h3>
                <p class="modal-subtitle">
                    Atualize os dados do produto sem sair da tela.
                </p>
            </div>

            <button type="button" onclick="fecharModalEditar()" class="modal-close">
                ×
            </button>
        </div>

        <form id="form-editar-produto" method="POST" enctype="multipart/form-data" class="modal-body">
            @csrf
            @method('PUT')

            <div class="modal-grid">
                <div>
                    <label class="form-label">Código</label>
                    <input type="text" id="edit_codigo" readonly class="form-input form-input-readonly">
                </div>

                <div>
                    <label class="form-label">Preço</label>
                    <input type="number" step="0.01" min="0" name="preco" id="edit_preco" required class="form-input">
                </div>

                <div class="grid-col-full">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" id="edit_nome" required class="form-input">
                </div>

                <div>
                    <label class="form-label">Estoque</label>
                    <input type="number" min="0" name="estoque" id="edit_estoque" required class="form-input">
                </div>

                <div>
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" id="edit_categoria_id" class="form-input">
                        <option value="">Sem categoria</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Imagem</label>
                    <input type="file" name="imagem" accept="image/*" class="form-input">
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="ativo" id="edit_ativo" class="form-input">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" onclick="fecharModalEditar()" class="btn btn-light">
                    Cancelar
                </button>

                <button type="submit" class="btn btn-primary">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-importar-produto" class="modal-overlay" style="display:none;" onclick="fecharModalImportar(event)">
    <div class="modal-dialog modal-dialog-lg" onclick="event.stopPropagation()">
        <div class="modal-header modal-header-orange">
            <div>
                <h3 class="modal-title">Importar Produtos</h3>
                <p class="modal-subtitle">
                    Selecione um arquivo TXT ou CSV e visualize os dados antes de importar.
                </p>
            </div>

            <button type="button" onclick="fecharModalImportar()" class="modal-close">
                ×
            </button>
        </div>

        <form id="form-importar-produto" method="POST" action="{{ route('produtos.importar') }}" enctype="multipart/form-data" class="modal-body">
            @csrf

            <div class="import-info-box">
                <div class="import-info-title">Formato esperado do arquivo:</div>
                <div><strong>codigo;nome;preco;quantidade</strong></div>
                <div class="import-info-example-title">Exemplo:</div>
                <div>3;Pizza;12.50;20</div>
                <div>4;Refrigerante;2.90;50</div>
                <div>6;Arroz, feijão e filé de frango;13.20;10</div>
            </div>

            <div class="import-top-grid">
                <div>
                    <label class="form-label">Arquivo TXT/CSV</label>
                    <input
                        id="arquivo_csv"
                        type="file"
                        name="arquivo_csv"
                        accept=".csv,.txt,text/csv,text/plain"
                        required
                        class="form-input"
                    >
                </div>

                <button type="button" onclick="previsualizarArquivo()" class="btn btn-primary import-btn">
                    Pré-visualizar
                </button>

                <button type="button" onclick="limparPreviaImportacao()" class="btn btn-light import-btn">
                    Limpar prévia
                </button>
            </div>

            <div id="mensagem-preview" class="preview-message" style="display:none;"></div>

            <div id="preview-container" class="preview-container" style="display:none;">
                <div class="preview-header">
                    <div>
                        <h4 class="preview-title">Pré-visualização da importação</h4>
                        <p id="preview-resumo" class="preview-subtitle"></p>
                    </div>
                </div>

                <div class="preview-table-wrap">
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th>Linha</th>
                                <th>Código</th>
                                <th>Nome</th>
                                <th class="text-right">Preço</th>
                                <th class="text-center">Quantidade</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="preview-body"></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" onclick="fecharModalImportar()" class="btn btn-light">
                    Cancelar
                </button>

                <button id="btn-importar-final" type="submit" disabled class="btn btn-warning btn-disabled">
                    Importar arquivo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalEditarPorBotao(botao) {
        const form = document.getElementById('form-editar-produto');
        form.action = `/produtos/${botao.dataset.id}`;

        document.getElementById('edit_codigo').value = botao.dataset.codigo || '';
        document.getElementById('edit_nome').value = botao.dataset.nome || '';
        document.getElementById('edit_preco').value = botao.dataset.preco || '';
        document.getElementById('edit_estoque').value = botao.dataset.estoque || 0;
        document.getElementById('edit_categoria_id').value = botao.dataset.categoriaId || '';
        document.getElementById('edit_ativo').value = botao.dataset.ativo || '1';

        document.getElementById('modal-editar-produto').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function fecharModalEditar(event = null) {
        if (event && event.target !== document.getElementById('modal-editar-produto')) {
            return;
        }

        document.getElementById('modal-editar-produto').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function abrirModalImportar() {
        document.getElementById('modal-importar-produto').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function fecharModalImportar(event = null) {
        if (event && event.target !== document.getElementById('modal-importar-produto')) {
            return;
        }

        document.getElementById('modal-importar-produto').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function mostrarMensagemPreview(texto, tipo = 'erro') {
        const box = document.getElementById('mensagem-preview');
        box.style.display = 'block';
        box.innerHTML = texto;
        box.className = 'preview-message';

        if (tipo === 'sucesso') {
            box.classList.add('preview-message-success');
        } else if (tipo === 'aviso') {
            box.classList.add('preview-message-warning');
        } else {
            box.classList.add('preview-message-error');
        }
    }

    function esconderMensagemPreview() {
        const box = document.getElementById('mensagem-preview');
        box.style.display = 'none';
        box.innerHTML = '';
        box.className = 'preview-message';
    }

    function limparPreviaImportacao() {
        document.getElementById('preview-container').style.display = 'none';
        document.getElementById('preview-body').innerHTML = '';
        document.getElementById('preview-resumo').innerHTML = '';
        esconderMensagemPreview();

        const btnImportar = document.getElementById('btn-importar-final');
        btnImportar.disabled = true;
        btnImportar.classList.add('btn-disabled');
    }

    function habilitarBotaoImportar() {
        const btnImportar = document.getElementById('btn-importar-final');
        btnImportar.disabled = false;
        btnImportar.classList.remove('btn-disabled');
    }

    function desabilitarBotaoImportar() {
        const btnImportar = document.getElementById('btn-importar-final');
        btnImportar.disabled = true;
        btnImportar.classList.add('btn-disabled');
    }

    function formatarPrecoBR(valor) {
        const numero = parseFloat(valor);
        if (isNaN(numero)) return valor;
        return numero.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function previsualizarArquivo() {
        limparPreviaImportacao();

        const input = document.getElementById('arquivo_csv');
        const arquivo = input.files[0];

        if (!arquivo) {
            mostrarMensagemPreview('Selecione um arquivo TXT ou CSV para pré-visualizar.');
            return;
        }

        const nomeArquivo = arquivo.name.toLowerCase();
        if (!nomeArquivo.endsWith('.csv') && !nomeArquivo.endsWith('.txt')) {
            mostrarMensagemPreview('Arquivo inválido. Envie um arquivo com extensão .txt ou .csv.');
            return;
        }

        const leitor = new FileReader();

        leitor.onload = function(e) {
            let conteudo = e.target.result || '';

            conteudo = conteudo.replace(/\r\n/g, '\n').replace(/\r/g, '\n').trim();

            if (!conteudo) {
                mostrarMensagemPreview('O arquivo está vazio.');
                return;
            }

            const linhas = conteudo.split('\n').filter(linha => linha.trim() !== '');

            if (linhas.length === 0) {
                mostrarMensagemPreview('Nenhuma linha válida encontrada no arquivo.');
                return;
            }

            const tbody = document.getElementById('preview-body');
            let totalValidas = 0;
            let totalInvalidas = 0;
            let html = '';

            linhas.forEach((linha, index) => {
                const numeroLinha = index + 1;
                const colunas = linha.split(';');

                if (colunas.length < 4) {
                    totalInvalidas++;
                    html += `
                        <tr class="preview-row-error">
                            <td class="preview-cell">${numeroLinha}</td>
                            <td colspan="4" class="preview-cell preview-error-text">
                                Linha inválida: esperado formato codigo;nome;preco;quantidade
                            </td>
                            <td class="preview-cell text-center">
                                <span class="status-badge status-badge-error">Inválida</span>
                            </td>
                        </tr>
                    `;
                    return;
                }

                const codigo = (colunas[0] || '').trim();
                const nome = (colunas[1] || '').trim();
                const preco = (colunas[2] || '').trim().replace(',', '.');
                const quantidade = (colunas[3] || '').trim();

                const codigoValido = codigo !== '';
                const nomeValido = nome !== '';
                const precoValido = preco !== '' && !isNaN(parseFloat(preco));
                const quantidadeValida = quantidade !== '' && !isNaN(parseInt(quantidade));

                const linhaValida = codigoValido && nomeValido && precoValido && quantidadeValida;

                if (linhaValida) {
                    totalValidas++;
                    html += `
                        <tr>
                            <td class="preview-cell">${numeroLinha}</td>
                            <td class="preview-cell preview-code">${codigo}</td>
                            <td class="preview-cell preview-name">${nome}</td>
                            <td class="preview-cell text-right">R$ ${formatarPrecoBR(preco)}</td>
                            <td class="preview-cell text-center">${quantidade}</td>
                            <td class="preview-cell text-center">
                                <span class="status-badge status-badge-ok">OK</span>
                            </td>
                        </tr>
                    `;
                } else {
                    totalInvalidas++;
                    let erros = [];
                    if (!codigoValido) erros.push('código');
                    if (!nomeValido) erros.push('nome');
                    if (!precoValido) erros.push('preço');
                    if (!quantidadeValida) erros.push('quantidade');

                    html += `
                        <tr class="preview-row-error">
                            <td class="preview-cell">${numeroLinha}</td>
                            <td class="preview-cell">${codigo || '-'}</td>
                            <td class="preview-cell">${nome || '-'}</td>
                            <td class="preview-cell text-right">${preco || '-'}</td>
                            <td class="preview-cell text-center">${quantidade || '-'}</td>
                            <td class="preview-cell text-center">
                                <span class="status-badge status-badge-error">
                                    Erro: ${erros.join(', ')}
                                </span>
                            </td>
                        </tr>
                    `;
                }
            });

            tbody.innerHTML = html;
            document.getElementById('preview-container').style.display = 'block';
            document.getElementById('preview-resumo').innerHTML = `Total de linhas: <strong>${linhas.length}</strong> | Válidas: <strong>${totalValidas}</strong> | Inválidas: <strong>${totalInvalidas}</strong>`;

            if (totalValidas > 0 && totalInvalidas === 0) {
                mostrarMensagemPreview('Pré-visualização carregada com sucesso. O arquivo está pronto para importação.', 'sucesso');
                habilitarBotaoImportar();
            } else if (totalValidas > 0 && totalInvalidas > 0) {
                mostrarMensagemPreview('Atenção: existem linhas inválidas. Corrija o arquivo antes de importar.', 'aviso');
                desabilitarBotaoImportar();
            } else {
                mostrarMensagemPreview('Nenhuma linha válida encontrada para importação.', 'erro');
                desabilitarBotaoImportar();
            }
        };

        leitor.onerror = function() {
            mostrarMensagemPreview('Não foi possível ler o arquivo selecionado.');
        };

        leitor.readAsText(arquivo, 'UTF-8');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-editar-produto').forEach(function (botao) {
            botao.addEventListener('click', function () {
                abrirModalEditarPorBotao(botao);
            });
        });

        const inputArquivo = document.getElementById('arquivo_csv');
        if (inputArquivo) {
            inputArquivo.addEventListener('change', function() {
                limparPreviaImportacao();
            });
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            fecharModalEditar();
            fecharModalImportar();
        }
    });

    @if($errors->any() && old('dados_importacao'))
        window.addEventListener('load', function() {
            abrirModalImportar();
        });
    @endif
</script>
@endsection
