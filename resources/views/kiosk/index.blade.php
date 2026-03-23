@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/retiradas-create.css') }}">
<style>
    .kiosk-lock-floating {
        position: fixed;
        right: 14px;
        bottom: 14px;
        z-index: 9999;
    }

    .kiosk-lock-btn {
        width: 42px;
        height: 42px;
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.78);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.28);
        margin: 0;
        padding: 0;
    }

    .kiosk-lock-btn svg {
        width: 17px;
        height: 17px;
        color: #fff;
    }

    .kiosk-unlock-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.35);
        z-index: 10000;
        display: none;
    }

    .kiosk-unlock-modal {
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: min(92vw, 360px);
        background: #fff;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22);
        z-index: 10001;
        display: none;
    }

    .kiosk-unlock-title {
        margin: 0 0 8px 0;
        font-size: 20px;
        font-weight: 900;
        color: #0f172a;
        text-align: center;
    }

    .kiosk-unlock-text {
        margin: 0 0 16px 0;
        font-size: 14px;
        color: #64748b;
        text-align: center;
    }

    .kiosk-unlock-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .kiosk-unlock-actions button {
        margin: 0;
    }

    .kiosk-btn-light {
        background: #e2e8f0 !important;
        color: #0f172a !important;
        box-shadow: none !important;
    }

    .kiosk-only .retirada-top {
        display: none !important;
    }

    .kiosk-only {
        min-height: 100vh;
        height: 100vh;
        padding: 10px 10px 72px 10px;
        overflow: hidden;
    }

    .kiosk-only .retirada-shell {
        max-width: 100%;
        height: calc(100vh - 82px);
        display: flex;
        flex-direction: column;
    }

    html.kiosk-html,
    body.kiosk-body {
        width: 100%;
        height: 100%;
        overflow: hidden;
        overscroll-behavior: none;
        touch-action: manipulation;
    }

    .senha-box {
        margin-top: 16px;
        padding: 14px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .senha-box label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: #0f172a;
    }

    .senha-box input {
        width: 100%;
        height: 46px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 15px;
        outline: none;
    }

    .senha-box input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    @media (max-width: 768px) {
        .kiosk-only {
            padding: 12px 12px 76px 12px;
        }

        .kiosk-lock-btn {
            width: 40px;
            height: 40px;
        }
    }
</style>
@endpush

@section('content')
@php
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

    $modoFixo = session('kiosk_modo_fixo', false);
@endphp

<div class="retirada-page {{ $modoFixo ? 'kiosk-only' : '' }}">
    <div class="retirada-shell">
        <div class="retirada-top">
            <div>
                <h2 class="retirada-title">Nova Retirada</h2>
                <p class="retirada-subtitle">
                    Busque o colaborador, selecione os produtos e confirme o pedido.
                </p>
            </div>

            <div class="retirada-badge">
                Painel de Pedido
            </div>
        </div>

        <div id="mensagem-erro" class="alert-box alert-box--error" style="display:none;"></div>
        <div id="mensagem-sucesso" class="alert-box alert-box--success" style="display:none;"></div>

        @if(session('error'))
            <div class="alert-inline alert-inline--error">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert-inline alert-inline--success">
                {{ session('success') }}
            </div>
        @endif

        <div class="retirada-grid">
            <div class="panel-card">
                <h3 class="panel-title">Identificação</h3>

                <label for="numero_folha_busca" class="form-label">
                    Número da Folha
                </label>

                <div class="search-row">
                    <input
                        type="text"
                        id="numero_folha_busca"
                        placeholder="Digite o número"
                        class="input-main"
                    >

                    <button
                        type="button"
                        onclick="buscarFuncionario()"
                        title="Buscar colaborador"
                        class="icon-btn icon-btn--blue"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="M20 20L17 17"></path>
                        </svg>
                    </button>
                </div>

                <button
                    type="button"
                    onclick="buscarFuncionario()"
                    class="btn-dark btn-block"
                >
                    Buscar colaborador
                </button>

                <div id="funcionario-info" class="funcionario-box" style="display:none;">
                    <div class="funcionario-top">
                        <div class="funcionario-foto-wrap">
                            <img
                                id="funcionario-foto"
                                src=""
                                alt="Foto do colaborador"
                                class="funcionario-foto"
                                style="display:none;"
                            >

                            <div id="funcionario-sem-foto" class="funcionario-sem-foto">
                                S/F
                            </div>
                        </div>

                        <div class="funcionario-dados">
                            <p class="funcionario-linha">
                                <strong>Nome:</strong><br>
                                <span id="funcionario-nome"></span>
                            </p>
                            <p class="funcionario-linha">
                                <strong>Folha:</strong> <span id="funcionario-folha"></span>
                            </p>
                            <p class="funcionario-linha">
                                <strong>Cargo:</strong> <span id="funcionario-cargo"></span>
                            </p>
                        </div>
                    </div>

                    <div class="funcionario-action">
                        <button
                            type="button"
                            onclick="mostrarProdutos()"
                            class="btn-success btn-block"
                        >
                            Liberar produtos
                        </button>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <form method="POST" action="{{ route('retiradas.store') }}" id="form-retirada" style="display:none;">
                    @csrf
                    <input type="hidden" name="numero_folha" id="numero_folha_hidden">
                    <input type="hidden" name="senha_mercadinho" id="senha_mercadinho_hidden">

                    <div class="produtos-header">
                        <h3 class="panel-title panel-title--no-margin">Produtos</h3>

                        <div class="produtos-filtros">
                            <select
                                id="filtro-categoria"
                                onchange="filtrarProdutos()"
                                class="input-filter"
                            >
                                <option value="">Todas as categorias</option>
                                @foreach($categoriasUnicas as $categoria)
                                    <option value="{{ $categoria }}">{{ $categoria }}</option>
                                @endforeach
                            </select>

                            <input
                                type="text"
                                id="busca-produto"
                                placeholder="Buscar produto..."
                                oninput="filtrarProdutos()"
                                class="input-filter"
                            >
                        </div>
                    </div>

                    <div id="itens-area" class="produtos-grid">
                        @foreach($produtos as $produto)
                            @php
                                $imagemProduto = !empty($produto->imagem) ? trim($produto->imagem) : '';

                                $nomeCategoria = '';
                                if (isset($produto->categoria) && is_object($produto->categoria) && !empty($produto->categoria->nome)) {
                                    $nomeCategoria = $produto->categoria->nome;
                                } elseif (!empty($produto->categoria) && is_string($produto->categoria)) {
                                    $nomeCategoria = $produto->categoria;
                                }
                            @endphp

                            <div
                                class="produto-card"
                                data-categoria="{{ strtolower($nomeCategoria) }}"
                                data-nome="{{ strtolower($produto->nome) }}"
                                data-nome-original="{{ $produto->nome }}"
                                data-preco="{{ $produto->preco }}"
                            >
                                <div class="produto-top">
                                    <div class="produto-image-wrap">
                                        @if($imagemProduto !== '')
                                            <img
                                                src="{{ asset('storage/' . $imagemProduto) }}"
                                                alt="{{ $produto->nome }}"
                                                class="produto-imagem"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                            >
                                            <div class="produto-sem-imagem" style="display:none;">
                                                S/I
                                            </div>
                                        @else
                                            <div class="produto-sem-imagem">
                                                S/I
                                            </div>
                                        @endif
                                    </div>

                                    <div class="produto-info">
                                        <div class="produto-info-header">
                                            <p class="produto-nome">
                                                {{ $produto->nome }}
                                            </p>

                                            @if($nomeCategoria !== '')
                                                <span class="produto-categoria">
                                                    {{ $nomeCategoria }}
                                                </span>
                                            @endif
                                        </div>

                                        <p class="produto-preco">
                                            R$ {{ number_format($produto->preco, 2, ',', '.') }}
                                        </p>

                                        <p class="produto-estoque">
                                            Estoque: {{ $produto->estoque }}
                                        </p>
                                    </div>
                                </div>

                                <div class="produto-bottom">
                                    <label class="form-label">
                                        Quantidade
                                    </label>

                                    <div class="quantidade-row">
                                        <button
                                            type="button"
                                            onclick="alterarQuantidade({{ $loop->index }}, -1, {{ $produto->estoque }})"
                                            class="qty-btn qty-btn--light"
                                        >
                                            -
                                        </button>

                                        <input
                                            type="number"
                                            id="quantidade_{{ $loop->index }}"
                                            name="itens[{{ $loop->index }}][quantidade]"
                                            min="0"
                                            max="{{ $produto->estoque }}"
                                            value="0"
                                            inputmode="numeric"
                                            oninput="atualizarResumo()"
                                            class="qty-input"
                                        >

                                        <button
                                            type="button"
                                            onclick="alterarQuantidade({{ $loop->index }}, 1, {{ $produto->estoque }})"
                                            class="qty-btn qty-btn--blue"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <input type="hidden" name="itens[{{ $loop->index }}][produto_id]" value="{{ $produto->id }}">
                                    <input type="hidden" class="produto-nome-hidden" value="{{ $produto->nome }}">
                                    <input type="hidden" class="produto-preco-hidden" value="{{ $produto->preco }}">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="confirmar-wrap">
                        <button
                            type="button"
                            onclick="abrirConfirmacao()"
                            class="btn-success btn-confirmar"
                        >
                            Confirmar pedido
                        </button>
                    </div>

                    <div id="confirmacao-overlay" class="confirmacao-overlay" style="display:none;"></div>

                    <div id="confirmacao-modal" class="confirmacao-modal" style="display:none;">
                        <div class="confirmacao-header">
                            <h3 class="confirmacao-title">Confirmar retirada</h3>

                            <button
                                type="button"
                                onclick="fecharConfirmacao()"
                                class="confirmacao-close"
                            >
                                ×
                            </button>
                        </div>

                        <div class="confirmacao-funcionario-box">
                            <p><strong>Colaborador:</strong> <span id="confirmacao-funcionario"></span></p>
                            <p><strong>Folha:</strong> <span id="confirmacao-folha"></span></p>
                        </div>

                        <div class="confirmacao-table-wrap">
                            <table class="confirmacao-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qtd</th>
                                        <th class="text-right">Valor</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="confirmacao-itens"></tbody>
                            </table>
                        </div>

                        <div class="senha-box">
                            <label for="senha_mercadinho_modal">Senha do Mercadinho</label>
                            <input
                                type="password"
                                id="senha_mercadinho_modal"
                                placeholder="Digite a senha para finalizar"
                                autocomplete="off"
                            >
                        </div>

                        <div class="confirmacao-footer">
                            <div class="confirmacao-total">
                                Total: <span id="confirmacao-total">R$ 0,00</span>
                            </div>

                            <div class="confirmacao-actions">
                                <button
                                    type="button"
                                    onclick="fecharConfirmacao()"
                                    class="btn-light"
                                >
                                    Voltar
                                </button>

                                <button
                                    type="button"
                                    onclick="finalizarRetiradaComSenha()"
                                    class="btn-success"
                                >
                                    Finalizar retirada
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="resumo-card">
                <h3 class="resumo-title">Resumo do Pedido</h3>

                <div id="resumo-vazio" class="resumo-vazio">
                    Nenhum item selecionado.
                </div>

                <div id="resumo-conteudo" class="resumo-conteudo" style="display:none;">
                    <div id="resumo-itens" class="resumo-itens"></div>

                    <div class="resumo-footer">
                        <div class="resumo-linha">
                            <span>Itens:</span>
                            <strong id="resumo-quantidade-itens">0</strong>
                        </div>

                        <div class="resumo-total">
                            <span>Total</span>
                            <span id="resumo-total">R$ 0,00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($modoFixo)
    <div class="kiosk-lock-floating">
        <button type="button" class="kiosk-lock-btn" onclick="abrirDesbloqueio()" title="Desativar modo kiosk">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 10-9 0V10.5m-1.5 0h12a1.5 1.5 0 011.5 1.5v7.5A1.5 1.5 0 0118 21H6a1.5 1.5 0 01-1.5-1.5V12A1.5 1.5 0 016 10.5z" />
            </svg>
        </button>
    </div>

    <div id="kioskUnlockOverlay" class="kiosk-unlock-overlay" onclick="fecharDesbloqueio()"></div>

    <div id="kioskUnlockModal" class="kiosk-unlock-modal">
        <h3 class="kiosk-unlock-title">Desativar trava tela :)</h3>
        <p class="kiosk-unlock-text">Digite a senha para voltar ao painel administrativo.</p>

        <form method="POST" action="{{ route('kiosk.desativar') }}">
            @csrf
            <input
                type="password"
                name="senha"
                id="kioskSenha"
                placeholder="Digite a senha"
                autocomplete="off"
            >

            <div class="kiosk-unlock-actions">
                <button type="button" class="kiosk-btn-light" onclick="fecharDesbloqueio()">Cancelar</button>
                <button type="submit">Desativar</button>
            </div>
        </form>
    </div>
@endif

<script>
    const MODO_KIOSK_ATIVO = @json($modoFixo);

    function limparMensagens() {
        const erroBox = document.getElementById('mensagem-erro');
        const sucessoBox = document.getElementById('mensagem-sucesso');

        if (erroBox) {
            erroBox.style.display = 'none';
            erroBox.innerText = '';
        }

        if (sucessoBox) {
            sucessoBox.style.display = 'none';
            sucessoBox.innerText = '';
        }
    }

    function mostrarErro(texto) {
        const erroBox = document.getElementById('mensagem-erro');
        if (!erroBox) return;

        erroBox.innerText = texto;
        erroBox.style.display = 'block';
    }

    function mostrarSucesso(texto) {
        const sucessoBox = document.getElementById('mensagem-sucesso');
        if (!sucessoBox) return;

        sucessoBox.innerText = texto;
        sucessoBox.style.display = 'block';
    }

    function mostrarSemFoto() {
        const foto = document.getElementById('funcionario-foto');
        const semFoto = document.getElementById('funcionario-sem-foto');

        if (foto) {
            foto.style.display = 'none';
            foto.src = '';
        }

        if (semFoto) {
            semFoto.style.display = 'flex';
        }
    }

    function mostrarFoto(src) {
        const foto = document.getElementById('funcionario-foto');
        const semFoto = document.getElementById('funcionario-sem-foto');

        if (!foto) return;

        foto.onerror = function () {
            mostrarSemFoto();
        };

        foto.src = src;
        foto.style.display = 'block';

        if (semFoto) {
            semFoto.style.display = 'none';
        }
    }

    function limparQuantidades() {
        const inputs = document.querySelectorAll('.produto-card input[type="number"]');

        inputs.forEach(input => {
            input.value = 0;
        });
    }

    function resetarFuncionario() {
        const info = document.getElementById('funcionario-info');
        const form = document.getElementById('form-retirada');
        const nome = document.getElementById('funcionario-nome');
        const folha = document.getElementById('funcionario-folha');
        const cargo = document.getElementById('funcionario-cargo');
        const hiddenFolha = document.getElementById('numero_folha_hidden');
        const hiddenSenha = document.getElementById('senha_mercadinho_hidden');
        const senhaModal = document.getElementById('senha_mercadinho_modal');

        if (info) info.style.display = 'none';
        if (form) form.style.display = 'none';
        if (nome) nome.innerText = '';
        if (folha) folha.innerText = '';
        if (cargo) cargo.innerText = '';
        if (hiddenFolha) hiddenFolha.value = '';
        if (hiddenSenha) hiddenSenha.value = '';
        if (senhaModal) senhaModal.value = '';

        mostrarSemFoto();
        limparQuantidades();
        atualizarResumo();
        fecharConfirmacao();
    }

    async function buscarFuncionario() {
        const numeroFolhaInput = document.getElementById('numero_folha_busca');
        const infoBox = document.getElementById('funcionario-info');
        const form = document.getElementById('form-retirada');

        const numeroFolha = numeroFolhaInput ? numeroFolhaInput.value.trim() : '';

        limparMensagens();
        resetarFuncionario();

        if (!numeroFolha) {
            mostrarErro('Digite o número da folha.');
            return;
        }

        try {
            const response = await fetch(`/funcionarios/buscar/${encodeURIComponent(numeroFolha)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            let data = null;

            try {
                data = await response.json();
            } catch (e) {
                mostrarErro('Resposta inválida ao buscar colaborador.');
                return;
            }

            if (!response.ok || !data.success) {
                mostrarErro(data.message || 'Colaborador não encontrado.');
                return;
            }

            const funcionario = data.funcionario || {};

            const nomeEl = document.getElementById('funcionario-nome');
            const folhaEl = document.getElementById('funcionario-folha');
            const cargoEl = document.getElementById('funcionario-cargo');
            const hiddenFolha = document.getElementById('numero_folha_hidden');

            if (nomeEl) nomeEl.innerText = funcionario.nome ?? '';
            if (folhaEl) folhaEl.innerText = funcionario.numero_folha ?? '';
            if (cargoEl) cargoEl.innerText = funcionario.cargo ?? 'Não informado';
            if (hiddenFolha) hiddenFolha.value = funcionario.numero_folha ?? '';

            const fotoFuncionario = typeof funcionario.foto === 'string'
                ? funcionario.foto.trim()
                : '';

            if (fotoFuncionario !== '') {
                mostrarFoto(fotoFuncionario);
            } else {
                mostrarSemFoto();
            }

            if (infoBox) infoBox.style.display = 'block';
            if (form) form.style.display = 'none';

            mostrarSucesso('Colaborador encontrado com sucesso.');
        } catch (error) {
            mostrarErro('Erro ao buscar colaborador.');
        }
    }

    function mostrarProdutos() {
        limparMensagens();

        const form = document.getElementById('form-retirada');
        if (!form) return;

        form.style.display = 'block';
        form.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    function alterarQuantidade(index, variacao, estoqueMaximo) {
        const input = document.getElementById(`quantidade_${index}`);
        if (!input) return;

        let valorAtual = parseInt(input.value || 0, 10);

        if (isNaN(valorAtual)) {
            valorAtual = 0;
        }

        valorAtual += variacao;

        if (valorAtual < 0) valorAtual = 0;
        if (valorAtual > estoqueMaximo) valorAtual = estoqueMaximo;

        input.value = valorAtual;
        atualizarResumo();
    }

    function filtrarProdutos() {
        const filtroCategoria = document.getElementById('filtro-categoria');
        const buscaProduto = document.getElementById('busca-produto');
        const cards = document.querySelectorAll('.produto-card');

        const categoria = filtroCategoria ? filtroCategoria.value.trim().toLowerCase() : '';
        const busca = buscaProduto ? buscaProduto.value.trim().toLowerCase() : '';

        cards.forEach(card => {
            const categoriaCard = (card.dataset.categoria || '').toLowerCase();
            const nomeCard = (card.dataset.nome || '').toLowerCase();

            const categoriaOk = categoria === '' || categoriaCard === categoria;
            const nomeOk = busca === '' || nomeCard.includes(busca);

            card.style.display = (categoriaOk && nomeOk) ? '' : 'none';
        });
    }

    function formatarReal(valor) {
        const numero = Number(valor || 0);

        return numero.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function obterDadosProduto(card) {
        const inputQuantidade = card.querySelector('input[type="number"]');
        const inputNomeHidden = card.querySelector('.produto-nome-hidden');
        const inputPrecoHidden = card.querySelector('.produto-preco-hidden');
        const inputProdutoId = card.querySelector('input[name*="[produto_id]"]');

        const quantidade = parseInt(inputQuantidade?.value || 0, 10) || 0;
        const nome = (card.dataset.nomeOriginal || inputNomeHidden?.value || '').trim();

        let preco = parseFloat(card.dataset.preco || inputPrecoHidden?.value || 0);
        if (isNaN(preco)) preco = 0;

        const produtoId = inputProdutoId ? inputProdutoId.value : '';

        return {
            quantidade,
            nome,
            preco,
            produtoId
        };
    }

    function atualizarResumo() {
        const cards = document.querySelectorAll('.produto-card');
        const resumoVazio = document.getElementById('resumo-vazio');
        const resumoConteudo = document.getElementById('resumo-conteudo');
        const resumoItens = document.getElementById('resumo-itens');
        const resumoTotal = document.getElementById('resumo-total');
        const resumoQuantidadeItens = document.getElementById('resumo-quantidade-itens');

        if (!resumoItens || !resumoTotal || !resumoQuantidadeItens || !resumoVazio || !resumoConteudo) {
            return;
        }

        resumoItens.innerHTML = '';

        let total = 0;
        let itensSelecionados = 0;

        cards.forEach(card => {
            const { quantidade, nome, preco } = obterDadosProduto(card);

            if (quantidade > 0) {
                const subtotal = quantidade * preco;
                total += subtotal;
                itensSelecionados += quantidade;

                const itemResumo = document.createElement('div');
                itemResumo.className = 'resumo-item';
                itemResumo.innerHTML = `
                    <div>
                        <div class="resumo-item__nome">${nome}</div>
                        <div class="resumo-item__desc">${quantidade} x ${formatarReal(preco)}</div>
                    </div>
                    <div class="resumo-item__valor">${formatarReal(subtotal)}</div>
                `;

                resumoItens.appendChild(itemResumo);
            }
        });

        resumoQuantidadeItens.innerText = itensSelecionados;
        resumoTotal.innerText = formatarReal(total);

        if (itensSelecionados > 0) {
            resumoVazio.style.display = 'none';
            resumoConteudo.style.display = 'block';
        } else {
            resumoVazio.style.display = 'block';
            resumoConteudo.style.display = 'none';
        }
    }

    function abrirConfirmacao() {
        limparMensagens();

        const cards = document.querySelectorAll('.produto-card');
        const tbody = document.getElementById('confirmacao-itens');
        const nomeFuncionarioEl = document.getElementById('funcionario-nome');
        const folhaFuncionarioEl = document.getElementById('funcionario-folha');
        const numeroFolhaHidden = document.getElementById('numero_folha_hidden');
        const senhaModal = document.getElementById('senha_mercadinho_modal');

        if (!numeroFolhaHidden || !numeroFolhaHidden.value) {
            mostrarErro('Busque um colaborador antes de confirmar.');
            return;
        }

        if (!tbody) return;

        tbody.innerHTML = '';

        let total = 0;
        let temItens = false;

        cards.forEach(card => {
            const { quantidade, nome, preco } = obterDadosProduto(card);

            if (quantidade > 0) {
                temItens = true;

                const subtotal = quantidade * preco;
                total += subtotal;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="confirmacao-cell">${nome}</td>
                    <td class="confirmacao-cell text-center">${quantidade}</td>
                    <td class="confirmacao-cell text-right">${formatarReal(preco)}</td>
                    <td class="confirmacao-cell text-right confirmacao-strong">${formatarReal(subtotal)}</td>
                `;
                tbody.appendChild(tr);
            }
        });

        if (!temItens) {
            mostrarErro('Selecione pelo menos um produto com quantidade maior que zero.');
            return;
        }

        const nomeFuncionario = nomeFuncionarioEl ? nomeFuncionarioEl.innerText.trim() : '';
        const folhaFuncionario = folhaFuncionarioEl ? folhaFuncionarioEl.innerText.trim() : '';

        const confirmacaoFuncionario = document.getElementById('confirmacao-funcionario');
        const confirmacaoFolha = document.getElementById('confirmacao-folha');
        const confirmacaoTotal = document.getElementById('confirmacao-total');
        const overlay = document.getElementById('confirmacao-overlay');
        const modal = document.getElementById('confirmacao-modal');

        if (confirmacaoFuncionario) confirmacaoFuncionario.innerText = nomeFuncionario;
        if (confirmacaoFolha) confirmacaoFolha.innerText = folhaFuncionario;
        if (confirmacaoTotal) confirmacaoTotal.innerText = formatarReal(total);
        if (overlay) overlay.style.display = 'block';
        if (modal) modal.style.display = 'block';
        if (senhaModal) {
            senhaModal.value = '';
            setTimeout(() => senhaModal.focus(), 100);
        }
    }

    function finalizarRetiradaComSenha() {
        const senha = document.getElementById('senha_mercadinho_modal');
        const senhaHidden = document.getElementById('senha_mercadinho_hidden');
        const form = document.getElementById('form-retirada');

        const valorSenha = senha ? senha.value.trim() : '';

        if (!valorSenha) {
            mostrarErro('Digite a senha do mercadinho para finalizar.');
            return;
        }

        if (senhaHidden) senhaHidden.value = valorSenha;
        if (form) form.submit();
    }

    function fecharConfirmacao() {
        const overlay = document.getElementById('confirmacao-overlay');
        const modal = document.getElementById('confirmacao-modal');

        if (overlay) overlay.style.display = 'none';
        if (modal) modal.style.display = 'none';
    }

    function abrirDesbloqueio() {
        const overlay = document.getElementById('kioskUnlockOverlay');
        const modal = document.getElementById('kioskUnlockModal');
        const input = document.getElementById('kioskSenha');

        if (overlay) overlay.style.display = 'block';
        if (modal) modal.style.display = 'block';

        setTimeout(() => {
            if (input) input.focus();
        }, 100);
    }

    function fecharDesbloqueio() {
        const overlay = document.getElementById('kioskUnlockOverlay');
        const modal = document.getElementById('kioskUnlockModal');
        const input = document.getElementById('kioskSenha');

        if (overlay) overlay.style.display = 'none';
        if (modal) modal.style.display = 'none';
        if (input) input.value = '';
    }

    function aplicarModoTelaCheiaVisual() {
        document.documentElement.classList.add('kiosk-html');
        document.body.classList.add('kiosk-body');
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
        window.scrollTo(0, 1);
    }

    async function tentarFullscreenAutomatico() {
        if (!MODO_KIOSK_ATIVO) return;
        if (document.fullscreenElement) return;

        const el = document.documentElement;

        try {
            if (el.requestFullscreen) {
                await el.requestFullscreen();
            } else if (el.webkitRequestFullscreen) {
                await el.webkitRequestFullscreen();
            } else if (el.msRequestFullscreen) {
                await el.msRequestFullscreen();
            }
        } catch (e) {
        }
    }

    function ativarFullscreenNoPrimeiroToque() {
        if (!MODO_KIOSK_ATIVO) return;

        const entrar = async () => {
            if (!document.fullscreenElement) {
                await tentarFullscreenAutomatico();
            }

            document.removeEventListener('touchstart', entrar);
            document.removeEventListener('mousedown', entrar);
            document.removeEventListener('click', entrar);
        };

        document.addEventListener('touchstart', entrar, { passive: true });
        document.addEventListener('mousedown', entrar);
        document.addEventListener('click', entrar);
    }

    document.addEventListener('DOMContentLoaded', async function () {
        const numeroFolhaBusca = document.getElementById('numero_folha_busca');

        if (numeroFolhaBusca) {
            numeroFolhaBusca.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    buscarFuncionario();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                fecharConfirmacao();
                fecharDesbloqueio();
            }
        });

        if (MODO_KIOSK_ATIVO) {
            aplicarModoTelaCheiaVisual();

            setTimeout(() => {
                tentarFullscreenAutomatico();
            }, 200);

            setTimeout(() => {
                tentarFullscreenAutomatico();
            }, 800);

            setTimeout(() => {
                tentarFullscreenAutomatico();
            }, 1500);

            ativarFullscreenNoPrimeiroToque();
        }

        atualizarResumo();
    });
</script>
@endsection
