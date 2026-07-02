<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Clube Investvida</title>
<link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
<style>
  .nav-disabled{display:flex;gap:11px;align-items:center;padding:9px 12px;border-radius:var(--r);font-size:14px;color:#718198;cursor:not-allowed}
  .nav-disabled small{margin-left:auto;font-size:9px}
  .metric-grid{grid-template-columns:repeat(4,minmax(0,1fr))}
  .chart-grid{grid-template-columns:minmax(0,1.7fr) minmax(260px,1fr)}
  .bottom-grid{grid-template-columns:minmax(0,1.55fr) minmax(280px,1fr)}
  .chart-grid>*,.bottom-grid>*{min-width:0}
  .metric-accent{border-left:4px solid var(--orange)}
  .bar-chart{height:210px;display:flex;align-items:stretch;gap:14px;padding-top:18px}
  .bar-col{flex:1;min-width:34px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:6px}
  .bar-value{font-size:12px;font-weight:700;color:var(--navy)}
  .bar-track{width:100%;height:145px;display:flex;align-items:flex-end;background:linear-gradient(to top,#edf2f7 1px,transparent 1px);background-size:100% 36px;border-bottom:1px solid var(--line)}
  .bar-fill{width:100%;min-height:8px;background:linear-gradient(180deg,var(--blue),var(--navy));border-radius:7px 7px 2px 2px}
  .bar-label{font-size:11px;color:var(--muted)}
  .branch-row{display:grid;grid-template-columns:minmax(90px,1fr) 2fr 34px;align-items:center;gap:10px;margin-top:13px;font-size:13px}
  .branch-track{height:8px;border-radius:999px;background:#edf2f7;overflow:hidden}
  .branch-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--amber),var(--orange))}
  .birthday-item{display:grid;grid-template-columns:40px minmax(0,1fr) auto;gap:10px;align-items:center;padding:11px 0;border-top:1px solid #eef1f5}
  .birthday-date{width:40px;height:40px;border-radius:10px;background:var(--info-bg);color:var(--navy);display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:800;line-height:1}
  .birthday-date small{font-size:9px;margin-top:3px}
  .table-scroll{overflow:auto;max-width:100%}
  @media(max-width:980px){.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.chart-grid,.bottom-grid{grid-template-columns:1fr}}
  @media(max-width:520px){.metric-grid{grid-template-columns:1fr}.bar-chart{gap:7px}.bar-track{height:120px}.branch-row{grid-template-columns:85px 1fr 28px}}
</style>
</head>
<body>
<div class="app">
  <aside id="appSidebar" class="sidebar">
    <div class="brand">
      <img src="{{ asset('assets/brand/logo-simbolo-claro.svg') }}" width="34" height="34" alt="">
      <div><div class="bname">Clube Investvida</div><div class="btag">Corretora de Seguros</div></div>
    </div>
    <nav class="nav" aria-label="Navegação principal">
      <a href="{{ route('dashboard') }}" class="on" aria-current="page"><span class="ico">▦</span> Dashboard</a>
      <a href="{{ route('clientes.index') }}"><span class="ico">◉</span> Clientes</a>
      <a href="{{ route('apolices.index') }}"><span class="ico">❤</span> Apólices</a>
      <span class="nav-disabled"><span class="ico">◔</span> Leads / CRM <small>EM BREVE</small></span>
      <span class="nav-disabled"><span class="ico">◷</span> Chamados <small>EM BREVE</small></span>
      <span class="nav-disabled"><span class="ico">$</span> Financeiro <small>EM BREVE</small></span>
      <span class="nav-disabled"><span class="ico">⛁</span> Cofre Digital <small>EM BREVE</small></span>
      <span class="nav-disabled"><span class="ico">⚙</span> Configurações <small>EM BREVE</small></span>
    </nav>
    <div class="foot">Ambiente de homologação</div>
  </aside>

  <div class="sidebar-backdrop" data-sidebar-close></div>
  <div class="content">
    <header class="topbar">
      <button type="button" class="mobile-nav-toggle" aria-controls="appSidebar" aria-expanded="false" aria-label="Abrir menu">☰</button>
      <img src="{{ asset('assets/brand/logo-horizontal.svg') }}" alt="Clube Investvida — Corretora de Seguros" style="height:30px">
      <form method="GET" action="{{ route('clientes.index') }}" class="row flex1" style="max-width:460px">
        <input class="input flex1" name="busca" placeholder="Buscar cliente, CPF ou telefone…" aria-label="Buscar cliente">
        <button class="btn btn-primary" type="submit">Buscar</button>
      </form>
      <span class="sr-only">Autenticação concluída</span>
      <div class="avatar mla" title="{{ auth()->user()->nome }}">{{ mb_strtoupper(mb_substr(auth()->user()->nome, 0, 2)) }}</div>
    </header>

    <main class="main">
      <div class="between wrap mb16">
        <div>
          <h1 class="h1">Dashboard</h1>
          <p class="lead">Visão geral da carteira com dados reais.</p>
        </div>
        @can('create', App\Models\Cliente::class)
          <a href="{{ route('clientes.create') }}" class="btn btn-action">+ Novo cliente</a>
        @endcan
      </div>

      <div class="grid metric-grid mb16">
        <div class="kpi"><div class="k">Clientes ativos</div><div class="v">{{ number_format($clientesAtivos, 0, ',', '.') }}</div><div class="small faint">pessoas e empresas</div></div>
        <div class="kpi"><div class="k">Apólices ativas</div><div class="v">{{ number_format($apolicesAtivas, 0, ',', '.') }}</div><div class="small faint">ativas ou em renovação</div></div>
        <div class="kpi"><div class="k">Comissão a receber</div><div class="v">R$ {{ number_format($comissaoAReceber, 2, ',', '.') }}</div><div class="small faint">próximos 90 dias</div></div>
        <div class="kpi metric-accent"><div class="k">Renovações em 30 dias</div><div class="v" style="color:var(--orange)">{{ $renovacoesCount }}</div><div class="small faint">precisam de acompanhamento</div></div>
      </div>

      <div class="grid chart-grid">
        <section class="card">
          <div class="between wrap">
            <div><h2 class="h2">Produção dos últimos 6 meses</h2><p class="lead">Novas propostas e apólices cadastradas por mês.</p></div>
            <span class="chip chip-info">{{ $producaoMensal->sum('total') }} no período</span>
          </div>
          <div class="bar-chart" role="img" aria-label="Gráfico de produção mensal">
            @foreach ($producaoMensal as $mes)
              <div class="bar-col" title="{{ $mes['label'] }}: {{ $mes['total'] }}">
                <div class="bar-value">{{ $mes['total'] }}</div>
                <div class="bar-track"><div class="bar-fill" style="height:{{ $mes['percentual'] }}%"></div></div>
                <div class="bar-label">{{ $mes['label'] }}</div>
              </div>
            @endforeach
          </div>
        </section>

        <section class="card">
          <h2 class="h2">Carteira por produto</h2>
          <p class="lead">Distribuição das propostas e apólices.</p>
          @forelse ($carteiraPorRamo as $ramo)
            <div class="branch-row">
              <span>{{ $ramo['nome'] }}</span>
              <div class="branch-track"><div class="branch-fill" style="width:{{ $ramo['percentual'] }}%"></div></div>
              <strong>{{ $ramo['total'] }}</strong>
            </div>
          @empty
            <div class="empty"><div class="ico">◔</div><div class="t">Carteira ainda vazia</div><p>Cadastre a primeira proposta para alimentar este gráfico.</p></div>
          @endforelse
          <a href="{{ route('apolices.index') }}" class="btn btn-ghost btn-block mt16">Ver propostas e apólices</a>
        </section>
      </div>

      <div class="grid bottom-grid mt16">
        <section class="card">
          <div class="between wrap mb16">
            <div><h2 class="h2">Renovações próximas</h2><p class="lead">Apólices com vencimento nos próximos 30 dias.</p></div>
            <a href="{{ route('apolices.index', ['status' => 'RENOVACAO']) }}" style="color:var(--orange);font-weight:600;font-size:14px">Ver carteira</a>
          </div>
          <div class="table-wrap table-scroll">
            <table class="table">
              <thead><tr><th>Cliente</th><th>Produto</th><th>Seguradora</th><th>Vencimento</th><th></th></tr></thead>
              <tbody>
                @forelse ($renovacoes as $apolice)
                  <tr>
                    <td><a href="{{ route('clientes.show', $apolice->cliente) }}" style="font-weight:600">{{ $apolice->cliente->nome }}</a></td>
                    <td>{{ $apolice->ramo?->nome ?? '—' }}</td>
                    <td class="muted">{{ $apolice->seguradora?->nome ?? '—' }}</td>
                    <td>{{ $apolice->fim_vigencia?->format('d/m/Y') }}</td>
                    <td><a href="{{ route('apolices.edit', $apolice) }}" class="btn btn-action" style="padding:6px 10px;font-size:12px">Acompanhar</a></td>
                  </tr>
                @empty
                  <tr><td colspan="5"><div class="empty"><div class="t">Nenhuma renovação nos próximos 30 dias</div></div></td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>

        <section class="card">
          <div class="between wrap">
            <div><h2 class="h2">Aniversários</h2><p class="lead">Clientes que fazem aniversário nos próximos 30 dias.</p></div>
            @if ($aniversariantes->where('dias', 0)->count())
              <span class="chip chip-warn">{{ $aniversariantes->where('dias', 0)->count() }} hoje</span>
            @endif
          </div>
          <div class="mt16">
            @forelse ($aniversariantes as $item)
              <div class="birthday-item">
                <div class="birthday-date">{{ $item['data']->format('d') }}<small>{{ mb_strtoupper($item['data']->translatedFormat('M')) }}</small></div>
                <div>
                  <a href="{{ route('clientes.show', $item['cliente']) }}" style="font-weight:600;color:var(--navy)">{{ $item['cliente']->nome }}</a>
                  <div class="small faint">Completa {{ $item['idade'] }} anos</div>
                </div>
                <span class="chip {{ $item['dias'] === 0 ? 'chip-warn' : 'chip-info' }}">
                  {{ $item['dias'] === 0 ? 'Hoje' : ($item['dias'] === 1 ? 'Amanhã' : 'Em '.$item['dias'].' dias') }}
                </span>
              </div>
            @empty
              <div class="empty"><div class="ico">♢</div><div class="t">Nenhum aniversário próximo</div><p>Os avisos aparecem automaticamente a partir da data de nascimento.</p></div>
            @endforelse
          </div>
        </section>
      </div>

      <form method="POST" action="{{ url('/logout') }}" class="mt16">
        @csrf
        <button type="submit" class="btn btn-ghost">Sair com segurança</button>
      </form>
    </main>
  </div>
</div>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
