<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Mercadinho Interno</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="brand">
                <div class="brand-badge">M</div>
                <h1>Mercadinho Interno</h1>
                <p>Faça login para acessar o sistema</p>
            </div>

            @if(session('success'))
                <div class="success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="error">
                    @foreach($errors->all() as $erro)
                        <div>{{ $erro }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>

                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>