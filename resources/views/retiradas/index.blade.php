@extends('layouts.app')

@section('content')
<div style="max-width: 1400px; margin: 24px auto; padding: 20px;">
    <div style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-radius: 24px; padding: 24px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.10); border: 1px solid #e5e7eb;">

        <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:24px;">
            <div>
                <h2 style="margin:0; font-size:36px; font-weight:900; color:#0f172a;">
                    Histórico de Retiradas
                </h2>
                <p style="margin:6px 0 0 0; color:#64748b; font-size:15px;">
                    Consulte os pedidos realizados com filtros por colaborador e período.
                </p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <div style="padding:10px 16px; border-radius:14px; background:#eff6ff; color:#1d4ed8; font-weight:800; border:1px solid #bfdbfe;">
                    Painel de Histórico
                </div>

                <button
                    type="button"
                    onclick="abrirModalDestaques()"
                    style="padding:10px 16px; border-radius:14px; background:#f59e0b; color:#fff; font-weight:800; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(245, 158, 11, 0.25);"
                >
                    Destaques
                </button>
            </div>
        </div>

        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:22px; padding:20px; box-shadow:0 8px 22px rgba(15, 23, 42, 0.04); margin-bottom:20px;">
            <form method="GET" action="{{ route('retiradas.index') }}">
                <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:end; margin-bottom:16px;">

                    <div style="min-width:260px; flex:1;">
                        <label style="display:block; margin-bottom:8px; font-weight:800; color:#334155;">
                            Nome do funcionário
                        </label>
                        <input
                            type="text"
                            name="nome"
                            value="{{ request('nome') }}"
                            placeholder="Digite o nome"
                            style="width:100%; padding:14px 16px; border:1px solid #cbd5e1; border-radius:14px; font-size:15px; outline:none;"
                        >
                    </div>

                    <div style="min-width:200px;">
                        <label style="display:block; margin-bottom:8px; font-weight:800; color:#334155;">
                            Data inicial
                        </label>
                        <input
                            type="date"
                            name="data_inicio"
                            value="{{ request('data_inicio') }}"
                            style="width:100%; padding:14px 16px; border:1px solid #cbd5e1; border-radius:14px; font-size:15px; outline:none;"
                        >
                    </div>

                    <div style="min-width:200px;">
                        <label style="display:block; margin-bottom:8px; font-weight:800; color:#334155;">
                            Data final
                        </label>
                        <input
                            type="date"
                            name="data_fim"
                            value="{{ request('data_fim') }}"
                            style="width:100%; padding:14px 16px; border:1px solid #cbd5e1; border-radius:14px; font-size:15px; outline:none;"
                        >
                    </div>
                </div>

                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <button
                        type="submit"
                        style="display:flex; align-items:center; justify-content:center; gap:6px;
                               width:130px; height:42px;
                               border:none; border-radius:10px;
                               background:#2563eb; color:#fff;
                               font-size:14px; font-weight:800;
                               cursor:pointer; box-shadow:0 4px 12px rgba(37, 99, 235, 0.25);"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
                        </svg>
                        Filtrar
                    </button>

                    <a
                        href="{{ route('retiradas.index') }}"
                        style="display:flex; align-items:center; justify-content:center; gap:6px;
                               width:130px; height:42px;
                               border-radius:10px;
                               background:#e2e8f0; color:#0f172a;
                               font-size:14px; font-weight:800;
                               text-decoration:none;"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"></path>
                            <path d="M8 6V4h8v2"></path>
                            <path d="M19 6l-1 14H6L5 6"></path>
                        </svg>
                        Limpar
                    </a>

                    <button
                        type="button"
                        onclick="baixarTXT()"
                        style="display:flex; align-items:center; justify-content:center; gap:6px;
                               width:130px; height:42px;
                               border:none; border-radius:10px;
                               background:#16a34a; color:#fff;
                               font-size:14px; font-weight:800;
                               cursor:pointer; box-shadow:0 4px 12px rgba(22, 163, 74, 0.25);"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 3v12"></path>
                            <path d="M8 11l4 4 4-4"></path>
                            <path d="M5 21h14"></path>
                        </svg>
                        Baixar
                    </button>
                </div>
            </form>
        </div>

        @if(session('success'))
            <div style="margin-bottom:18px; padding:14px 16px; border-radius:14px; background:#dcfce7; color:#166534; border:1px solid #bbf7d0; font-weight:700;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="margin-bottom:18px; padding:14px 16px; border-radius:14px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; font-weight:700;">
                {{ session('error') }}
            </div>
        @endif

        @forelse($retiradas as $retirada)
            <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:22px; padding:20px; margin-bottom:18px; box-shadow:0 8px 22px rgba(15, 23, 42, 0.04);">
                <div style="display:flex; justify-content:space-between; gap:18px; flex-wrap:wrap; align-items:center;">
                    <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
                        <div style="width:78px; height:78px; flex-shrink:0;">
                            @if(!empty(optional($retirada->funcionario)->foto))
                                <img
                                    src="{{ $retirada->funcionario->foto }}"
                                    alt="{{ $retirada->funcionario->nome }}"
                                    style="width:78px; height:78px; object-fit:cover; border-radius:50%; border:2px solid #d1d5db; background:#fff;"
                                >
                            @else
                                <div style="width:78px; height:78px; border-radius:50%; background:#e2e8f0; border:2px solid #d1d5db; display:flex; align-items:center; justify-content:center; color:#475569; font-size:18px; font-weight:900;">
                                    S/F
                                </div>
                            @endif
                        </div>

                        <div>
                            <p style="margin:0 0 6px 0; font-size:20px; color:#0f172a; font-weight:900;">
                                {{ $retirada->funcionario->nome ?? 'Funcionário não encontrado' }}
                            </p>
                            <p style="margin:0 0 4px 0; color:#475569; font-size:15px;">
                            <strong>Folha:</strong> {{ $retirada->numero_folha }}
                            </p>
                            <p style="margin:0 0 4px 0; color:#475569; font-size:15px;">
                                <strong>Pedidos:</strong> {{ $retirada->total_pedidos }}
                            </p>
                            <p style="margin:0; color:#475569; font-size:15px;">
                                <strong>Data/Hora:</strong> {{ \Carbon\Carbon::parse($retirada->data_hora)->format('d/m/Y H:i:s') }}
                            </p>
                        </div>
                    </div>

                    <div style="min-width:200px; padding:14px 18px; border-radius:18px; background:#0f172a; color:#fff; text-align:center;">
                        <div style="font-size:13px; color:#cbd5e1; margin-bottom:6px; font-weight:700;">
                            Total do Pedido
                        </div>
                        <div style="font-size:28px; font-weight:900;">
                            R$ {{ number_format($retirada->valor_total, 2, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div style="margin-top:18px; overflow:auto; border:1px solid #e5e7eb; border-radius:18px;">
                    <table style="width:100%; border-collapse:collapse; min-width:720px;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="padding:14px 16px; text-align:left; color:#334155; font-size:14px; font-weight:900;">Foto</th>
                                <th style="padding:14px 16px; text-align:left; color:#334155; font-size:14px; font-weight:900;">Item</th>
                                <th style="padding:14px 16px; text-align:center; color:#334155; font-size:14px; font-weight:900;">Qtd</th>
                                <th style="padding:14px 16px; text-align:right; color:#334155; font-size:14px; font-weight:900;">Valor Unitário</th>
                                <th style="padding:14px 16px; text-align:right; color:#334155; font-size:14px; font-weight:900;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($retirada->itens as $item)
    @php
        $imagemProduto = !empty($item->imagem) ? trim($item->imagem) : '';
    @endphp

    <tr style="border-top:1px solid #e5e7eb;">
        <td style="padding:14px 16px;">
            @if($imagemProduto !== '')
                <img
                    src="{{ asset('storage/' . $imagemProduto) }}"
                    alt="{{ $item->nome ?? 'Produto' }}"
                    style="width:58px; height:58px; object-fit:cover; border-radius:12px; border:1px solid #d1d5db; background:#fff;"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                >
                <div style="display:none; width:58px; height:58px; border-radius:12px; background:#f1f5f9; border:1px solid #d1d5db; align-items:center; justify-content:center; color:#64748b; font-size:12px; font-weight:800;">
                    S/I
                </div>
            @else
                <div style="width:58px; height:58px; border-radius:12px; background:#f1f5f9; border:1px solid #d1d5db; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:12px; font-weight:800;">
                    S/I
                </div>
            @endif
        </td>

        <td style="padding:14px 16px; color:#0f172a; font-weight:800;">
            {{ $item->nome ?? 'Produto não encontrado' }}
        </td>

        <td style="padding:14px 16px; text-align:center;">
            <span style="display:inline-flex; min-width:44px; justify-content:center; padding:8px 10px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-weight:900;">
                {{ $item->quantidade }}
            </span>
        </td>

        <td style="padding:14px 16px; text-align:right; color:#334155; font-weight:700;">
            R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}
        </td>

        <td style="padding:14px 16px; text-align:right; color:#0f172a; font-weight:900;">
            R$ {{ number_format($item->subtotal, 2, ',', '.') }}
        </td>
    </tr>
@endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div style="padding:28px; border:1px solid #e5e7eb; border-radius:22px; background:#ffffff; color:#64748b; text-align:center; box-shadow:0 8px 22px rgba(15, 23, 42, 0.04);">
                <div style="font-size:18px; font-weight:800; color:#334155; margin-bottom:6px;">
                    Nenhuma retirada encontrada
                </div>
                <div style="font-size:14px;">
                    Ajuste os filtros e tente novamente.
                </div>
            </div>
        @endforelse
    </div>
</div>

<div
    id="modal-destaques"
    onclick="fecharModalDestaques(event)"
    style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:9999; padding:20px; overflow:auto;"
>
    <div
        style="max-width:1100px; margin:40px auto; background:#ffffff; border-radius:24px; box-shadow:0 25px 70px rgba(0,0,0,0.30); border:1px solid #e5e7eb; overflow:hidden;"
        onclick="event.stopPropagation()"
    >
        <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; padding:22px 24px; background:linear-gradient(90deg, #f59e0b 0%, #f97316 100%); color:#fff;">
            <div>
                <h3 style="margin:0; font-size:28px; font-weight:900;">Destaques do Período</h3>
                <p style="margin:6px 0 0 0; font-size:14px; color:rgba(255,255,255,0.92);">
                    Top 3 compradores e top 3 itens com base nos filtros selecionados.
                </p>
            </div>

            <button
                type="button"
                onclick="fecharModalDestaques()"
                style="width:42px; height:42px; border:none; border-radius:12px; background:rgba(255,255,255,0.18); color:#fff; font-size:22px; font-weight:900; cursor:pointer;"
            >
                ×
            </button>
        </div>

        <div style="padding:24px;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:20px;">
                <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:20px; padding:20px; box-shadow:0 8px 22px rgba(15, 23, 42, 0.04);">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                        <div style="width:42px; height:42px; border-radius:12px; background:#dcfce7; color:#16a34a; display:flex; align-items:center; justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div style="font-size:22px; font-weight:900; color:#0f172a;">
                            Top 3 Compradores
                        </div>
                    </div>

                    @forelse($topCompradores as $index => $comprador)
                        <div style="display:flex; gap:14px; align-items:center; padding:14px 0; {{ !$loop->last ? 'border-bottom:1px solid #e5e7eb;' : '' }}">
                            <div style="width:38px; height:38px; border-radius:999px; background:#16a34a; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:900; flex-shrink:0;">
                                {{ $index + 1 }}
                            </div>

                            @if(!empty($comprador->foto))
                                <img
                                    src="{{ $comprador->foto }}"
                                    alt="{{ $comprador->nome }}"
                                    style="width:56px; height:56px; object-fit:cover; border-radius:50%; border:2px solid #d1d5db; background:#fff; flex-shrink:0;"
                                >
                            @else
                                <div style="width:56px; height:56px; border-radius:50%; background:#e2e8f0; border:2px solid #d1d5db; display:flex; align-items:center; justify-content:center; color:#475569; font-size:12px; font-weight:900; flex-shrink:0;">
                                    S/F
                                </div>
                            @endif

                            <div style="flex:1;">
                                <div style="font-size:16px; font-weight:900; color:#0f172a;">
                                    {{ $comprador->nome }}
                                </div>
                                <div style="font-size:13px; color:#475569; margin-top:3px;">
                                    <strong>Folha:</strong> {{ $comprador->numero_folha }}
                                </div>
                                <div style="font-size:13px; color:#475569; margin-top:3px;">
                                    <strong>Pedidos:</strong> {{ (int) $comprador->total_pedidos }}
                                </div>
                                <div style="font-size:15px; color:#16a34a; font-weight:900; margin-top:4px;">
                                    R$ {{ number_format((float) $comprador->total_gasto, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding:18px; border-radius:16px; background:#f8fafc; color:#64748b; border:1px dashed #cbd5e1;">
                            Nenhum comprador encontrado para os filtros selecionados.
                        </div>
                    @endforelse
                </div>

                <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:20px; padding:20px; box-shadow:0 8px 22px rgba(15, 23, 42, 0.04);">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                        <div style="width:42px; height:42px; border-radius:12px; background:#dbeafe; color:#2563eb; display:flex; align-items:center; justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M3 3h18v4H3z"></path>
                                <path d="M5 7h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7z"></path>
                                <path d="M9 12h6"></path>
                            </svg>
                        </div>
                        <div style="font-size:22px; font-weight:900; color:#0f172a;">
                            Top 3 Itens
                        </div>
                    </div>

                    @forelse($topItens as $index => $itemTop)
                        @php
                            $imagemTop = !empty($itemTop->imagem) ? trim($itemTop->imagem) : '';
                        @endphp

                        <div style="display:flex; gap:14px; align-items:center; padding:14px 0; {{ !$loop->last ? 'border-bottom:1px solid #e5e7eb;' : '' }}">
                            <div style="width:38px; height:38px; border-radius:999px; background:#2563eb; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:900; flex-shrink:0;">
                                {{ $index + 1 }}
                            </div>

                            @if($imagemTop !== '')
                                <img
                                    src="{{ asset('storage/' . $imagemTop) }}"
                                    alt="{{ $itemTop->nome }}"
                                    style="width:56px; height:56px; object-fit:cover; border-radius:14px; border:1px solid #d1d5db; background:#fff; flex-shrink:0;"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >
                                <div style="display:none; width:56px; height:56px; border-radius:14px; background:#f1f5f9; border:1px solid #d1d5db; align-items:center; justify-content:center; color:#64748b; font-size:12px; font-weight:800; flex-shrink:0;">
                                    S/I
                                </div>
                            @else
                                <div style="width:56px; height:56px; border-radius:14px; background:#f1f5f9; border:1px solid #d1d5db; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:12px; font-weight:800; flex-shrink:0;">
                                    S/I
                                </div>
                            @endif

                            <div style="flex:1;">
                                <div style="font-size:16px; font-weight:900; color:#0f172a;">
                                    {{ $itemTop->nome }}
                                </div>
                                <div style="font-size:13px; color:#475569; margin-top:3px;">
                                    <strong>Quantidade:</strong> {{ (int) $itemTop->total_quantidade }}
                                </div>
                                <div style="font-size:15px; color:#2563eb; font-weight:900; margin-top:4px;">
                                    R$ {{ number_format((float) $itemTop->total_valor, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="padding:18px; border-radius:16px; background:#f8fafc; color:#64748b; border:1px dashed #cbd5e1;">
                            Nenhum item encontrado para os filtros selecionados.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function baixarTXT() {
        const dataInicio = document.querySelector('[name="data_inicio"]').value;
        const dataFim = document.querySelector('[name="data_fim"]').value;
        const nome = document.querySelector('[name="nome"]').value;

        if (!dataInicio || !dataFim) {
            alert('Selecione a data inicial e a data final antes de baixar o TXT.');
            return;
        }

        let url = `{{ route('retiradas.exportar') }}?data_inicio=${dataInicio}&data_fim=${dataFim}`;

        if (nome) {
            url += `&nome=${encodeURIComponent(nome)}`;
        }

        window.location.href = url;
    }

    function abrirModalDestaques() {
        document.getElementById('modal-destaques').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function fecharModalDestaques(event = null) {
        if (event && event.target !== document.getElementById('modal-destaques')) {
            return;
        }

        document.getElementById('modal-destaques').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('modal-destaques');
            if (modal && modal.style.display === 'block') {
                fecharModalDestaques();
            }
        }
    });
</script>
@endsection