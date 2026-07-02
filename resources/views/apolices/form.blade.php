@php
  $isEditing = $apolice !== null;
  $defaults = [
    'tipo_proposta' => 'NOVO',
    'status' => 'EM_EMISSAO',
    'vidas' => [[
      'nome' => $cliente->nome,
      'parentesco' => 'TITULAR',
      'nascimento' => $cliente->nascimento?->toDateString(),
      'capital' => null,
    ]],
    'beneficiarios' => [],
    'coberturas' => [],
    'dados_produto' => [],
  ];
  $formValues = array_replace_recursive($defaults, $apoliceForm, session()->getOldInput());
  $field = static fn (string $key, mixed $default = null): mixed => data_get($formValues, $key, $default);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $isEditing ? 'Editar proposta/apólice' : 'Nova proposta/apólice' }} — Clube Investvida</title>
<link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('assets/css/laravel-utilities.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
 body{font-family:"Inter",sans-serif;background:#F4F7F9;color:#1A1C1E}.font-head{font-family:"Manrope",sans-serif}
 .card{background:#fff;border:1px solid #E2E8F0;border-radius:.75rem}.lbl{font-size:.72rem;font-weight:600;letter-spacing:.02em;text-transform:uppercase;color:#475569}
 .inp{width:100%;border:1px solid #E2E8F0;border-radius:.375rem;padding:.62rem .75rem;font-size:.92rem;background:#fff}
 .inp:focus{outline:none;border-color:#003461;box-shadow:0 0 0 3px rgba(0,75,135,.12)}
 .step-n{width:1.7rem;height:1.7rem;border-radius:999px;background:#003461;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700}
 .nav a,.nav .nav-disabled{display:flex;gap:.6rem;align-items:center;padding:.55rem .75rem;border-radius:.5rem;font-size:.9rem;color:#cbd5e1}
 .nav a:hover{background:rgba(255,255,255,.06);color:#fff}.nav a.on{background:rgba(255,255,255,.10);color:#fff;font-weight:600;box-shadow:inset 3px 0 0 #FF6B00}
 .nav .nav-disabled{color:#718198;cursor:not-allowed}.nav .nav-disabled small{margin-left:auto;font-size:.6rem}
 .dynamic-row{display:grid;grid-template-columns:minmax(12rem,2fr) minmax(8rem,1fr) minmax(9rem,1fr) minmax(8rem,1fr) 2.5rem;gap:.6rem;align-items:end}
 .benef-row{display:grid;grid-template-columns:minmax(12rem,2fr) minmax(9rem,1fr) 7rem 2.5rem;gap:.6rem;align-items:end}
 .coverage-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.6rem}
 .coverage-product.hidden{display:none}
 .coverage-option{display:flex;gap:.55rem;align-items:center;border:1px solid #E2E8F0;border-radius:.5rem;padding:.7rem;background:#fff;font-size:.86rem}
 @media(max-width:900px){.dynamic-row,.benef-row{grid-template-columns:1fr 1fr}.dynamic-row .remove-row,.benef-row .remove-row{grid-column:2}.coverage-grid{grid-template-columns:1fr 1fr}}
 @media(max-width:560px){.dynamic-row,.benef-row,.coverage-grid{grid-template-columns:1fr}.dynamic-row .remove-row,.benef-row .remove-row{grid-column:1}}
</style>
</head>
<body>
<div class="flex min-h-screen">
  <aside id="appSidebar" class="sidebar w-60 bg-navy text-white flex-col">
    <div class="px-5 py-4 flex items-center gap-3 border-b border-white/10">
      <img src="{{ asset('assets/brand/logo-simbolo-claro.svg') }}" width="36" height="36" alt="">
      <div><div class="font-head font-bold leading-none">Clube Investvida</div><div class="text-[11px] text-blue-200 mt-1">Seguros de Pessoas</div></div>
    </div>
    <nav class="nav p-3 space-y-1 flex-1">
      <a href="{{ route('dashboard') }}"><span>▦</span> Dashboard</a>
      <a href="{{ route('clientes.index') }}"><span>◉</span> Clientes</a>
      <a href="{{ route('apolices.index') }}" class="on"><span>❤</span> Apólices</a>
      <span class="nav-disabled"><span>◔</span> Leads / CRM <small>EM BREVE</small></span>
      <span class="nav-disabled"><span>◷</span> Chamados <small>EM BREVE</small></span>
      <span class="nav-disabled"><span>$</span> Financeiro <small>EM BREVE</small></span>
      <span class="nav-disabled"><span>⛁</span> Cofre Digital <small>EM BREVE</small></span>
      <span class="nav-disabled"><span>⚙</span> Configurações <small>EM BREVE</small></span>
    </nav>
    <div class="p-3 border-t border-white/10 text-[11px] text-blue-200">Ambiente de homologação</div>
  </aside>
  <div class="sidebar-backdrop" data-sidebar-close></div>
  <div class="flex-1 flex flex-col min-w-0">
    <header class="bg-white border-b border-line px-5 py-3 flex items-center gap-4">
      <button type="button" class="mobile-nav-toggle" aria-controls="appSidebar" aria-expanded="false" aria-label="Abrir menu">☰</button>
      <a href="{{ route('clientes.show', $cliente) }}" class="text-sm text-slate-500 hover:text-navy">‹ {{ $cliente->nome }}</a>
      <div class="ml-auto w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-semibold">{{ mb_strtoupper(mb_substr(auth()->user()->nome, 0, 2)) }}</div>
    </header>

    <main class="w-full max-w-6xl mx-auto px-5 py-6">
      <div class="mb-5">
        <h1 class="text-2xl font-head font-bold text-navy">{{ $isEditing ? 'Editar proposta/apólice' : 'Nova proposta/apólice' }}</h1>
        <p class="text-sm text-slate-500">Cliente: <strong>{{ $cliente->nome }}</strong> · {{ $cliente->documentoMascarado() }}</p>
      </div>

      @if ($errors->any())
        <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          <p class="font-semibold">Revise os campos indicados:</p>
          <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
      @endif

      <form id="apoliceForm" method="POST" action="{{ $isEditing ? route('apolices.update', $apolice) : route('apolices.store', $cliente) }}" class="space-y-5">
        @csrf
        @if ($isEditing) @method('PUT') @endif

        <section class="card p-5">
          <div class="flex items-center gap-2 mb-4"><span class="step-n">1</span><h2 class="font-head font-semibold text-navy">Identificação</h2></div>
          <div class="grid md:grid-cols-3 gap-4">
            <div>
              <label class="lbl" for="ramo_id">Produto</label>
              <select class="inp mt-1" id="ramo_id" name="ramo_id" required>
                <option value="">Selecione…</option>
                @foreach ($ramos as $ramo)
                  <option value="{{ $ramo->id }}" data-nome="{{ $ramo->nome }}" @selected((string) $field('ramo_id') === (string) $ramo->id)>{{ $ramo->nome }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="lbl" for="seguradora_id">Seguradora</label>
              <select class="inp mt-1" id="seguradora_id" name="seguradora_id" required>
                <option value="">Selecione…</option>
                @foreach ($seguradoras as $seguradora)
                  <option value="{{ $seguradora->id }}" @selected((string) $field('seguradora_id') === (string) $seguradora->id)>{{ $seguradora->nome }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="lbl" for="tipo_proposta">Movimento</label>
              <select class="inp mt-1" id="tipo_proposta" name="tipo_proposta">
                @foreach (['NOVO' => 'Novo', 'RENOVACAO' => 'Renovação', 'ENDOSSO' => 'Endosso'] as $value => $label)
                  <option value="{{ $value }}" @selected($field('tipo_proposta') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div><label class="lbl" for="num_proposta">Nº da proposta</label><input class="inp mt-1" id="num_proposta" name="num_proposta" maxlength="40" value="{{ $field('num_proposta') }}"></div>
            <div><label class="lbl" for="num_apolice">Nº da apólice</label><input class="inp mt-1" id="num_apolice" name="num_apolice" maxlength="40" value="{{ $field('num_apolice') }}"></div>
            <div>
              <label class="lbl" for="status">Status</label>
              <select class="inp mt-1" id="status" name="status">
                @foreach (['PROSPECCAO' => 'Prospecção', 'EM_EMISSAO' => 'Em emissão', 'ATIVO' => 'Ativo', 'RENOVACAO' => 'Renovação', 'CANCELADO' => 'Cancelado', 'INATIVO' => 'Inativo'] as $value => $label)
                  <option value="{{ $value }}" @selected($field('status') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div><label class="lbl" for="inicio_vigencia">Início da vigência</label><input type="date" class="inp mt-1" id="inicio_vigencia" name="inicio_vigencia" value="{{ $field('inicio_vigencia') }}"></div>
            <div><label class="lbl" for="fim_vigencia">Fim da vigência</label><input type="date" class="inp mt-1" id="fim_vigencia" name="fim_vigencia" value="{{ $field('fim_vigencia') }}"></div>
            <div><label class="lbl" for="capital_segurado">Capital segurado (R$)</label><input type="number" step="0.01" min="0" class="inp mt-1" id="capital_segurado" name="capital_segurado" value="{{ $field('capital_segurado') }}"></div>
          </div>
        </section>

        <section class="card p-5 product-panel hidden" data-products="Previdência">
          <div class="flex items-center gap-2 mb-4"><span class="step-n">2</span><h2 class="font-head font-semibold text-navy">Dados da Previdência</h2></div>
          <div class="grid md:grid-cols-2 gap-4">
            <div><label class="lbl" for="modalidade_previdencia">Modalidade</label><select class="inp mt-1" id="modalidade_previdencia" name="dados_produto[modalidade_previdencia]"><option value="">Selecione…</option><option value="PGBL" @selected($field('dados_produto.modalidade_previdencia') === 'PGBL')>PGBL</option><option value="VGBL" @selected($field('dados_produto.modalidade_previdencia') === 'VGBL')>VGBL</option></select></div>
            <div><label class="lbl" for="regime_tributario">Regime tributário</label><select class="inp mt-1" id="regime_tributario" name="dados_produto[regime_tributario]"><option value="">Selecione…</option><option value="PROGRESSIVO" @selected($field('dados_produto.regime_tributario') === 'PROGRESSIVO')>Progressivo</option><option value="REGRESSIVO" @selected($field('dados_produto.regime_tributario') === 'REGRESSIVO')>Regressivo</option></select></div>
          </div>
        </section>

        <section class="card p-5 product-panel hidden" data-products="Saúde">
          <div class="flex items-center gap-2 mb-4"><span class="step-n">2</span><h2 class="font-head font-semibold text-navy">Dados do Plano de Saúde</h2></div>
          <div class="grid md:grid-cols-3 gap-4">
            <div><label class="lbl" for="acomodacao">Acomodação</label><select class="inp mt-1" id="acomodacao" name="dados_produto[acomodacao]"><option value="">Selecione…</option><option value="ENFERMARIA" @selected($field('dados_produto.acomodacao') === 'ENFERMARIA')>Enfermaria</option><option value="APARTAMENTO" @selected($field('dados_produto.acomodacao') === 'APARTAMENTO')>Apartamento</option></select></div>
            <div><label class="lbl" for="abrangencia">Abrangência</label><select class="inp mt-1" id="abrangencia" name="dados_produto[abrangencia]"><option value="">Selecione…</option><option value="REGIONAL" @selected($field('dados_produto.abrangencia') === 'REGIONAL')>Regional</option><option value="ESTADUAL" @selected($field('dados_produto.abrangencia') === 'ESTADUAL')>Estadual</option><option value="NACIONAL" @selected($field('dados_produto.abrangencia') === 'NACIONAL')>Nacional</option></select></div>
            <label class="flex items-center gap-2 text-sm text-slate-600 mt-6"><input type="hidden" name="dados_produto[coparticipacao]" value="0"><input type="checkbox" name="dados_produto[coparticipacao]" value="1" @checked($field('dados_produto.coparticipacao'))> Possui coparticipação</label>
          </div>
        </section>

        <section class="card p-5 product-panel hidden" data-products="Residencial">
          <div class="flex items-center gap-2 mb-4"><span class="step-n">2</span><h2 class="font-head font-semibold text-navy">Dados do Imóvel Segurado</h2></div>
          <div class="grid md:grid-cols-6 gap-4">
            <div class="md:col-span-2"><label class="lbl" for="tipo_imovel">Tipo do imóvel</label><select class="inp mt-1" id="tipo_imovel" name="dados_produto[tipo_imovel]"><option value="">Selecione…</option>@foreach (['CASA' => 'Casa', 'APARTAMENTO' => 'Apartamento', 'CONDOMINIO' => 'Condomínio', 'OUTRO' => 'Outro'] as $value => $label)<option value="{{ $value }}" @selected($field('dados_produto.tipo_imovel') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label class="lbl" for="cep_imovel">CEP</label><input class="inp mt-1" id="cep_imovel" name="dados_produto[cep_imovel]" maxlength="9" value="{{ $field('dados_produto.cep_imovel') }}"></div>
            <div class="md:col-span-3"><label class="lbl" for="endereco_imovel">Endereço completo</label><input class="inp mt-1" id="endereco_imovel" name="dados_produto[endereco_imovel]" maxlength="180" value="{{ $field('dados_produto.endereco_imovel') }}"></div>
            <div class="md:col-span-3"><label class="lbl" for="cidade_imovel">Cidade</label><input class="inp mt-1" id="cidade_imovel" name="dados_produto[cidade_imovel]" maxlength="80" value="{{ $field('dados_produto.cidade_imovel') }}"></div>
            <div><label class="lbl" for="uf_imovel">UF</label><input class="inp mt-1" id="uf_imovel" name="dados_produto[uf_imovel]" maxlength="2" value="{{ $field('dados_produto.uf_imovel') }}"></div>
          </div>
        </section>

        <section id="vidasPanel" class="card p-5 product-panel hidden" data-products="Vida,Saúde">
          <div class="flex items-center justify-between gap-3 mb-4"><div class="flex items-center gap-2"><span class="step-n">3</span><h2 class="font-head font-semibold text-navy">Vidas Seguradas</h2></div><button type="button" id="addVida" class="text-sm font-semibold text-orange">+ Adicionar vida</button></div>
          <div id="vidas" class="space-y-3"></div>
        </section>

        <section id="benefPanel" class="card p-5 product-panel hidden" data-products="Vida,Previdência">
          <div class="flex items-center justify-between gap-3 mb-2"><div class="flex items-center gap-2"><span class="step-n">4</span><h2 class="font-head font-semibold text-navy">Beneficiários</h2></div><button type="button" id="addBenef" class="text-sm font-semibold text-orange">+ Adicionar beneficiário</button></div>
          <p class="text-xs text-slate-500 mb-4">Se informados, os percentuais devem somar exatamente 100%.</p>
          <div id="beneficiarios" class="space-y-3"></div>
          <div class="mt-3 text-sm">Total: <strong id="benefTotal">0%</strong> <span id="benefStatus"></span></div>
        </section>

        <section class="card p-5">
          <div class="flex items-center gap-2 mb-4"><span class="step-n">5</span><h2 class="font-head font-semibold text-navy">Coberturas</h2></div>
          @foreach ($coberturasPorProduto as $produto => $coberturas)
            <div class="coverage-grid coverage-product hidden" data-product="{{ $produto }}">
              @foreach ($coberturas as $cobertura)
                <label class="coverage-option"><input type="checkbox" name="coberturas[]" value="{{ $cobertura }}" @checked(in_array($cobertura, (array) $field('coberturas', []), true))> {{ $cobertura }}</label>
              @endforeach
            </div>
          @endforeach
          <p id="coverageHint" class="text-sm text-slate-500">Selecione primeiro o produto.</p>
        </section>

        <section class="card p-5">
          <div class="flex items-center gap-2 mb-4"><span class="step-n">6</span><h2 class="font-head font-semibold text-navy">Plano Financeiro</h2></div>
          <div class="grid md:grid-cols-3 gap-4">
            <div><label class="lbl" for="valor_mensal">Valor mensal (R$)</label><input type="number" step="0.01" min="0.01" class="inp mt-1" id="valor_mensal" name="valor_mensal" required value="{{ $field('valor_mensal') }}"></div>
            <div><label class="lbl" for="primeiro_vencimento">Primeiro vencimento</label><input type="date" class="inp mt-1" id="primeiro_vencimento" name="primeiro_vencimento" required value="{{ $field('primeiro_vencimento') }}"></div>
            <div class="rounded-lg bg-blue-50 p-3 text-sm text-slate-600">Serão preparadas 12 parcelas mensais. Parcelas já liquidadas não são apagadas em futuras edições.</div>
          </div>
        </section>

        <div class="flex justify-end gap-3 pb-10">
          <a href="{{ route('clientes.show', $cliente) }}" class="bg-white border border-line px-5 py-2.5 rounded-md text-sm font-semibold">Cancelar</a>
          <button type="submit" id="submitApolice" class="bg-navy text-white px-6 py-2.5 rounded-md text-sm font-semibold">{{ $isEditing ? 'Salvar alterações' : 'Salvar proposta/apólice →' }}</button>
        </div>
      </form>
    </main>
  </div>
</div>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script>
const ramoSelect=document.getElementById('ramo_id');
const vidasEl=document.getElementById('vidas'),benefEl=document.getElementById('beneficiarios');
let vidaSeq=0,benefSeq=0;
const selectedProduct=()=>ramoSelect.options[ramoSelect.selectedIndex]?.dataset.nome||'';

function vidaRow(data={}){
  const index=vidaSeq++,row=document.createElement('div');row.className='dynamic-row';
  row.innerHTML=`<div><label class="lbl">Nome da pessoa segurada</label><input class="inp mt-1 vida-nome" name="vidas[${index}][nome]" maxlength="150"></div>
  <div><label class="lbl">Parentesco</label><select class="inp mt-1 vida-parentesco" name="vidas[${index}][parentesco]"><option value="TITULAR">Titular</option><option value="CONJUGE">Cônjuge</option><option value="FILHO">Filho(a)</option><option value="PAI_MAE">Pai/Mãe</option><option value="OUTRO">Outro</option></select></div>
  <div><label class="lbl">Nascimento</label><input type="date" class="inp mt-1 vida-nascimento" name="vidas[${index}][nascimento]"></div>
  <div><label class="lbl">Capital individual</label><input type="number" step="0.01" min="0" class="inp mt-1 vida-capital" name="vidas[${index}][capital]"></div>
  <button type="button" class="remove-row text-red-600 min-h-10" aria-label="Remover vida">✕</button>`;
  row.querySelector('.vida-nome').value=data.nome||'';row.querySelector('.vida-parentesco').value=data.parentesco||'OUTRO';
  row.querySelector('.vida-nascimento').value=data.nascimento||'';row.querySelector('.vida-capital').value=data.capital||'';
  row.querySelector('.remove-row').onclick=()=>row.remove();vidasEl.appendChild(row);
}
function beneficiarioRow(data={}){
  const index=benefSeq++,row=document.createElement('div');row.className='benef-row';
  row.innerHTML=`<div><label class="lbl">Nome do beneficiário</label><input class="inp mt-1 benef-nome" name="beneficiarios[${index}][nome]" maxlength="150"></div>
  <div><label class="lbl">Parentesco</label><input class="inp mt-1 benef-parentesco" name="beneficiarios[${index}][parentesco]" maxlength="30"></div>
  <div><label class="lbl">Percentual</label><input type="number" step="0.01" min="0.01" max="100" class="inp mt-1 benef-pct" name="beneficiarios[${index}][percentual]"></div>
  <button type="button" class="remove-row text-red-600 min-h-10" aria-label="Remover beneficiário">✕</button>`;
  row.querySelector('.benef-nome').value=data.nome||'';row.querySelector('.benef-parentesco').value=data.parentesco||'';
  row.querySelector('.benef-pct').value=data.percentual||'';row.querySelector('.benef-pct').addEventListener('input',benefTotal);
  row.querySelector('.remove-row').onclick=()=>{row.remove();benefTotal()};benefEl.appendChild(row);
}
function benefTotal(){
  const inputs=[...benefEl.querySelectorAll('.benef-pct')],total=inputs.reduce((sum,input)=>sum+(parseFloat(input.value)||0),0);
  document.getElementById('benefTotal').textContent=total.toFixed(2).replace('.00','')+'%';
  const status=document.getElementById('benefStatus');
  if(!inputs.length){status.textContent='';return}
  status.textContent=Math.abs(total-100)<.001?'✓ Percentual completo':(total<100?`Faltam ${(100-total).toFixed(2)}%`:`Excede ${(total-100).toFixed(2)}%`);
  status.className=Math.abs(total-100)<.001?'text-green-700':'text-red-700';
}
function updateProduct(){
  const product=selectedProduct();
  document.querySelectorAll('.product-panel').forEach(panel=>{
    const visible=panel.dataset.products.split(',').includes(product);panel.classList.toggle('hidden',!visible);
    panel.querySelectorAll('input,select,button').forEach(el=>el.disabled=!visible);
  });
  document.querySelectorAll('.coverage-product').forEach(group=>{
    const visible=group.dataset.product===product;group.classList.toggle('hidden',!visible);
    group.querySelectorAll('input').forEach(input=>input.disabled=!visible);
  });
  document.getElementById('coverageHint').classList.toggle('hidden',Boolean(product));
}
document.getElementById('addVida').onclick=()=>vidaRow();
document.getElementById('addBenef').onclick=()=>beneficiarioRow();
@foreach ((array) $field('vidas', []) as $vida)
vidaRow(@json($vida));
@endforeach
@foreach ((array) $field('beneficiarios', []) as $beneficiario)
beneficiarioRow(@json($beneficiario));
@endforeach
benefTotal();ramoSelect.addEventListener('change',updateProduct);updateProduct();
document.getElementById('cep_imovel').addEventListener('input',e=>e.target.value=CI.mCEP(e.target.value));
document.getElementById('uf_imovel').addEventListener('input',e=>e.target.value=e.target.value.toUpperCase().replace(/[^A-Z]/g,'').slice(0,2));
document.getElementById('apoliceForm').addEventListener('submit',()=>{
  const button=document.getElementById('submitApolice');button.disabled=true;button.textContent='Salvando…';
});
</script>
</body>
</html>
