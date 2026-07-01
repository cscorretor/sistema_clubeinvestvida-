<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clientes — Clube Investvida</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script>tailwind.config={theme:{extend:{colors:{navy:'#003461',navy2:'#004B87',orange:'#FF6B00',ink:'#1A1C1E',line:'#E2E8F0',surface:'#F4F7F9'},fontFamily:{head:['Manrope'],body:['Inter']}}}}</script>
<style>
 body{font-family:'Inter',sans-serif;background:#F4F7F9;color:#1A1C1E}
 .font-head{font-family:'Manrope',sans-serif}
 .nav a{display:flex;gap:.6rem;align-items:center;padding:.55rem .75rem;border-radius:.5rem;font-size:.9rem;color:#cbd5e1}
 .nav a:hover{background:rgba(255,255,255,.06);color:#fff}
 .nav a.on{background:rgba(255,255,255,.10);color:#fff;font-weight:600;box-shadow:inset 3px 0 0 #FF6B00}
 .inp{border:1px solid #E2E8F0;border-radius:.5rem;padding:.55rem .75rem;font-size:.9rem;background:#fff}
 .inp:focus{outline:none;border-color:#003461;box-shadow:0 0 0 3px rgba(0,75,135,.12)}
 .chip{font-size:.68rem;font-weight:600;padding:.12rem .5rem;border-radius:999px}
 th{font-size:.68rem;text-transform:uppercase;letter-spacing:.03em;color:#64748b;font-weight:600;text-align:left}
 td,th{padding:.7rem .75rem}
 tbody tr:hover{background:#f1f5f9}
</style>
</head>
<body>
<div class="flex min-h-screen">
  <aside class="w-60 bg-navy text-white flex-col hidden md:flex">
    <div class="px-5 py-4 flex items-center gap-3 border-b border-white/10">
      <div class="w-9 h-9 rounded-md bg-orange flex items-center justify-center font-head font-bold">CI</div>
      <div><div class="font-head font-bold leading-none">Clube Investvida</div>
      <div class="text-[11px] text-blue-200 mt-1">Seguros de Pessoas</div></div>
    </div>
    <nav class="nav p-3 space-y-1 flex-1">
      <a href="{{ route('dashboard') }}"><span>▦</span> Dashboard</a>
      <a href="{{ route('clientes.index') }}" class="on"><span>◉</span> Clientes</a>
      <a href="#" aria-disabled="true"><span>❤</span> Apólices</a>
      <a href="#" aria-disabled="true"><span>◔</span> Leads / CRM</a>
      <a href="#" aria-disabled="true"><span>◷</span> Chamados</a>
      <a href="#" aria-disabled="true"><span>$</span> Financeiro</a>
      <a href="#" aria-disabled="true"><span>⛁</span> Cofre Digital</a>
      <a href="#" aria-disabled="true"><span>⚙</span> Configurações</a>
    </nav>
    <div class="p-3 border-t border-white/10 text-[11px] text-blue-200">v0 · protótipo</div>
  </aside>

  <div class="flex-1 flex flex-col min-w-0">
    <header class="bg-white border-b border-line px-5 py-3 flex items-center gap-4">
      <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-1 max-w-md">
        <input class="inp w-full rounded-r-none" name="busca" value="{{ $filters['busca'] ?? '' }}" placeholder="Buscar por nome, CPF, telefone…" aria-label="Buscar clientes">
        @foreach (['tipo', 'status', 'ramo', 'cidade'] as $filter)
          @if (isset($filters[$filter]))
            <input type="hidden" name="{{ $filter }}" value="{{ $filters[$filter] }}">
          @endif
        @endforeach
        <button type="submit" class="rounded-r-md bg-navy px-3 text-sm font-semibold text-white hover:bg-[#00284c]">Buscar</button>
      </form>
      <div class="ml-auto flex items-center gap-3 text-sm text-slate-500">
        <span class="hidden sm:inline">{{ auth()->user()->nome }}</span>
        <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-semibold">
          {{ mb_strtoupper(mb_substr(auth()->user()->nome, 0, 2)) }}
        </div>
      </div>
    </header>

    <main class="p-5">
      <div class="flex items-end justify-between flex-wrap gap-3 mb-4">
        <div><h1 class="text-2xl font-head font-bold text-navy">Clientes</h1>
          <p class="text-sm text-slate-500">Sua carteira em um só lugar — busca e filtros rápidos.</p></div>
        @can('create', App\Models\Cliente::class)
          <a href="{{ route('clientes.create') }}" class="bg-navy text-white px-4 py-2.5 rounded-md text-sm font-semibold hover:bg-[#00284c]">+ Novo Cliente</a>
        @endcan
      </div>

      <form method="GET" action="{{ route('clientes.index') }}" class="bg-white border border-line rounded-xl p-3 mb-4 flex flex-wrap gap-3 items-end">
        <input type="hidden" name="busca" value="{{ $filters['busca'] ?? '' }}">
        <div><label class="text-xs text-slate-500" for="tipo">Tipo</label><br>
          <select class="inp mt-1" id="tipo" name="tipo">
            @foreach (['TODOS' => 'Todos', 'EFETIVO' => 'Efetivo', 'PROSPECT' => 'Prospect', 'RELACIONAMENTO' => 'Relacionamento', 'CONDUTOR' => 'Condutor', 'LOCADOR' => 'Locador'] as $value => $label)
              <option value="{{ $value }}" @selected(($filters['tipo'] ?? 'TODOS') === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div><label class="text-xs text-slate-500" for="status">Status</label><br>
          <select class="inp mt-1" id="status" name="status">
            <option value="ATIVO" @selected(($filters['status'] ?? 'ATIVO') === 'ATIVO')>Ativos</option>
            <option value="INATIVO" @selected(($filters['status'] ?? '') === 'INATIVO')>Inativos</option>
            <option value="TODOS" @selected(($filters['status'] ?? '') === 'TODOS')>Todos</option>
          </select>
        </div>
        <div><label class="text-xs text-slate-500" for="ramo">Ramo</label><br>
          <select class="inp mt-1" id="ramo" name="ramo">
            <option value="">Todos</option>
            @foreach ($ramos as $ramo)
              <option value="{{ $ramo->id }}" @selected((string) ($filters['ramo'] ?? '') === (string) $ramo->id)>{{ $ramo->nome }}</option>
            @endforeach
          </select>
        </div>
        <div><label class="text-xs text-slate-500" for="cidade">Cidade</label><br>
          <select class="inp mt-1" id="cidade" name="cidade">
            <option value="">Todas</option>
            @foreach ($cidades as $cidade)
              <option value="{{ $cidade }}" @selected(($filters['cidade'] ?? '') === $cidade)>{{ $cidade }}</option>
            @endforeach
          </select>
        </div>
        <button class="bg-orange text-white px-4 py-2.5 rounded-md text-sm font-semibold">Filtrar</button>
        <a href="{{ route('clientes.index') }}" class="px-3 py-2.5 text-sm text-slate-500 hover:text-navy">Limpar</a>
        <span class="ml-auto text-xs text-slate-400 self-center">{{ $clientes->total() }} cliente(s) encontrado(s)</span>
      </form>

      <div class="bg-white border border-line rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[850px] text-sm">
            <thead class="border-b border-line bg-slate-50">
              <tr><th>Cliente</th><th>Tipo</th><th>Ramos</th><th>Cidade</th><th>Próx. vencimento</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($clientes as $cliente)
              @php
                $ramosCliente = $cliente->apolices->pluck('ramo.nome')->filter()->unique();
                $cidadeCliente = $cliente->enderecos->first()?->cidade;
                $tipoLabel = str($cliente->tipo_cliente)->replace('_', ' ')->title();
                $statusClass = $cliente->status === 'ATIVO' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500';
              @endphp
              <tr class="border-b border-line/70 cursor-pointer" onclick="location.href='{{ route('clientes.show', $cliente) }}'">
                <td><div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-navy/10 text-navy flex items-center justify-center text-xs font-semibold">{{ $cliente->iniciais() }}</div>
                  <div><a href="{{ route('clientes.show', $cliente) }}" class="font-medium text-ink hover:text-navy">{{ $cliente->nome }}</a>
                    <div class="text-[11px] text-slate-400">{{ $cliente->codigo }}</div></div>
                </div></td>
                <td class="text-slate-600">{{ $tipoLabel }}</td>
                <td>
                  @forelse ($ramosCliente as $ramoNome)
                    <span class="chip bg-blue-50 text-navy2 mr-1">{{ $ramoNome }}</span>
                  @empty
                    <span class="text-slate-400">—</span>
                  @endforelse
                </td>
                <td class="text-slate-600">{{ $cidadeCliente ?: '—' }}</td>
                <td class="text-slate-600">{{ $cliente->proxima_renovacao ? \Illuminate\Support\Carbon::parse($cliente->proxima_renovacao)->format('d/m/Y') : '—' }}</td>
                <td><span class="chip {{ $statusClass }}">{{ str($cliente->status)->title() }}</span></td>
                <td class="text-right"><a href="{{ route('clientes.show', $cliente) }}" class="text-slate-400" aria-label="Abrir {{ $cliente->nome }}">›</a></td>
              </tr>
            @empty
              <tr><td colspan="7" class="py-10 text-center text-slate-500">Nenhum cliente encontrado com esses filtros.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
        <div class="flex items-center justify-between gap-4 px-4 py-3 text-xs text-slate-500 border-t border-line">
          <span>
            @if ($clientes->total())
              Mostrando {{ $clientes->firstItem() }}–{{ $clientes->lastItem() }} de {{ $clientes->total() }} clientes
            @else
              Nenhum cliente para mostrar
            @endif
          </span>
          <div>{{ $clientes->onEachSide(1)->links() }}</div>
        </div>
      </div>
    </main>
  </div>
</div>
</body>
</html>
