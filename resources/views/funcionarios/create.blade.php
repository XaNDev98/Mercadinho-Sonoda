@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Cadastrar Funcionário</h2>

    <form method="POST" action="{{ route('funcionarios.store') }}">
        @csrf

        <label>Nome</label>
        <input type="text" name="nome" required>

        <label>Número da Folha</label>
        <input type="text" name="numero_folha" required>

        <label>Setor</label>
        <input type="text" name="setor">

        <button type="submit">Salvar</button>
    </form>
</div>
@endsection