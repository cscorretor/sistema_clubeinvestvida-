<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Propostas e apólices — Clube Investvida</title>
<link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('assets/css/laravel-utilities.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
 body{font-family:"Inter",sans-serif;background:#F4F7F9;color:#1A1C1E}.font-head{font-family:"Manrope",sans-serif}
 .nav a,.nav .nav-disabled{display:flex;gap:.6rem;align-items:center;padding:.55rem .75rem;border-radius:.5rem;font-size:.9rem;color:#cbd5e1}.nav a:hover{background:rgba(255,255,255,.06);color:#fff}.nav a.on{background:rgba(255,255,255,.10);color:#fff;font-weight:600;box-shadow:inset 3px 0 0 #FF6B00}.nav .nav-disabled{color:#718198;cursor:not-allowed}.nav .nav-disabled small{margin-left:auto;font-size:.6rem}
 .inp{border:1px solid #E2E8F0;border-radius:.5rem;padding:.55rem .75rem;font-size:.9rem;background:#fff}.chip{font-size:.68rem;font-weight:600;padding:.12rem .5rem;border-radius:999px}
 th{font-size:.68rem;text-transform:uppercase;letter-spacing:.03em;color:#64748b;font-weight:600;text-align:left}td,th{padding:.75rem}
 .client-match{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;align-items:center;padding:12px 14px;border:1px solid #E2E8F0;border-radius:.65rem;background:#fff}
</style>
</head>
<body>
<div class="flex min-h-screen">
  <aside id="appSidebar" class="sidebar w-60 bg-navy text-white flex-col">
    <div class="px-5 py-4 flex items-center gap-3 border-b border-white/10"><img src="{{ asset('assets/brand/logo-simbolo-claro.svg') }}" width="36" height="36" alt=""><div><div class="font-head font-bold leading-none">Clube Investvida</div><div class="text-[11px] text-blue-200 mt-1">Seguros de Pessoas</div></div></div>
    <nav class="nav p-3 space-y-1 flex-1">
      <a href="{{ route('dashboard') }}"><span>▦</span> Dashboard</a><a href="{{ route('clientes.index') }}"><span>◉</span> Clientes</a><a href="{{ route('apolices.index') }}" class="on"><span>❤</span> Apólices</a>
      <span class="nav-disabled"><span>◔</span> Leads / CRM <small>EM BREVE</small></span><span class="nav-disabled"><span>◷</span> Chamados <small>EM BREVE</small></span><span class="nav-disabled"><span>$</span> Financeiro <small>EM BREVE</small></span><span class="nav-disabled"><span>⛁</span> Cofre Digital <small>EM BREVE</small></span><span class="nav-disabled"><span>⚙</span> Configurações <small>EM BREVE</small></span>
    </nav>
  </aside>
  <div class="sidebar-backdrop" data-sidebar-close></div>
  <div class="flex-1 flex flex-col min-w-0">
    <header class="bg-white border-b border-line px-5 py-3 flex items-center gap-4"><button type="button" class="mobile-nav-toggle" aria-controls="appSidebar" aria-expanded="false" aria-label="Abrir menu">☰</button><div class="ml-auto w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-semibold">{{ mb_strtoupper(mb_substr(auth()->user()->nome, 0, 2)) }}</div></header>
    <main class="p-5">
      <div class="mb-4 flex items-end justify-between flex-wrap gap-3">
        <div><h1 class="text-2xl font-head font-bold text-navy">Propostas e apólices</h1><p class="text-sm text-slate-500">Vida, Previdência, Saúde e Residencial em uma única carteira.</p></div>
        <a href="{{ route('clientes.index', ['acao' => 'nova_apolice']) }}" class="bg-navy text-white px-4 py-2.5 rounded-md text-sm font-semibold">+ Nova proposta</a>
      </div>
      <form method="GET" action="{{ route('apolices.index') }}" class="bg-white border border-line rounded-xl p-3 mb-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-60"><label class="text-xs text-slate-500" for="busca">Cliente, proposta ou apólice</label><input class="inp mt-1 w-full" id="busca" name="busca" value="{{ $filters['busca'] ?? '' }}"></div>
        <div><label class="text-xs text-slate-500" for="ramo">Produto</label><select class="inp mt-1" id="ramo" name="ramo"><option value="">Todos</option>@foreach($ramos as $ramo)<option value="{{ $ramo->id }}" @selected((string)($filters['ramo'] ?? '') === (string)$ramo->id)>{{ $ramo->nome }}</option>@endforeach</select></div>
        <div><label class="text-xs text-slate-500" for="status">Status</label><select class="inp mt-1" id="status" name="status">@foreach(['TODOS'=>'Todos','PROSPECCAO'=>'Prospecção','EM_EMISSAO'=>'Em emissão','ATIVO'=>'Ativo','RENOVACAO'=>'Renovação','CANCELADO'=>'Cancelado','INATIVO'=>'Inativo'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['status'] ?? 'TODOS') === $value)>{{ $label }}</option>@endforeach</select></div>
        <button class="bg-orange text-white px-4 py-2.5 rounded-md text-sm font-semibold">Filtrar</button><a href="{{ route('apolices.index') }}" class="px-3 py-2.5 text-sm text-slate-500">Limpar</a>
        <span class="ml-auto text-xs text-slate-400 self-center">{{ $apolices->total() }} resultado(s)</span>
      </form>
      <div class="bg-white border border-line rounded-xl overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full min-w-[850px] text-sm"><thead class="bg-slate-50 border-b border-line"><tr><th>Cliente</th><th>Produto</th><th>Seguradora</th><th>Proposta / Apólice</th><th>Vigência</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($apolices as $item)
          <tr class="border-t border-line/70"><td><a href="{{ route('clientes.show',$item->cliente) }}" class="font-medium text-navy">{{ $item->cliente->nome }}</a></td><td>{{ $item->ramo?->nome }}</td><td>{{ $item->seguradora?->nome }}</td><td>{{ $item->num_apolice ?: $item->num_proposta }}</td><td>{{ $item->inicio_vigencia?->format('d/m/Y') ?? '—' }} a {{ $item->fim_vigencia?->format('d/m/Y') ?? '—' }}</td><td><span class="chip bg-blue-50 text-navy">{{ str($item->status)->replace('_',' ')->title() }}</span></td><td><a href="{{ route('apolices.edit',$item) }}" class="text-orange font-semibold">Editar</a></td></tr>
        @empty
          <tr><td colspan="7" class="py-10 text-center text-slate-500">
            @if($totalCarteira === 0)
              <strong class="block text-navy mb-1">A carteira ainda não possui propostas ou apólices.</strong>
              Escolha um cliente para cadastrar a primeira proposta.
            @else
              <strong class="block text-navy mb-1">Nenhuma proposta ou apólice corresponde aos filtros.</strong>
              Revise a busca, o produto ou o status e tente novamente.
            @endif
          </td></tr>
        @endforelse
        </tbody></table></div>
        <div class="px-4 py-3 border-t border-line">{{ $apolices->links() }}</div>
      </div>

      @if($clientesCorrespondentes->isNotEmpty())
        <section class="mt-4">
          <h2 class="font-head font-semibold text-navy">Clientes encontrados</h2>
          <p class="text-sm text-slate-500 mb-3">O cliente existe, mas não possui proposta ou apólice que corresponda à pesquisa. Você pode criar uma agora.</p>
          <div class="space-y-2">
            @foreach($clientesCorrespondentes as $cliente)
              <div class="client-match">
                <div><a href="{{ route('clientes.show', $cliente) }}" class="font-semibold text-navy">{{ $cliente->nome }}</a><div class="text-xs text-slate-400">{{ $cliente->codigo }} · {{ $cliente->documentoMascarado() }}</div></div>
                <a href="{{ route('apolices.create', $cliente) }}" class="bg-orange text-white px-3 py-2 rounded-md text-sm font-semibold">Criar proposta</a>
              </div>
            @endforeach
          </div>
        </section>
      @endif
    </main>
  </div>
</div>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
