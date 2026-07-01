<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $cliente->nome }} — Clube Investvida</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script>tailwind.config={theme:{extend:{colors:{navy:"#003461",navy2:"#004B87",orange:"#FF6B00",ink:"#1A1C1E",line:"#E2E8F0",surface:"#F4F7F9"},fontFamily:{head:["Manrope"],body:["Inter"]}}}}</script>
<style>
 body{font-family:"Inter",sans-serif;background:#F4F7F9;color:#1A1C1E}.font-head{font-family:"Manrope",sans-serif}
 .nav a{display:flex;gap:.6rem;align-items:center;padding:.55rem .75rem;border-radius:.5rem;font-size:.9rem;color:#cbd5e1}
 .nav a:hover{background:rgba(255,255,255,.06);color:#fff}
 .nav a.on{background:rgba(255,255,255,.10);color:#fff;font-weight:600;box-shadow:inset 3px 0 0 #FF6B00}
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
      <a href="{{ route('clientes.index') }}" class="text-sm text-slate-500 hover:text-navy">‹ Clientes</a>
      <div class="ml-auto w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-semibold">
        {{ mb_strtoupper(mb_substr(auth()->user()->nome, 0, 2)) }}
      </div>
    </header>
    <main class="p-5">
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
          <span class="bg-white border border-line px-3 py-2 rounded-md text-sm font-semibold text-slate-400" title="Edição será ligada na próxima etapa">Editar</span>
          <span class="bg-navy/60 text-white px-3 py-2 rounded-md text-sm font-semibold" title="Cadastro de apólice ainda não ligado">+ Nova apólice</span>
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
              <th>Ramo</th><th>Seguradora</th><th>Apólice</th><th>Vigência</th><th>Parcela</th><th>Status</th></tr></thead>
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
                </tr>
              @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-500">Nenhuma apólice cadastrada para este cliente.</td></tr>
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
        <div class="card p-5 grid md:grid-cols-3 gap-4 text-sm">
          <div><div class="text-xs text-slate-500">Nascimento</div>{{ $cliente->nascimento?->format('d/m/Y') ?? 'Não informado' }}@if($cliente->nascimento) ({{ $cliente->nascimento->age }} anos) @endif</div>
          <div><div class="text-xs text-slate-500">Estado civil</div>{{ $estadoCivilLabels[$cliente->estado_civil] ?? 'Não informado' }}</div>
          <div><div class="text-xs text-slate-500">Profissão</div>{{ $cliente->profissao ?: 'Não informada' }}</div>
          <div><div class="text-xs text-slate-500">Faixa de renda</div>{{ $cliente->faixa_renda ?: 'Não informada' }}</div>
          <div><div class="text-xs text-slate-500">Produtor</div>{{ $cliente->produtor?->nome ?? 'Não atribuído' }}</div>
          <div><div class="text-xs text-slate-500">Endereço principal</div>
            @if ($endereco)
              {{ $endereco->logradouro }}{{ $endereco->numero ? ', '.$endereco->numero : '' }}{{ $endereco->bairro ? ' — '.$endereco->bairro : '' }}{{ $endereco->cidade ? ', '.$endereco->cidade.'/'.$endereco->uf : '' }}
            @else
              Não informado
            @endif
          </div>
          <div><div class="text-xs text-slate-500">Telefones</div>{{ $cliente->telefones->pluck('numero')->join(', ') ?: 'Não informado' }}</div>
          <div><div class="text-xs text-slate-500">E-mails</div>{{ $cliente->emails->pluck('email')->join(', ') ?: 'Não informado' }}</div>
          <div><div class="text-xs text-slate-500">Origem / indicação</div>{{ $cliente->intermedio ?: 'Não informada' }}</div>
          @if ($cliente->conjuge)
            <div><div class="text-xs text-slate-500">Cônjuge</div>{{ $cliente->conjuge->nome ?: 'Nome não informado' }}</div>
          @endif
          @if ($cliente->cnh)
            <div><div class="text-xs text-slate-500">CNH</div>{{ $cliente->cnh->numero_registro ?: 'Número não informado' }} · {{ $cliente->cnh->categoria ?: 'Categoria não informada' }}</div>
          @endif
        </div>
      </section>
    </main>
  </div>
</div>
<script>
document.querySelectorAll('.tab').forEach(b=>b.onclick=()=>{
  document.querySelectorAll('.tab').forEach(x=>x.className='tab shrink-0 whitespace-nowrap pb-2 text-slate-500');
  b.className='tab shrink-0 whitespace-nowrap pb-2 border-b-2 border-orange text-navy font-semibold';
  document.querySelectorAll('[data-p]').forEach(s=>s.classList.add('hidden'));
  document.querySelector('[data-p="'+b.dataset.t+'"]').classList.remove('hidden');
});
</script>
</body>
</html>
