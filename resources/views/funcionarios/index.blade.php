@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/funcionarios-index.css') }}">
@endpush

@section('content')
<div class="funcionarios-page">
    <div class="funcionarios-shell">

        <div class="funcionarios-header">
            <div>
                <h2 class="funcionarios-title">Funcionários</h2>
                <p class="funcionarios-subtitle">
                    Gerencie os colaboradores cadastrados no sistema.
                </p>
            </div>

            <div class="funcionarios-header-actions" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

                <div class="funcionarios-qty-badge">
                    Quantidade: {{ $funcionarios->count() }}
                </div>

            
                <a href="{{ route('funcionarios.create') }}"
                    style="
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            padding:8px;
                            border-radius:8px;
                            background:transparent;
                            color:#1e3a8a;
                            text-decoration:none;
                            transition:0.2s;
                    "
                    onmouseover="this.style.background='#eff6ff'; this.style.transform='scale(1.08)'"
                    onmouseout="this.style.background='transparent'; this.style.transform='scale(1)'">

    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
        viewBox="0 0 24 24" stroke="#1e3a8a" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 4v16m8-8H4" />
    </svg>

</a>

                {{-- BOTÃO SINCRONIZAR --}}
                <form method="POST" action="{{ route('funcionarios.sincronizar') }}"
                      onsubmit="return confirmarSincronizacao(this);" class="sync-form">
                    @csrf
                    <button id="btn-sincronizar" type="submit" class="btn-sync">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 4v5h.582M20 20v-5h-.581M5.8 9A7 7 0 0119 8m-.8 7A7 7 0 015 16" />
                        </svg>
                        Sincronizar
                    </button>
                </form>

            </div>
        </div>

        <div class="filtro-card">
            <form method="GET" action="{{ route('funcionarios.index') }}" class="filtro-form">
                <label for="banco_id" class="filtro-label">
                    Filtrar por banco
                </label>

                <select name="banco_id" id="banco_id" class="filtro-select">
                    <option value="">Todos os bancos</option>

                    @foreach($bancos as $banco)
                        @if($banco)
                            <option value="{{ $banco }}" {{ (string) $bancoSelecionado === (string) $banco ? 'selected' : '' }}>
                                Banco {{ $banco }}
                            </option>
                        @endif
                    @endforeach

                    <option value="manual" {{ $bancoSelecionado === 'manual' ? 'selected' : '' }}>
                        Cadastrados manualmente
                    </option>
                </select>

                <button type="submit" class="btn-filter">
                    Filtrar
                </button>

                <a href="{{ route('funcionarios.index') }}" class="btn-clear">
                    Limpar
                </a>
            </form>
        </div>

        @if(session('success'))
            <div class="alert-box alert-box--success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-box--error">
                {{ session('error') }}
            </div>
        @endif

        <div class="table-card">
            <div class="table-wrap">
                <table class="funcionarios-table">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>Banco</th>
                            <th>Número Folha</th>
                            <th>Setor</th> 
                            <th>Senha Mercadinho</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($funcionarios as $funcionario)
                            <tr>
                                <td>
                                    <div class="funcionario-colaborador">
                                        @if($funcionario->foto)
                                            <img src="{{ $funcionario->foto }}" class="funcionario-foto">
                                        @else
                                            <div class="funcionario-sem-foto">S/F</div>
                                        @endif

                                        <div class="funcionario-nome">
                                            {{ $funcionario->nome }}
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge-neutral">
                                        {{ $funcionario->banco_id ?: 'Manual' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge-blue">
                                        {{ $funcionario->numero_folha }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge-soft">
                                        {{ $funcionario->cargo ?? 'Não informado' }}
                                    </span>
                                </td>  

                                <td>
                                    <span class="badge-soft">
                                        {{ $funcionario->senha_mercadinho ?? 'Não informada' }}
                                    </span>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-row">
                                    Nenhum funcionário cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function confirmarSincronizacao(form) {
    const botao = document.getElementById('btn-sincronizar');

    botao.disabled = true;
    botao.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
            style="animation:girar 1s linear infinite;">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 4v5h.582M20 20v-5h-.581M5.8 9A7 7 0 0119 8m-.8 7A7 7 0 015 16" />
        </svg>
        Sincronizando...
    `;

    return true;
}
</script>
@endsection
