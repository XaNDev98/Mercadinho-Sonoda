<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercadinho Interno</title> 
    <link rel="icon" type="image/png" href="{{ asset('logo-integracao.png') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('logo-integracao.png') }}?v=1">
    <link rel="apple-touch-icon" href="{{ asset('logo-integracao.png') }}?v=1"> 
    @stack('styles')

@php
    $rotaAtual = request()->route() ? request()->route()->getName() : '';
    $estaNoKiosk = $rotaAtual === 'kiosk';
    $modoFixo = session('kiosk_modo_fixo', false);
    $ocultarTopoKiosk = $estaNoKiosk && $modoFixo;
@endphp

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --azul: #2563eb;
            --azul-escuro: #1d4ed8;
            --azul-claro: #eff6ff;
            --verde: #16a34a;
            --vermelho: #dc2626;
            --texto: #0f172a;
            --texto-suave: #64748b;
            --borda: #e2e8f0;
            --fundo: #f8fafc;
            --branco: #ffffff;
            --sombra: 0 12px 30px rgba(15, 23, 42, 0.08);
            --radius: 18px;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: Arial, sans-serif;
            background:
                radial-gradient(circle at top left, #dbeafe 0%, transparent 30%),
                radial-gradient(circle at top right, #e0f2fe 0%, transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
            color: var(--texto);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.92);
            border-bottom: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .topbar-inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .brand-badge {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--azul) 0%, var(--azul-escuro) 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 900;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.28);
            flex-shrink: 0;
        }

        .brand-text {
            min-width: 0;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 22px;
            line-height: 1.1;
            font-weight: 900;
            color: var(--texto);
        }

        .brand-text p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: var(--texto-suave);
            font-weight: 600;
        }

        .menu-toggle {
            display: none;
            width: 46px;
            height: 46px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--azul) 0%, var(--azul-escuro) 100%);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.22);
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
        }

        .menu-toggle svg {
            width: 22px;
            height: 22px;
        }

        .menu {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .menu a,
        .logout-form button,
        .kiosk-icon-btn {
            text-decoration: none;
            color: var(--texto);
            background: #ffffff;
            border: 1px solid var(--borda);
            padding: 11px 16px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 14px;
            transition: 0.22s ease;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .menu a:hover,
        .logout-form button:hover,
        .kiosk-icon-btn:hover {
            transform: translateY(-1px);
            background: var(--azul);
            color: #fff;
            border-color: var(--azul);
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.22);
        }

        .logout-form {
            margin: 0;
        }

        .logout-form button {
            cursor: pointer;
        }

        .kiosk-icon-form {
            margin: 0;
        }

        .kiosk-icon-btn {
            width: 44px;
            min-width: 44px;
            padding: 0;
            cursor: pointer;
        }

        .kiosk-icon-btn svg {
            width: 20px;
            height: 20px;
        }

        .container {
            max-width: 1240px;
            margin: 26px auto;
            padding: 0 20px 30px 20px;
        }

        .container--kiosk {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        .page-header {
            margin-bottom: 18px;
        }

        .page-header h2 {
            margin: 0 0 6px 0;
            font-size: 30px;
            font-weight: 900;
            color: var(--texto);
        }

        .page-header p {
            margin: 0;
            color: var(--texto-suave);
            font-size: 15px;
            font-weight: 500;
        }

        .card {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            padding: 24px;
            box-shadow: var(--sombra);
            margin-bottom: 20px;
        }

        input,
        select,
        button,
        textarea {
            width: 100%;
            padding: 13px 14px;
            margin-top: 6px;
            margin-bottom: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            transition: 0.18s ease;
            background: #fff;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--azul);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 800;
            color: #334155;
            margin-bottom: 4px;
        }

        button {
            background: linear-gradient(135deg, var(--azul) 0%, var(--azul-escuro) 100%);
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.22);
        }

        button:hover {
            transform: translateY(-1px);
            opacity: 0.98;
        }

        .success {
            background: linear-gradient(180deg, #ecfdf5 0%, #dcfce7 100%);
            color: #166534;
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 18px;
            font-weight: 800;
            border: 1px solid #bbf7d0;
            box-shadow: 0 8px 18px rgba(22, 163, 74, 0.08);
        }

        .error {
            background: linear-gradient(180deg, #fff1f2 0%, #fee2e2 100%);
            color: #991b1b;
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 18px;
            font-weight: 800;
            border: 1px solid #fecaca;
            box-shadow: 0 8px 18px rgba(220, 38, 38, 0.08);
        }

        @media (max-width: 992px) {
            .topbar-inner {
                align-items: center;
                flex-wrap: wrap;
            }
        }

        @media (max-width: 768px) {
            .topbar {
                position: relative;
            }

            .topbar-inner {
                padding: 14px;
                flex-wrap: wrap;
                gap: 14px;
            }

            .brand {
                flex: 1;
                min-width: 0;
            }

            .brand-badge {
                width: 44px;
                height: 44px;
                border-radius: 14px;
                font-size: 20px;
            }

            .brand-text h1 {
                font-size: 19px;
            }

            .brand-text p {
                font-size: 12px;
            }

            .menu-toggle {
                display: inline-flex;
                width: 46px;
                flex: 0 0 46px;
            }

            .menu {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
                padding-top: 4px;
            }

            .menu.is-open {
                display: flex;
            }

            .menu a,
            .logout-form,
            .logout-form button,
            .kiosk-icon-form,
            .kiosk-icon-btn {
                width: 100%;
            }

            .kiosk-icon-btn {
                padding: 11px 16px;
                justify-content: center;
            }

            .container {
                padding: 0 14px 24px 14px;
                margin: 18px auto;
            }

            .card {
                padding: 18px;
                border-radius: 20px;
            }

            .page-header h2 {
                font-size: 25px;
            }
        }

        @media (max-width: 520px) {
            .container {
                padding: 0 12px 20px 12px;
            }

            .brand-text h1 {
                font-size: 17px;
            }

            .brand-text p {
                font-size: 11px;
            }

            .page-header h2 {
                font-size: 22px;
            }
        } 
         
            /* PADRÃO (MOBILE E TABLET) */
            .footer-logo {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                margin-top: 40px;
                padding-bottom: 10px;
                gap: 4px;
            }

            .footer-logo img {
                width: 110px;
                object-fit: contain;
                opacity: 0.7;
            }

            .footer-logo p {
                font-size: 12px;
                color: #64748b;
                font-weight: 600;
                margin: 0;
            }

            /* DESKTOP (FIXO EMBAIXO) */
            @media (min-width: 1024px) {
                .footer-logo {
                    position: fixed;
                    bottom: 8px;
                    left: 0;
                    width: 100%;
                    margin-top: 0;
                    pointer-events: none;
                }
            }
    </style>
</head>
<body>

@if(session()->get('secullum_autenticado') && !$ocultarTopoKiosk)
    <div class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-badge">M</div>
                <div class="brand-text">
                    <h1>Mercadinho Interno</h1>
                    <p>Controle de retiradas, produtos e colaboradores</p>
                </div>
            </div>

            <button class="menu-toggle" type="button" aria-label="Abrir menu" onclick="toggleMobileMenu()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <nav class="menu" id="mobileMenu">
                <a href="{{ route('retiradas.create') }}">Nova Retirada</a>
                <a href="{{ route('retiradas.index') }}">Histórico</a>
                <a href="{{ route('funcionarios.index') }}">Funcionários</a>
                <a href="{{ route('produtos.index') }}">Produtos</a>

              @if(!$modoFixo)
                <form method="POST" action="{{ route('kiosk.ativar') }}" class="kiosk-icon-form">
                    @csrf
                    <button type="submit" class="kiosk-icon-btn" title="Ativar modo kiosk" aria-label="Ativar modo kiosk">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 10-9 0V10.5m-1.5 0h12a1.5 1.5 0 011.5 1.5v7.5A1.5 1.5 0 0118 21H6a1.5 1.5 0 01-1.5-1.5V12A1.5 1.5 0 016 10.5z" />
                        </svg>
                    </button>
                </form>
            @endif

                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button type="submit">Sair</button>
                </form>
            </nav>
        </div>
    </div>
@endif

<div class="container {{ $ocultarTopoKiosk ? 'container--kiosk' : '' }}">
    @if(session('success') && !$ocultarTopoKiosk)
        <div class="success">{{ session('success') }}</div>
    @endif

    @if(session('error') && !$ocultarTopoKiosk)
        <div class="error">{{ session('error') }}</div>
    @endif

    @yield('content')
</div>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        if (menu) {
            menu.classList.toggle('is-open');
        }
    }

    window.addEventListener('resize', function () {
        const menu = document.getElementById('mobileMenu');
        if (window.innerWidth > 768 && menu) {
            menu.classList.remove('is-open');
        }
    });
</script>


    <!-- FOOTER -->
    <div class="footer-logo">
        <img src="{{ asset('logo-integracao.png') }}" alt="Integração">
        <p>Desenvolvimento por Técnicos</p>
    </div>
</div>
</body>
</html>
