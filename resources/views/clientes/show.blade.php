<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $cliente->nome }} — Clube Investvida</title>
<link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('assets/css/laravel-utilities.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
 body{font-family:"Inter",sans-serif;background:#F4F7F9;color:#1A1C1E}.font-head{font-family:"Manrope",sans-serif}
 .nav a,.nav .nav-disabled{display:flex;gap:.6rem;align-items:center;padding:.55rem .75rem;border-radius:.5rem;font-size:.9rem;color:#cbd5e1}
 .nav a:hover{background:rgba(255,255,255,.06);color:#fff}
 .nav a.on{background:rgba(255,255,255,.10);color:#fff;font-weight:600;box-shadow:inset 3px 0 0 #FF6B00}
 .nav .nav-disabled{color:#718198;cursor:not-allowed}.nav .nav-disabled small{margin-left:auto;font-size:.6rem}
 .card{background:#fff;border:1px solid #E2E8F0;border-radius:.75rem}
 .chip{font-size:.66rem;font-weight:600;padding:.1rem .45rem;border-radius:999px}
 th{font-size:.68rem;text-transform:uppercase;letter-spacing:.03em;color:#64748b;font-weight:600;text-align:left}td,th{padding:.55rem .7rem}
</style>
</head>
<body>
@php
  $telefone = $cliente->telefones->first();
  $email = $cliente->emails->first();
  $endereco = $cliente->enderecos->first();
  $apolicesAtivas = $cliente->apolices->whereIn('status', ['ATIVO', 'RENOVACAO']);
  $valorAberto = $cliente->apolices->flatMap->parcelas->where('status', 'ABERTO')->sum('valor_cliente');
  $proximaRenovacao = $apolicesAtivas->pluck('fim_vigencia')->filter()->sort()->first();
  $telefoneDigits = preg_replace('/\D/', '', (string) $telefone?->numero);
  $whatsapp = $telefoneDigits ? 'https://wa.me/'.(str_starts_with($telefoneDigits, '55') ? $telefoneDigits : '55'.$telefoneDigits) : null;
  $statusClass = $cliente->status === 'ATIVO' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500';
  $estadoCivilLabels = ['SOLTEIRO' => 'Solteiro', 'CASADO' => 'Casado', 'DIVORCIADO' => 'Divorciado', 'VIUVO' => 'Viúvo', 'UNIAO_ESTAVEL' => 'União Estável'];
@endphp
<div class="flex min-h-screen">
  <aside id="appSidebar" class="sidebar w-60 bg-navy text-white flex-col">
    <div class="px-5 py-4 flex items-center gap-3 border-b border-white/10">
      <img src="{{ asset('assets/brand/logo-simbolo-claro.svg') }}" width="36" height="36" alt="">
      <div><div class="font-head font-bold leading-none">Clube Investvida</div>
      <div class="text-[11px] text-blue-200 mt-1">Seguros de Pessoas</div></div>
    </div>
    <nav class="nav p-3 space-y-1 flex-1">
      <a href="{{ route('dashboard') }}"><span>▦</span> Dashboard</a>
      <a href="{{ route('clientes.index') }}" class="on"><span>◉</span> Clientes</a>
      <a href="{{ route('apolices.index') }}"><span>❤</span> Apólices</a>
      <span class="nav-disabled"><span>◔</span> Leads / CRM <small>EM BREVE</small></span>
      <span class="nav-disabled"><span>◷</span> Chamados <small>EM BREVE</small></span>
      <span class="nav-disabled"><span>$</span> Financeiro <small>EM BREVE</small></span>
      <span class="nav-disabled"><span>⛁</span> Cofre Digital <small>EM BREVE</small></span>
      <span class="nav-disabled"><span>⚙</span> Configurações <small>EM BREVE</small></span>
    </nav>
    <div class="p-3 border-t border-white/10 text-[11px] text-blue-200">v0 · protótipo</div>
  </aside>
  <div class="sidebar-backdrop" data-sidebar-close></div>
  <div class="flex-1 flex flex-col min-w-0">
    <header class="bg-white border-b border-line px-5 py-3 flex items-center gap-4">
      <button type="button" class="mobile-nav-toggle" aria-controls="appSidebar" aria-expanded="false" aria-label="Abrir menu">☰</button>
      <a href="{{ route('clientes.index') }}" class="text-sm text-slate-500 hover:text-navy">‹ Clientes</a>
      <div class="ml-auto w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-semibold">
        {{ mb_strtoupper(mb_substr(auth()->user()->nome, 0, 2)) }}
      </div>
    </header>
    <main class="p-5">
      @if (session('status'))
        <div role="status" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
          ✓ {{ session('status') }}
        </div>
      @endif
      <div class="card p-5 mb-4 flex items-start justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4 min-w-0">
          <div class="w-14 h-14 shrink-0 rounded-full bg-navy/10 text-navy flex items-center justify-center text-lg font-head font-bold">{{ $cliente->iniciais() }}</div>
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-xl font-head font-bold text-navy">{{ $cliente->nome }}</h1>
              <span class="chip {{ $statusClass }}">{{ str($cliente->status)->title() }}</span>
              <span class="chip bg-blue-50 text-navy2">{{ str($cliente->tipo_cliente)->replace('_', ' ')->title() }}</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
              {{ $cliente->pessoa === 'PJ' ? 'CNPJ' : 'CPF' }} {{ $cliente->documentoMascarado() }}
              @if ($endereco?->cidade) · {{ $endereco->cidade }}/{{ $endereco->uf }} @endif
              · Cliente desde {{ $cliente->data_cadastro?->format('m/Y') ?? $cliente->created_at?->format('m/Y') }}
            </p>
            <div class="flex gap-3 mt-2 text-sm text-slate-600 flex-wrap">
              <span>📞 {{ $telefone?->numero ?: 'Não informado' }}</span>
              <span>✉ {{ $email?->email ?: 'Não informado' }}</span>
            </div>
          </div>
        </div>
        <div class="flex gap-2 flex-wrap">
          @can('update', $cliente)
            <a href="{{ route('clientes.edit', $cliente) }}" class="bg-white border border-line px-3 py-2 rounded-md text-sm font-semibold text-navy hover:bg-slate-50">Editar</a>
          @endcan
          @can('update', $cliente)
            <a href="{{ route('apolices.create', $cliente) }}" class="bg-navy text-white px-3 py-2 rounded-md text-sm font-semibold">+ Nova proposta/apólice</a>
          @endcan
          @if ($whatsapp)
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="bg-green-600 text-white px-3 py-2 rounded-md text-sm font-semibold">WhatsApp</a>
          @else
            <span class="bg-slate-200 text-slate-500 px-3 py-2 rounded-md text-sm font-semibold">WhatsApp</span>
          @endif
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="card p-3"><div class="text-xs text-slate-500">Apólices ativas</div><div class="font-head font-bold text-navy text-lg">{{ $apolicesAtivas->count() }}</div></div>
        <div class="card p-3"><div class="text-xs text-slate-500">Parcelas em aberto</div><div class="font-head font-bold text-navy text-lg">R$ {{ number_format((float) $valorAberto, 2, ',', '.') }}</div></div>
        <div class="card p-3"><div class="text-xs text-slate-500">Chamados pendentes</div><div class="font-head font-bold text-navy text-lg">{{ $cliente->chamados->whereIn('status', ['PENDENTE', 'EM_ANDAMENTO'])->count() }}</div></div>
        <div class="card p-3"><div class="text-xs text-slate-500">Próx. renovação</div><div class="font-head font-bold text-orange text-lg">{{ $proximaRenovacao?->format('d/m/Y') ?? '—' }}</div></div>
      </div>

      <div class="flex gap-5 border-b border-line mb-4 text-sm overflow-x-auto">
        <button class="tab shrink-0 whitespace-nowrap pb-2 border-b-2 border-orange text-navy font-semibold" data-t="apolices">Apólices</button>
        <button class="tab shrink-0 whitespace-nowrap pb-2 text-slate-500" data-t="docs">Documentos</button>
        <button class="tab shrink-0 whitespace-nowrap pb-2 text-slate-500" data-t="chamados">Chamados</button>
        <button class="tab shrink-0 whitespace-nowrap pb-2 text-slate-500" data-t="timeline">Linha do tempo</button>
        <button class="tab shrink-0 whitespace-nowrap pb-2 text-slate-500" data-t="dados">Dados</button>
      </div>

      <section data-p="apolices">
        <div class="card overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm"><thead class="bg-slate-50 border-b border-line"><tr>
              <th>Ramo</th><th>Seguradora</th><th>Proposta / Apólice</th><th>Vigência</th><th>Parcela</th><th>Status</th><th></th></tr></thead>
              <tbody>
              @forelse ($cliente->apolices as $apolice)
                @php
                  $parcela = $apolice->parcelas->where('status', '<>', 'CANCELADO')->sortBy('numero')->first();
                  $apoliceStatusClass = in_array($apolice->status, ['ATIVO', 'RENOVACAO'], true) ? 'bg-green-100 text-green-700' : ($apolice->status === 'CANCELADO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                @endphp
                <tr class="border-t border-line/70">
                  <td><span class="chip bg-blue-50 text-navy2">{{ $apolice->ramo?->nome ?? '—' }}</span></td>
                  <td>{{ $apolice->seguradora?->nome ?? '—' }}</td>
                  <td>{{ $apolice->num_apolice ?: $apolice->num_proposta ?: '—' }}</td>
                  <td>{{ $apolice->fim_vigencia?->format('d/m/Y') ?? 'Sem prazo' }}</td>
                  <td>{{ $parcela ? 'R$ '.number_format((float) $parcela->valor_cliente, 2, ',', '.') : '—' }}</td>
                  <td><span class="chip {{ $apoliceStatusClass }}">{{ str($apolice->status)->replace('_', ' ')->title() }}</span></td>
                  <td>@can('update', $cliente)<a href="{{ route('apolices.edit', $apolice) }}" class="text-orange font-semibold">Editar</a>@endcan</td>
                </tr>
              @empty
                <tr><td colspan="7" class="py-8 text-center text-slate-500">Nenhuma proposta ou apólice cadastrada para este cliente.</td></tr>
              @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section data-p="docs" class="hidden">
        <div class="card p-6 text-center">
          <div class="text-2xl">📄</div>
          <div class="font-medium mt-2">Nenhum documento disponível</div>
          <p class="text-sm text-slate-500 mt-1">O schema de documentos ainda não foi criado; nenhum arquivo ilustrativo é exibido.</p>
        </div>
      </section>

      <section data-p="chamados" class="hidden">
        <div class="card overflow-hidden"><div class="overflow-x-auto"><table class="w-full min-w-[600px] text-sm"><thead class="bg-slate-50 border-b border-line"><tr>
          <th>Descrição</th><th>Tipo</th><th>Prazo</th><th>Status</th></tr></thead><tbody>
          @forelse ($cliente->chamados as $chamado)
            @php $chamadoClass = $chamado->status === 'FINALIZADO' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'; @endphp
            <tr class="border-t border-line/70">
              <td>{{ $chamado->descricao ?: $chamado->subtipo ?: 'Sem descrição' }}</td>
              <td>{{ str($chamado->tipo)->replace('_', ' ')->title() }}</td>
              <td>{{ $chamado->data_resolucao?->format('d/m/Y') ?? '—' }}</td>
              <td><span class="chip {{ $chamadoClass }}">{{ str($chamado->status)->replace('_', ' ')->title() }}</span></td>
            </tr>
          @empty
            <tr><td colspan="4" class="py-8 text-center text-slate-500">Nenhum chamado cadastrado para este cliente.</td></tr>
          @endforelse
        </tbody></table></div></div>
      </section>

      <section data-p="timeline" class="hidden">
        @if ($cliente->auditLogs->isEmpty())
          <div class="card p-6 text-center text-sm text-slate-500">Nenhum evento registrado para este cliente.</div>
        @else
          <ol class="relative border-l-2 border-line ml-2 space-y-4 pl-5">
            @foreach ($cliente->auditLogs as $evento)
              <li><span class="absolute -left-[7px] w-3 h-3 rounded-full {{ $evento->acao === 'CRIAR' ? 'bg-navy' : 'bg-orange' }}"></span>
                <div class="text-xs text-slate-400">{{ $evento->created_at?->format('d/m/Y H:i') }}</div>
                <div class="text-sm">Cliente: {{ str($evento->acao)->lower()->ucfirst() }} por {{ $evento->usuario }}.</div>
              </li>
            @endforeach
          </ol>
        @endif
      </section>

      <section data-p="dados" class="hidden">
        <div class="space-y-4">
          @if ($cliente->pessoa === 'PJ')
            <div class="card p-5">
              <h2 class="font-head font-semibold text-navy mb-4">Dados da empresa</h2>
              <div class="grid md:grid-cols-3 gap-4 text-sm">
                <div><div class="text-xs text-slate-500">Razão social</div>{{ $cliente->nome }}</div>
                <div><div class="text-xs text-slate-500">CNPJ</div>{{ $cliente->documentoMascarado() }}</div>
                <div><div class="text-xs text-slate-500">Nome fantasia</div>{{ $cliente->nome_fantasia ?: 'Não informado' }}</div>
                <div><div class="text-xs text-slate-500">Inscrição estadual</div>{{ $cliente->inscricao_est ?: 'Não informada' }}</div>
                <div><div class="text-xs text-slate-500">Data de abertura</div>{{ $cliente->data_abertura?->format('d/m/Y') ?? 'Não informada' }}</div>
                <div><div class="text-xs text-slate-500">Produtor responsável</div>{{ $cliente->produtor?->nome ?? 'Não atribuído' }}</div>
                <div><div class="text-xs text-slate-500">Endereço principal da empresa</div>
                  @if ($endereco)
                    {{ $endereco->logradouro }}{{ $endereco->numero ? ', '.$endereco->numero : '' }}{{ $endereco->bairro ? ' — '.$endereco->bairro : '' }}{{ $endereco->cidade ? ', '.$endereco->cidade.'/'.$endereco->uf : '' }}
                  @else
                    Não informado
                  @endif
                </div>
                <div><div class="text-xs text-slate-500">Telefones da empresa</div>{{ $cliente->telefones->pluck('numero')->join(', ') ?: 'Não informado' }}</div>
                <div><div class="text-xs text-slate-500">E-mails da empresa</div>{{ $cliente->emails->pluck('email')->join(', ') ?: 'Não informado' }}</div>
                <div><div class="text-xs text-slate-500">Canal de origem</div>{{ $cliente->intermedio ?: 'Não informado' }}</div>
              </div>
            </div>

            <div class="card p-5">
              <h2 class="font-head font-semibold text-navy mb-4">Pessoas de contato</h2>
              <div class="space-y-3">
                @forelse ($cliente->contatos as $contato)
                  <div class="grid md:grid-cols-4 gap-3 rounded-lg border border-line p-4 text-sm">
                    <div><div class="text-xs text-slate-500">Nome</div>{{ $contato->nome }}</div>
                    <div><div class="text-xs text-slate-500">Cargo / função</div>{{ $contato->cargo ?: 'Não informado' }}</div>
                    <div><div class="text-xs text-slate-500">E-mail</div>{{ $contato->email ?: 'Não informado' }}</div>
                    <div><div class="text-xs text-slate-500">Telefone</div>{{ $contato->telefone ?: 'Não informado' }}</div>
                  </div>
                @empty
                  <p class="text-sm text-slate-500">Nenhuma pessoa de contato cadastrada.</p>
                @endforelse
              </div>
            </div>
          @else
            <div class="card p-5">
              <h2 class="font-head font-semibold text-navy mb-4">Dados do titular</h2>
              <div class="grid md:grid-cols-3 gap-4 text-sm">
                <div><div class="text-xs text-slate-500">CPF do titular</div>{{ $cliente->documentoMascarado() }}</div>
                <div><div class="text-xs text-slate-500">Nascimento do titular</div>{{ $cliente->nascimento?->format('d/m/Y') ?? 'Não informado' }}@if($cliente->nascimento) ({{ $cliente->nascimento->age }} anos) @endif</div>
                <div><div class="text-xs text-slate-500">Estado civil do titular</div>{{ $estadoCivilLabels[$cliente->estado_civil] ?? 'Não informado' }}</div>
                <div><div class="text-xs text-slate-500">Profissão do titular</div>{{ $cliente->profissao ?: 'Não informada' }}</div>
                <div><div class="text-xs text-slate-500">Faixa de renda do titular</div>{{ $cliente->faixa_renda ?: 'Não informada' }}</div>
                <div><div class="text-xs text-slate-500">Produtor responsável</div>{{ $cliente->produtor?->nome ?? 'Não atribuído' }}</div>
                <div><div class="text-xs text-slate-500">Endereço principal do titular</div>
                  @if ($endereco)
                    {{ $endereco->logradouro }}{{ $endereco->numero ? ', '.$endereco->numero : '' }}{{ $endereco->bairro ? ' — '.$endereco->bairro : '' }}{{ $endereco->cidade ? ', '.$endereco->cidade.'/'.$endereco->uf : '' }}
                  @else
                    Não informado
                  @endif
                </div>
                <div><div class="text-xs text-slate-500">Telefones do titular</div>{{ $cliente->telefones->pluck('numero')->join(', ') ?: 'Não informado' }}</div>
                <div><div class="text-xs text-slate-500">E-mails do titular</div>{{ $cliente->emails->pluck('email')->join(', ') ?: 'Não informado' }}</div>
                <div><div class="text-xs text-slate-500">Canal de origem</div>{{ $cliente->intermedio ?: 'Não informado' }}</div>
                @if ($cliente->cnh)
                  <div><div class="text-xs text-slate-500">CNH do titular</div>{{ $cliente->cnh->numero_registro ?: 'Número não informado' }} · {{ $cliente->cnh->categoria ?: 'Categoria não informada' }}</div>
                @endif
              </div>
            </div>

            @if ($cliente->conjuge)
              <div class="card p-5 border-blue-100 bg-blue-50/30">
                <h2 class="font-head font-semibold text-navy mb-4">Dados do cônjuge</h2>
                <div class="grid md:grid-cols-3 gap-4 text-sm">
                  <div><div class="text-xs text-slate-500">Nome do cônjuge</div>{{ $cliente->conjuge->nome ?: 'Não informado' }}</div>
                  <div><div class="text-xs text-slate-500">CPF do cônjuge</div>{{ $cliente->conjuge->documentoMascarado() }}</div>
                  <div><div class="text-xs text-slate-500">Nascimento do cônjuge</div>{{ $cliente->conjuge->nascimento?->format('d/m/Y') ?? 'Não informado' }}</div>
                </div>
              </div>
            @endif
          @endif
        </div>
      </section>
    </main>
  </div>
</div>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script>
@if (session('status'))
localStorage.removeItem('cliente-create');
localStorage.removeItem(@json('cliente-edit-'.$cliente->getKey()));
@endif
document.querySelectorAll('.tab').forEach(b=>b.onclick=()=>{
  document.querySelectorAll('.tab').forEach(x=>x.className='tab shrink-0 whitespace-nowrap pb-2 text-slate-500');
  b.className='tab shrink-0 whitespace-nowrap pb-2 border-b-2 border-orange text-navy font-semibold';
  document.querySelectorAll('[data-p]').forEach(s=>s.classList.add('hidden'));
  document.querySelector('[data-p="'+b.dataset.t+'"]').classList.remove('hidden');
});
</script>
</body>
</html>
