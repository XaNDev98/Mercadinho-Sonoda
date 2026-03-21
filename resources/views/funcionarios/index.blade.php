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

            <div class="funcionarios-header-actions">
                <div class="funcionarios-qty-badge">
                    Quantidade: {{ $funcionarios->count() }}
                </div>

                <form method="POST" action="{{ route('funcionarios.sincronizar') }}" onsubmit="return confirmarSincronizacao(this);" class="sync-form">
                    @csrf
                    <button id="btn-sincronizar" type="submit" class="btn-sync">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.8 9A7 7 0 0119 8m-.8 7A7 7 0 015 16" />
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

        @if(session('sync_output'))
            <div class="sync-output-card">
                <div class="sync-output-header">
                    Resultado da sincronização
                </div>

                <div class="sync-output-body">
                    @php
                        $sync = session('sync_output');
                    @endphp

                    <div class="sync-stats-grid">
                        <div class="sync-stat-box">
                            <div class="sync-stat-label">Status</div>
                            <div class="sync-stat-value">
                                {{ $sync['status'] ?? 'Finalizado' }}
                            </div>
                        </div>

                        <div class="sync-stat-box">
                            <div class="sync-stat-label">Funcionários sincronizados</div>
                            <div class="sync-stat-value">
                                {{ $sync['funcionarios_sincronizados'] ?? 0 }}
                            </div>
                        </div>

                        <div class="sync-stat-box">
                            <div class="sync-stat-label">Fotos recebidas</div>
                            <div class="sync-stat-value">
                                {{ $sync['fotos_recebidas'] ?? 0 }}
                            </div>
                        </div>
                    </div>

                    <div class="sync-console">
                        {{ $sync['texto'] ?? '' }}
                    </div>
                </div>
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
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($funcionarios as $funcionario)
                            <tr>
                                <td>
                                    <div class="funcionario-colaborador">
                                        @if($funcionario->foto)
                                            <img
                                                src="{{ $funcionario->foto }}"
                                                alt="Foto"
                                                class="funcionario-foto"
                                            >
                                        @else
                                            <div class="funcionario-sem-foto">
                                                S/F
                                            </div>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-row">
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
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="animation:girar 1s linear infinite;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.8 9A7 7 0 0119 8m-.8 7A7 7 0 015 16" />
            </svg>
            Sincronizando...
        `;

        return true;
    }
</script>
@endsection