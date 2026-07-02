<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação em duas etapas — Clube Investvida</title>
    <link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <style>
        .auth{display:grid;grid-template-columns:1.05fr .95fr;min-height:100vh}
        .brandcol{background:linear-gradient(160deg,#062A4A,#031B33);color:#fff;padding:44px;display:flex;flex-direction:column;justify-content:space-between}
        .formcol{display:flex;align-items:center;justify-content:center;padding:28px}
        .auth-box{width:100%;max-width:390px}.otp{display:flex;gap:8px}.otp .input{min-width:0;text-align:center;font-size:18px}
        .recovery-panel[hidden],.authenticator-panel[hidden]{display:none}
        @media(max-width:760px){.auth{grid-template-columns:1fr}.brandcol{display:none}}
    </style>
</head>
<body>
<h1 class="sr-only">Verificação em duas etapas</h1>
<div class="auth">
    <aside class="brandcol">
        <div class="row center gap8">
            <img src="{{ asset('assets/brand/logo-simbolo-claro.svg') }}" width="40" height="40" alt="">
            <strong style="font-family:Manrope;font-size:17px">Clube Investvida</strong>
        </div>
        <div>
            <p style="font-family:Manrope;font-weight:800;font-size:30px;line-height:1.15;margin:0">Mais uma camada<br>de proteção.</p>
            <p style="color:#9FB4CC;margin-top:12px;max-width:22rem">Confirme que é você usando o código do aplicativo autenticador.</p>
        </div>
        <div class="small" style="color:#7E93AC">© {{ date('Y') }} Clube Investvida Corretora de Seguros</div>
    </aside>

    <main class="formcol">
        <div class="auth-box">
            <img src="{{ asset('assets/brand/logo-horizontal.svg') }}" alt="Clube Investvida — Corretora de Seguros" style="height:44px;margin-bottom:24px;max-width:100%">
            <h2 class="h1" style="font-size:22px">Verificação em duas etapas</h2>

            @if ($errors->any())
                <div role="alert" class="alert alert-error mt16">O código informado não é válido.</div>
            @endif

            <form id="two-factor-form" method="POST" action="{{ url('/two-factor-challenge') }}" class="mt16">
                @csrf
                <div id="authenticator-panel" class="authenticator-panel">
                    <p class="lead mb16">Digite o código de 6 dígitos do seu app autenticador.</p>
                    <div class="otp mb16" id="otp" role="group" aria-label="Código de autenticação"></div>
                    <input id="code" name="code" type="hidden">
                </div>

                <div id="recovery-panel" class="recovery-panel field" hidden>
                    <label class="label" for="recovery_code">Código de recuperação</label>
                    <input class="input" id="recovery_code" name="recovery_code" type="text" disabled autocomplete="one-time-code" placeholder="xxxxx-xxxxx">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Confirmar</button>
                <button type="button" id="toggle-recovery" class="btn btn-ghost btn-block mt8">Usar um código de recuperação</button>
                <a href="{{ url('/login') }}" class="btn btn-ghost btn-block mt8">‹ Voltar ao login</a>
            </form>
        </div>
    </main>
</div>
<script>
const form = document.getElementById('two-factor-form');
const otp = document.getElementById('otp');
const code = document.getElementById('code');
const recovery = document.getElementById('recovery_code');
const toggle = document.getElementById('toggle-recovery');
let usingRecovery = false;

for (let i = 0; i < 6; i++) {
    const input = document.createElement('input');
    input.type = 'text';
    input.maxLength = 1;
    input.inputMode = 'numeric';
    input.autocomplete = i === 0 ? 'one-time-code' : 'off';
    input.className = 'input';
    input.setAttribute('aria-label', `Dígito ${i + 1}`);
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '');
        if (input.value && input.nextElementSibling) input.nextElementSibling.focus();
    });
    input.addEventListener('keydown', event => {
        if (event.key === 'Backspace' && !input.value && input.previousElementSibling) input.previousElementSibling.focus();
    });
    otp.appendChild(input);
}

form.addEventListener('submit', event => {
    if (!usingRecovery) {
        code.value = Array.from(otp.querySelectorAll('input')).map(input => input.value).join('');
        if (!/^\d{6}$/.test(code.value)) {
            event.preventDefault();
            otp.querySelector('input').focus();
        }
    }
});

toggle.addEventListener('click', () => {
    usingRecovery = !usingRecovery;
    document.getElementById('authenticator-panel').hidden = usingRecovery;
    document.getElementById('recovery-panel').hidden = !usingRecovery;
    code.disabled = usingRecovery;
    recovery.disabled = !usingRecovery;
    toggle.textContent = usingRecovery ? 'Usar o aplicativo autenticador' : 'Usar um código de recuperação';
    (usingRecovery ? recovery : otp.querySelector('input')).focus();
});

otp.querySelector('input').focus();
</script>
</body>
</html>
