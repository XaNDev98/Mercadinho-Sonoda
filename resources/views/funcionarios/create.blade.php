@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Cadastrar Funcionário</h2>

    @if($errors->any())
        <div style="margin-bottom:15px; padding:12px; border-radius:8px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca;">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('funcionarios.store') }}" enctype="multipart/form-data">
        @csrf

        <label>Foto (opcional)</label>
        <input type="file" name="foto" accept="image/*">

        <label>Nome</label>
        <input type="text" name="nome" required value="{{ old('nome') }}">

        <label>Número da Folha</label>
        <input type="text" name="numero_folha" required value="{{ old('numero_folha') }}">

        <label>Setor</label>
        <input type="text" name="cargo" value="{{ old('cargo') }}">

        <label>Senha Mercadinho (opcional)</label>
        <input type="text" name="senha_mercadinho" value="{{ old('senha_mercadinho') }}">

        <button type="submit">Salvar</button>
    </form>
</div>
@endsection
