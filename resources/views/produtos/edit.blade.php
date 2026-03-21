@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Editar Produto</h2>

    @if($errors->any())
        <div style="margin-bottom:15px; padding:12px; border-radius:8px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca;">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('produtos.update', $produto->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="margin-bottom:16px;">
            <label>Foto atual</label>
            <div style="margin-top:8px;">
                @if(!empty($produto->imagem))
                    <img
                        src="{{ asset('storage/' . $produto->imagem) }}"
                        alt="{{ $produto->nome }}"
                        style="width:90px; height:90px; object-fit:cover; border-radius:10px; border:1px solid #ddd;"
                    >
                @else
                    <div style="width:90px; height:90px; display:flex; align-items:center; justify-content:center; border:1px solid #ddd; border-radius:10px; background:#f3f4f6; color:#6b7280; font-size:14px;">
                        S/I
                    </div>
                @endif
            </div>
        </div>

        <label>Nova foto</label>
        <input type="file" name="imagem" accept="image/*">

        <label>Nome</label>
        <input type="text" name="nome" required value="{{ old('nome', $produto->nome) }}">

        <label>Categoria</label>
        <select name="categoria_id">
            <option value="">Selecione a categoria</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" {{ old('categoria_id', $produto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                    {{ $categoria->nome }}
                </option>
            @endforeach
        </select>

        <label>Preço</label>
        <input type="number" step="0.01" name="preco" required min="0" value="{{ old('preco', $produto->preco) }}">

        <label>Quantidade</label>
        <input type="number" name="estoque" required min="0" value="{{ old('estoque', $produto->estoque) }}">

        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px;">
            <button type="submit">Salvar alterações</button>

            <a href="{{ route('produtos.index') }}">
                <button type="button" style="background:#6b7280;">Cancelar</button>
            </a>
        </div>
    </form>
</div>
@endsection