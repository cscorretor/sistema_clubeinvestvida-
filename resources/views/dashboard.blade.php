<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel — Clube Investvida</title>
    <link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <style>
        .nav-disabled{display:flex;gap:11px;align-items:center;padding:9px 12px;border-radius:var(--r);font-size:14px;color:#718198;cursor:not-allowed}
        .welcome-grid{display:grid;grid-template-columns:2fr 1fr;gap:16px}
        .profile-line{display:flex;align-items:center;gap:12px}
        @media(max-width:760px){.welcome-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <img src="{{ asset('assets/brand/logo-simbolo-claro.svg') }}" width="34" height="34" alt="">
            <div><div class="bname">Clube Investvida</div><div class="btag">Corretora de Seguros</div></div>
        </div>
        <nav class="nav" aria-label="Navegação principal">
            <a href="{{ route('dashboard') }}" class="on" aria-current="page"><span class="ico">▦</span> Dashboard</a>
            <a href="{{ route('clientes.index') }}"><span class="ico">◉</span> Clientes</a>
            <span class="nav-disabled"><span class="ico">❤</span> Apólices</span>
            <span class="nav-disabled"><span class="ico">◔</span> Leads / CRM</span>
            <span class="nav-disabled"><span class="ico">◷</span> Chamados</span>
            <span class="nav-disabled"><span class="ico">$</span> Financeiro</span>
            <span class="nav-disabled"><span class="ico">⛁</span> Cofre Digital</span>
            <span class="nav-disabled"><span class="ico">⚙</span> Configurações</span>
        </nav>
        <div class="foot">Ambiente de homologação</div>
    </aside>

    <div class="content">
        <header class="topbar">
            <img src="{{ asset('assets/brand/logo-horizontal.svg') }}" alt="Clube Investvida — Corretora de Seguros" style="height:30px">
            <div class="avatar mla" title="{{ auth()->user()->nome }}">{{ mb_strtoupper(mb_substr(auth()->user()->nome, 0, 2)) }}</div>
        </header>

        <main class="main">
            <div class="mb16">
                <h1 class="h1">Dashboard</h1>
                <p class="lead">Bem-vindo ao sistema de gestão da Clube Investvida.</p>
            </div>

            <div class="welcome-grid">
                <section class="card">
                    <div class="profile-line">
                        <div class="avatar" style="width:48px;height:48px;font-size:15px">{{ mb_strtoupper(mb_substr(auth()->user()->nome, 0, 2)) }}</div>
                        <div>
                            <h2 class="h2">Autenticação concluída</h2>
                            <p class="lead">Olá, {{ auth()->user()->nome }}. Seu perfil é <strong>{{ auth()->user()->perfil }}</strong>.</p>
                        </div>
                    </div>
                    <div class="alert alert-ok mt16">✓ Conexão segura e sessão autenticada.</div>
                </section>

                <section class="card">
                    <h2 class="h2">Acesso rápido</h2>
                    <p class="lead mb16">Continue pelos módulos já ligados ao banco.</p>
                    <a href="{{ route('clientes.index') }}" class="btn btn-primary btn-block">Consultar clientes</a>
                    @can('create', App\Models\Cliente::class)
                        <a href="{{ route('clientes.create') }}" class="btn btn-action btn-block mt8">Cadastrar cliente</a>
                    @endcan
                </section>
            </div>

            <section class="card mt16">
                <div class="between wrap">
                    <div>
                        <h2 class="h2">Próximos módulos</h2>
                        <p class="lead">Apólices, leads, chamados, financeiro e cofre serão ligados gradualmente aos dados reais.</p>
                    </div>
                    <span class="chip chip-info">Em desenvolvimento</span>
                </div>
            </section>

            <form method="POST" action="{{ url('/logout') }}" class="mt16">
                @csrf
                <button type="submit" class="btn btn-ghost">Sair com segurança</button>
            </form>
        </main>
    </div>
</div>
</body>
</html>
