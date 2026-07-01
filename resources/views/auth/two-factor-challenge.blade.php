<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação em duas etapas — Clube Investvida</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {theme: {extend: {colors: {navy: "#003461", orange: "#FF6B00"}, fontFamily: {head: ["Manrope"], body: ["Inter"]}}}};
    </script>
    <style>
        body{font-family:"Inter",sans-serif;background:#F4F7F9;color:#1A1C1E}.font-head{font-family:"Manrope",sans-serif}
        .inp{border:1px solid #E2E8F0;border-radius:.5rem;padding:.5rem .7rem;background:#fff}
        .inp:focus{outline:none;border-color:#003461;box-shadow:0 0 0 3px rgba(0,75,135,.12)}
        .bgn{background:linear-gradient(135deg,#003461,#00284c)}
    </style>
</head>
<body>
<div class="min-h-screen grid md:grid-cols-2">
    <div class="bgn text-white hidden md:flex flex-col justify-between p-10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-orange flex items-center justify-center font-head font-bold text-lg">CI</div>
            <div class="font-head font-bold text-lg">Clube Investvida</div>
        </div>
        <div>
            <h1 class="font-head font-bold text-3xl leading-tight">Mais uma camada<br>de proteção.</h1>
            <p class="text-blue-200 mt-3 text-sm max-w-sm">Confirme que é você usando o código do aplicativo autenticador.</p>
        </div>
        <div class="text-blue-300 text-xs">© 2026 Clube Investvida Corretora de Seguros</div>
    </div>

    <main class="flex items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <div class="md:hidden flex items-center gap-2 mb-6">
                <div class="w-9 h-9 rounded-md bg-navy text-white flex items-center justify-center font-head font-bold">CI</div>
                <span class="font-head font-bold text-navy">Clube Investvida</span>
            </div>

            <h2 class="font-head font-bold text-2xl text-navy">Verificação em duas etapas</h2>

            @if ($errors->any())
                <div role="alert" class="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    O código informado não é válido.
                </div>
            @endif

            <form id="two-factor-form" method="POST" action="{{ url('/two-factor-challenge') }}" class="space-y-4 mt-6">
                @csrf
                <div id="authenticator-panel">
                    <p class="text-sm text-slate-600 mb-4">Digite o código de 6 dígitos do seu app autenticador.</p>
                    <div class="flex gap-2 justify-between" id="otp" aria-label="Código de autenticação"></div>
                    <input id="code" name="code" type="hidden">
                </div>

                <div id="recovery-panel" class="hidden">
                    <label for="recovery_code" class="text-xs font-semibold text-slate-600">Código de recuperação</label>
                    <input id="recovery_code" name="recovery_code" type="text" disabled autocomplete="one-time-code"
                           class="inp w-full mt-1" placeholder="xxxxx-xxxxx">
                </div>

                <button type="submit" class="w-full bg-navy text-white py-2.5 rounded-md font-semibold hover:bg-[#00284c]">
                    Confirmar
                </button>
                <button type="button" id="toggle-recovery" class="w-full text-slate-500 text-sm">
                    Usar um código de recuperação
                </button>
                <a href="{{ url('/login') }}" class="block w-full text-center text-slate-500 text-sm">‹ Voltar ao login</a>
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
        input.pattern = '[0-9]';
        input.className = 'otp-digit inp w-11 text-center text-lg';
        input.setAttribute('aria-label', `Dígito ${i + 1}`);
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '');
            if (input.value && input.nextElementSibling) input.nextElementSibling.focus();
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'Backspace' && !input.value && input.previousElementSibling) {
                input.previousElementSibling.focus();
            }
        });
        otp.appendChild(input);
    }

    form.addEventListener('submit', event => {
        if (!usingRecovery) {
            code.value = Array.from(document.querySelectorAll('.otp-digit')).map(input => input.value).join('');
            if (!/^\d{6}$/.test(code.value)) {
                event.preventDefault();
                document.querySelector('.otp-digit').focus();
            }
        }
    });

    toggle.addEventListener('click', () => {
        usingRecovery = !usingRecovery;
        document.getElementById('authenticator-panel').classList.toggle('hidden', usingRecovery);
        document.getElementById('recovery-panel').classList.toggle('hidden', !usingRecovery);
        code.disabled = usingRecovery;
        recovery.disabled = !usingRecovery;
        toggle.textContent = usingRecovery ? 'Usar o aplicativo autenticador' : 'Usar um código de recuperação';
        (usingRecovery ? recovery : document.querySelector('.otp-digit')).focus();
    });

    document.querySelector('.otp-digit').focus();
</script>
</body>
</html>
