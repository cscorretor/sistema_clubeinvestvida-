<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — Clube Investvida</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {theme: {extend: {colors: {navy: "#003461", navy2: "#004B87", orange: "#FF6B00", ink: "#1A1C1E", line: "#E2E8F0", surface: "#F4F7F9"}, fontFamily: {head: ["Manrope"], body: ["Inter"]}}}};
    </script>
    <style>
        body{font-family:"Inter",sans-serif;background:#F4F7F9;color:#1A1C1E}.font-head{font-family:"Manrope",sans-serif}
        .inp{border:1px solid #E2E8F0;border-radius:.5rem;padding:.5rem .7rem;font-size:.9rem;background:#fff}
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
            <h1 class="font-head font-bold text-3xl leading-tight">Proteção e patrimônio,<br>com gestão inteligente.</h1>
            <p class="text-blue-200 mt-3 text-sm max-w-sm">Sistema de gestão de seguros de pessoas — Vida, Previdência, Saúde, Viagem e Renda.</p>
        </div>
        <div class="text-blue-300 text-xs">© 2026 Clube Investvida Corretora de Seguros</div>
    </div>

    <main class="flex items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <div class="md:hidden flex items-center gap-2 mb-6">
                <div class="w-9 h-9 rounded-md bg-navy text-white flex items-center justify-center font-head font-bold">CI</div>
                <span class="font-head font-bold text-navy">Clube Investvida</span>
            </div>

            <h2 class="font-head font-bold text-2xl text-navy">Entrar</h2>
            <p class="text-sm text-slate-500 mb-6">Acesse o painel da corretora.</p>

            @if ($errors->any())
                <div role="alert" class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    E-mail ou senha inválidos. Verifique os dados e tente novamente.
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="text-xs font-semibold text-slate-600">E-mail</label>
                    <input id="email" name="email" type="email" autocomplete="username" required autofocus
                           class="inp w-full mt-1" placeholder="voce@clubeinvestvida.com" value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password" class="text-xs font-semibold text-slate-600">Senha</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="inp w-full mt-1" placeholder="••••••••">
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-slate-600">
                        <input name="remember" type="checkbox" value="1" class="accent-navy"> Lembrar
                    </label>
                    <span class="text-slate-400 text-xs">Recuperação pelo administrador</span>
                </div>
                <button type="submit" class="w-full bg-navy text-white py-2.5 rounded-md font-semibold hover:bg-[#00284c]">
                    Entrar
                </button>
            </form>

            <p class="text-xs text-slate-400 mt-6 text-center">Protegido por verificação em duas etapas (2FA).</p>
        </div>
    </main>
</div>
</body>
</html>
