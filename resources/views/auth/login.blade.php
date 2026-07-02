<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — Clube Investvida</title>
    <link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/apple-touch-icon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <style>
        .auth{display:grid;grid-template-columns:1.05fr .95fr;min-height:100vh}
        .brandcol{background:linear-gradient(160deg,#062A4A,#031B33);color:#fff;padding:44px;display:flex;flex-direction:column;justify-content:space-between}
        .formcol{display:flex;align-items:center;justify-content:center;padding:28px}
        .auth-title{font-family:Manrope,system-ui,sans-serif;font-weight:800;font-size:30px;line-height:1.15;margin:0}
        .auth-box{width:100%;max-width:360px}
        .remember{display:flex;align-items:center;gap:6px}
        @media(max-width:760px){.auth{grid-template-columns:1fr}.brandcol{display:none}}
    </style>
</head>
<body>
<h1 class="sr-only">Login do sistema Clube Investvida</h1>
<div class="auth">
    <aside class="brandcol">
        <div class="row center gap8">
            <img src="{{ asset('assets/brand/logo-simbolo-claro.svg') }}" width="40" height="40" alt="">
            <strong style="font-family:Manrope;font-size:17px">Clube Investvida</strong>
        </div>
        <div>
            <p class="auth-title">Proteção e patrimônio,<br>com gestão inteligente.</p>
            <p style="color:#9FB4CC;margin-top:12px;max-width:22rem">Sistema de gestão para seguros de pessoas — Vida, Previdência, Saúde, Viagem e Renda.</p>
        </div>
        <div class="small" style="color:#7E93AC">© {{ date('Y') }} Clube Investvida Corretora de Seguros</div>
    </aside>

    <main class="formcol">
        <div class="auth-box">
            <img src="{{ asset('assets/brand/logo-horizontal.svg') }}" alt="Clube Investvida — Corretora de Seguros" style="height:44px;margin-bottom:24px;max-width:100%">
            <h2 class="h1" style="font-size:22px">Entrar</h2>
            <p class="lead" style="margin-bottom:18px">Acesse o painel da corretora.</p>

            @if ($errors->any())
                <div role="alert" class="alert alert-error mb16">E-mail ou senha inválidos. Verifique os dados e tente novamente.</div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="field">
                    <label class="label" for="email">E-mail</label>
                    <input class="input" id="email" name="email" type="email" autocomplete="username" required autofocus
                           placeholder="voce@clubeinvestvida.com" value="{{ old('email') }}">
                </div>
                <div class="field">
                    <label class="label" for="password">Senha</label>
                    <input class="input" id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••">
                </div>
                <div class="between small mb16">
                    <label class="remember muted"><input name="remember" type="checkbox" value="1"> Lembrar</label>
                    <span class="faint">Recuperação pelo administrador</span>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Entrar</button>
            </form>

            <p class="small faint mt16" style="text-align:center">Protegido por verificação em duas etapas (2FA).</p>
        </div>
    </main>
</div>
</body>
</html>
