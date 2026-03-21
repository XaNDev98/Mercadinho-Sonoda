@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Cadastrar Produto</h2>

    @if(session('success'))
        <div style="margin-bottom:15px; padding:12px; border-radius:8px; background:#dcfce7; color:#166534; border:1px solid #bbf7d0;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom:15px; padding:12px; border-radius:8px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca;">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="nova-categoria-box" style="display:none; margin-bottom:20px; padding:15px; border:1px solid #ddd; border-radius:10px; background:#f9fafb;">
        <form method="POST" action="{{ route('categorias.store') }}">
            @csrf
            <label>Nome da nova categoria</label>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="text" name="nome" placeholder="Digite a categoria" style="min-width:250px;" required>
                <button
                    type="submit"
                    style="padding:10px 14px; border:none; border-radius:8px; background:#16a34a; color:#fff; font-weight:700; cursor:pointer;"
                >
                    Salvar Categoria
                </button>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('produtos.store') }}" enctype="multipart/form-data">
        @csrf

        <label>Foto</label>
        <input type="file" name="imagem" accept="image/*">

        <label>Quantidade</label>
        <input type="number" name="estoque" required min="0" value="{{ old('estoque') }}">

        <label>Nome</label>
        <input type="text" name="nome" required value="{{ old('nome') }}">

        <label>Categoria</label>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:10px;">
            <select name="categoria_id" style="min-width:260px;" required>
                <option value="">Selecione a categoria</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nome }}
                    </option>
                @endforeach
            </select>

            <button
                type="button"
                onclick="toggleNovaCategoria()"
                style="padding:10px 14px; border:none; border-radius:8px; background:#2563eb; color:#fff; font-weight:700; cursor:pointer;"
            >
                + Nova Categoria
            </button>
        </div>

        <label>Preço</label>
        <input type="number" step="0.01" name="preco" required min="0" value="{{ old('preco') }}">

        <button type="submit">Salvar</button>
    </form>
</div>

<script>
    function toggleNovaCategoria() {
        const box = document.getElementById('nova-categoria-box');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }
</script>
@endsection