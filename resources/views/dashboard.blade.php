<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel — Clube Investvida</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<main class="mx-auto max-w-3xl p-6 md:p-12">
    <section class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FF6B00] font-bold text-white">CI</div>
            <div>
                <p class="text-sm text-slate-500">Clube Investvida</p>
                <h1 class="text-2xl font-bold text-[#003461]">Autenticação concluída</h1>
            </div>
        </div>

        <p class="text-slate-600">Olá, {{ auth()->user()->nome }}. Seu perfil é <strong>{{ auth()->user()->perfil }}</strong>.</p>
        <p class="mt-2 text-sm text-slate-500">O painel completo será ligado aos dados reais na próxima fatia.</p>

        <form method="POST" action="{{ url('/logout') }}" class="mt-8">
            @csrf
            <button type="submit" class="rounded-md bg-[#003461] px-4 py-2 text-sm font-semibold text-white hover:bg-[#00284c]">
                Sair
            </button>
        </form>
    </section>
</main>
</body>
</html>
