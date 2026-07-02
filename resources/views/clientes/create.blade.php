@php
  $isEditing = $cliente !== null;
  $formValues = array_replace_recursive($clienteForm, session()->getOldInput());
  $field = static fn (string $key, mixed $default = null): mixed => data_get($formValues, $key, $default);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $isEditing ? 'Editar cliente' : 'Cadastro de Cliente' }} — Clube Investvida</title>
<link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/laravel-utilities.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
<style>
  body{font-family:'Inter',sans-serif;background:#F4F7F9;color:#1A1C1E}
  h1,h2,h3,.font-head{font-family:'Manrope',sans-serif}
  .lbl{font-size:.72rem;font-weight:600;letter-spacing:.02em;text-transform:uppercase;color:#475569}
  .inp{width:100%;border:1px solid #E2E8F0;border-radius:.375rem;padding:.6rem .75rem;font-size:.92rem;background:#fff;transition:border .15s,box-shadow .15s}
  .inp:focus{outline:none;border-color:#003461;box-shadow:0 0 0 3px rgba(0,75,135,.12)}
  .inp.ok{border-color:#1E7A3D;box-shadow:0 0 0 3px rgba(30,122,61,.10)}
  .inp.err{border-color:#B3261E;box-shadow:0 0 0 3px rgba(179,38,30,.10)}
  .card{background:#fff;border:1px solid #E2E8F0;border-radius:.75rem}
  .chip{font-size:.7rem;font-weight:600;padding:.15rem .5rem;border-radius:999px}
  .btn-primary{background:#003461;color:#fff}.btn-primary:hover{background:#00284c}
  .btn-primary:disabled{opacity:.6;cursor:wait}
  .btn-ghost{background:#fff;border:1px solid #E2E8F0}.btn-ghost:hover{background:#f8fafc}
  .step-n{width:1.6rem;height:1.6rem;border-radius:999px;background:#003461;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700}
  .hint{font-size:.72rem}
  .nav a,.nav .nav-disabled{display:flex;gap:.6rem;align-items:center;padding:.55rem .75rem;border-radius:.5rem;font-size:.9rem;color:#cbd5e1}
  .nav a:hover{background:rgba(255,255,255,.06);color:#fff}
  .nav a.on{background:rgba(255,255,255,.10);color:#fff;font-weight:600;box-shadow:inset 3px 0 0 #FF6B00}
  .nav .nav-disabled{color:#718198;cursor:not-allowed}.nav .nav-disabled small{margin-left:auto;font-size:.6rem}
  .contact-row{display:grid;grid-template-columns:9rem minmax(12rem,1fr) 2.5rem;gap:.5rem;align-items:center}
  .email-row{display:grid;grid-template-columns:minmax(12rem,1fr) 2.5rem;gap:.5rem;align-items:center}
  .phone-type,.phone-number{min-width:0}
  .remove-contact{display:flex;align-items:center;justify-content:center;min-height:42px}
  @media(max-width:520px){
    .contact-row{grid-template-columns:minmax(0,1fr) 2.25rem}
    .contact-row .phone-type{grid-column:1;grid-row:1}
    .contact-row .phone-number{grid-column:1;grid-row:2}
    .contact-row .remove-contact{grid-column:2;grid-row:1 / span 2}
    .email-row{grid-template-columns:minmax(0,1fr) 2.25rem}
  }
</style>
</head>
<body>
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

<main class="w-full max-w-5xl mx-auto px-5 py-6">
  <div class="flex items-end justify-between mb-5 flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-navy">{{ $isEditing ? 'Editar cliente' : 'Cadastro de Cliente' }}</h1>
      <p class="text-sm text-slate-500">{{ $isEditing ? 'Atualize os dados identificados abaixo; toda alteração será registrada.' : 'Preenchimento inteligente: CEP e validações automáticas para você digitar menos.' }}</p>
    </div>
    <div class="inline-flex bg-white border border-line rounded-lg p-1">
      <button type="button" id="btnPF" class="px-4 py-1.5 rounded-md text-sm font-semibold bg-navy text-white">Pessoa Física</button>
      <button type="button" id="btnPJ" class="px-4 py-1.5 rounded-md text-sm font-semibold text-slate-600">Pessoa Jurídica</button>
    </div>
  </div>

  @if (session('status'))
    <div role="status" class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
      ✓ {{ session('status') }}
    </div>
  @endif

  @if ($errors->any())
    <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      <p class="font-semibold">Revise os campos indicados:</p>
      <ul class="mt-1 list-disc pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form id="form" method="POST" action="{{ $isEditing ? route('clientes.update', $cliente) : route('clientes.store') }}" class="space-y-5">
    @csrf
    @if ($isEditing) @method('PUT') @endif
    <input type="hidden" id="pessoa" name="pessoa" value="{{ $field('pessoa', 'PF') }}">

    <section class="card p-5">
      <div class="flex items-center gap-2 mb-4"><span class="step-n">1</span><h2 class="font-head font-semibold text-navy">Dados Básicos</h2></div>

      <div class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="lbl" id="lblNome" for="nome">Nome completo</label>
          <input class="inp mt-1" id="nome" name="nome" required maxlength="150" placeholder="Nome do cliente" value="{{ $field('nome') }}">
        </div>

        <div>
          <label class="lbl" id="lblDoc" for="cpf_cnpj">CPF</label>
          <input class="inp mt-1" id="cpf_cnpj" name="cpf_cnpj" required inputmode="numeric" placeholder="000.000.000-00" value="{{ $field('cpf_cnpj') }}">
          <p class="hint mt-1 text-slate-400" id="cpfMsg">Validação automática ao digitar.</p>
        </div>

        <div id="wrapNasc">
          <label class="lbl" for="nascimento">Data de nascimento</label>
          <input type="date" class="inp mt-1" id="nascimento" name="nascimento" value="{{ $field('nascimento') }}">
        </div>

        <div>
          <label class="lbl" for="estado_civil">Estado civil</label>
          <select class="inp mt-1" id="estado_civil" name="estado_civil">
            <option value="">Selecione…</option>
            <option value="SOLTEIRO" @selected($field('estado_civil') === 'SOLTEIRO')>Solteiro</option>
            <option value="CASADO" @selected($field('estado_civil') === 'CASADO')>Casado</option>
            <option value="DIVORCIADO" @selected($field('estado_civil') === 'DIVORCIADO')>Divorciado</option>
            <option value="VIUVO" @selected($field('estado_civil') === 'VIUVO')>Viúvo</option>
            <option value="UNIAO_ESTAVEL" @selected($field('estado_civil') === 'UNIAO_ESTAVEL')>União Estável</option>
          </select>
        </div>

        <div>
          <label class="lbl" for="sexo">Sexo</label>
          <select class="inp mt-1" id="sexo" name="sexo">
            <option value="">Selecione…</option>
            <option value="F" @selected($field('sexo') === 'F')>Feminino</option>
            <option value="M" @selected($field('sexo') === 'M')>Masculino</option>
            <option value="OUTRO" @selected($field('sexo') === 'OUTRO')>Outro</option>
          </select>
        </div>

        <div>
          <label class="lbl" for="profissao">Profissão</label>
          <div class="ac mt-1">
            <input class="inp" id="profissao" name="profissao" maxlength="120" placeholder="Digite ao menos 3 letras" value="{{ $field('profissao') }}"
                   aria-controls="profissaoList" aria-expanded="false">
            <div id="profissaoList" class="ac-list" role="listbox" style="display:none"></div>
          </div>
          <p class="hint mt-1 text-slate-400">Sugestões da CBO; texto manual permitido.</p>
        </div>

        <div>
          <label class="lbl" for="faixa_renda">Faixa de renda</label>
          <select class="inp mt-1" id="faixa_renda" name="faixa_renda">
            <option value="">Selecione…</option>
            @foreach (['Até R$ 2.500', 'De R$ 2.500,01 a R$ 5.000', 'De R$ 5.000,01 a R$ 10.000', 'Acima de R$ 10.000'] as $faixa)
              <option value="{{ $faixa }}" @selected($field('faixa_renda') === $faixa)>{{ $faixa }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="lbl" for="tipo_cliente">Tipo de cliente</label>
          <select class="inp mt-1" id="tipo_cliente" name="tipo_cliente">
            <option value="PROSPECT" @selected($field('tipo_cliente', 'PROSPECT') === 'PROSPECT')>Prospect</option>
            <option value="EFETIVO" @selected($field('tipo_cliente') === 'EFETIVO')>Efetivo</option>
            <option value="RELACIONAMENTO" @selected($field('tipo_cliente') === 'RELACIONAMENTO')>Relacionamento</option>
            <option value="CONDUTOR" @selected($field('tipo_cliente') === 'CONDUTOR')>Condutor</option>
            <option value="LOCADOR" @selected($field('tipo_cliente') === 'LOCADOR')>Locador</option>
          </select>
        </div>
        <div>
          <label class="lbl" for="intermedio">Canal de origem do cliente</label>
          <select class="inp mt-1" id="intermedio" name="intermedio">
            <option value="">Selecione…</option>
            @foreach (\App\Models\Cliente::ORIGENS as $origem)
              <option value="{{ $origem }}" @selected($field('intermedio') === $origem)>{{ $origem }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div id="conjugeBox" class="mt-4 hidden">
        <div class="rounded-lg bg-blue-50/60 border border-blue-100 p-4">
          <div class="flex items-center gap-2 mb-3">
            <span class="chip bg-navy text-white">Casado</span>
            <h3 class="font-head font-semibold text-navy text-sm">Dados do Cônjuge</h3>
          </div>
          <div class="grid md:grid-cols-3 gap-4">
            <div><label class="lbl" for="conjNome">Nome do cônjuge</label><input class="inp mt-1" id="conjNome" name="conjuge[nome]" maxlength="150" value="{{ $field('conjuge.nome') }}"></div>
            <div><label class="lbl" for="conjCpf">CPF do cônjuge</label><input class="inp mt-1" id="conjCpf" name="conjuge[cpf]" inputmode="numeric" placeholder="000.000.000-00" value="{{ $field('conjuge.cpf') }}"></div>
            <div><label class="lbl" for="conjNasc">Nascimento do cônjuge</label><input type="date" class="inp mt-1" id="conjNasc" name="conjuge[nascimento]" value="{{ $field('conjuge.nascimento') }}"></div>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <label class="inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
          <input type="checkbox" name="tem_cnh" value="1" id="temCnh" class="accent-navy w-4 h-4" @checked($field('tem_cnh'))> Possui CNH
        </label>
        <div id="cnhBox" class="hidden mt-3 grid md:grid-cols-4 gap-4">
          <div><label class="lbl" for="cnhNumero">Nº registro da CNH do titular</label><input class="inp mt-1" id="cnhNumero" name="cnh[numero_registro]" maxlength="20" value="{{ $field('cnh.numero_registro') }}"></div>
          <div><label class="lbl" for="cnhCat">Categoria</label><input class="inp mt-1" id="cnhCat" name="cnh[categoria]" maxlength="5" placeholder="Ex.: B" value="{{ $field('cnh.categoria') }}"></div>
          <div><label class="lbl" for="cnhValidade">Validade</label><input type="date" class="inp mt-1" id="cnhValidade" name="cnh[validade]" value="{{ $field('cnh.validade') }}"></div>
          <div><label class="lbl" for="cnhPrimeira">1ª habilitação</label><input type="date" class="inp mt-1" id="cnhPrimeira" name="cnh[primeira_habilitacao]" value="{{ $field('cnh.primeira_habilitacao') }}"></div>
        </div>
      </div>
    </section>

    <section class="card p-5">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2"><span class="step-n">2</span><h2 class="font-head font-semibold text-navy">Endereços</h2></div>
        <button type="button" id="addEnd" class="text-sm font-semibold text-orange hover:underline">+ Adicionar endereço</button>
      </div>
      <div id="enderecos" class="space-y-3"></div>
    </section>

    <section class="card p-5">
      <div class="flex items-center gap-2 mb-4"><span class="step-n">3</span><h2 class="font-head font-semibold text-navy">Telefones e E-mails</h2></div>
      <div class="grid md:grid-cols-2 gap-5">
        <div>
          <div class="flex items-center justify-between mb-2"><span class="lbl">Telefones</span>
            <button type="button" id="addTel" class="text-xs font-semibold text-orange hover:underline">+ Adicionar</button></div>
          <div id="telefones" class="space-y-2"></div>
        </div>
        <div>
          <div class="flex items-center justify-between mb-2"><span class="lbl">E-mails</span>
            <button type="button" id="addEml" class="text-xs font-semibold text-orange hover:underline">+ Adicionar</button></div>
          <div id="emails" class="space-y-2"></div>
        </div>
      </div>
    </section>

    <div class="flex items-center justify-between flex-wrap gap-3 pb-10">
      <p class="hint text-slate-400">Os dados são validados antes de salvar. Rascunho é guardado automaticamente neste navegador.</p>
      <div class="flex gap-3">
        <button type="button" id="btnDraft" class="btn-ghost px-5 py-2.5 rounded-md text-sm font-semibold">Salvar rascunho</button>
        <button type="submit" id="btnSubmit" class="btn-primary px-6 py-2.5 rounded-md text-sm font-semibold">{{ $isEditing ? 'Salvar alterações' : 'Finalizar cadastro →' }}</button>
      </div>
    </div>
  </form>
</main>

<script>
const maskCPF = v => v.replace(/\D/g,'').slice(0,11)
  .replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2');
const maskCNPJ = v => v.replace(/\D/g,'').slice(0,14)
  .replace(/^(\d{2})(\d)/,'$1.$2').replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3')
  .replace(/\.(\d{3})(\d)/,'.$1/$2').replace(/(\d{4})(\d)/,'$1-$2');
const maskCEP = v => v.replace(/\D/g,'').slice(0,8).replace(/(\d{5})(\d)/,'$1-$2');
const maskFone = v => { v=v.replace(/\D/g,'').slice(0,11);
  return v.length>10 ? v.replace(/(\d{2})(\d{5})(\d{4})/,'($1) $2-$3')
                     : v.replace(/(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3').trim(); };

function cpfValido(cpf){
  cpf = cpf.replace(/\D/g,''); if(cpf.length!==11||/^(\d)\1{10}$/.test(cpf)) return false;
  let s=0; for(let i=0;i<9;i++) s+=parseInt(cpf[i])*(10-i);
  let d=(s*10)%11; if(d===10)d=0; if(d!==parseInt(cpf[9])) return false;
  s=0; for(let i=0;i<10;i++) s+=parseInt(cpf[i])*(11-i);
  d=(s*10)%11; if(d===10)d=0; return d===parseInt(cpf[10]);
}
function cnpjValido(cnpj){
  cnpj=cnpj.replace(/\D/g,''); if(cnpj.length!==14||/^(\d)\1{13}$/.test(cnpj)) return false;
  const dig=(base,pesos)=>{const soma=pesos.reduce((t,p,i)=>t+parseInt(base[i])*p,0),r=soma%11;return r<2?0:11-r};
  const d1=dig(cnpj.slice(0,12),[5,4,3,2,9,8,7,6,5,4,3,2]);
  const d2=dig(cnpj.slice(0,12)+d1,[6,5,4,3,2,9,8,7,6,5,4,3,2]);
  return d1===parseInt(cnpj[12])&&d2===parseInt(cnpj[13]);
}
function ligaCPF(input,msg){
  input.addEventListener('input',()=>{ input.value=maskCPF(input.value);
    const dig=input.value.replace(/\D/g,''); input.classList.remove('ok','err');
    if(!dig){ if(msg){msg.textContent='Validação automática ao digitar.';msg.className='hint mt-1 text-slate-400';} return; }
    if(dig.length<11){ if(msg){msg.textContent='Digite os 11 dígitos.';msg.className='hint mt-1 text-slate-400';} return; }
    if(cpfValido(dig)){ input.classList.add('ok'); if(msg){msg.textContent='✓ CPF válido';msg.className='hint mt-1 text-green-700 font-medium';} }
    else { input.classList.add('err'); if(msg){msg.textContent='✗ CPF inválido';msg.className='hint mt-1 text-red-700 font-medium';} }
  });
}
ligaCPF(document.getElementById('conjCpf'), null);

const btnPF=document.getElementById('btnPF'), btnPJ=document.getElementById('btnPJ');
const docInput=document.getElementById('cpf_cnpj'), docMsg=document.getElementById('cpfMsg'), pessoaInput=document.getElementById('pessoa');
function atualizaDocumento(){
  const pf=pessoaInput.value==='PF', dig=docInput.value.replace(/\D/g,'');
  docInput.value=pf?maskCPF(dig):maskCNPJ(dig);
  docInput.classList.remove('ok','err');
  if(!dig){docMsg.textContent='Validação automática ao digitar.';docMsg.className='hint mt-1 text-slate-400';return}
  const completo=dig.length===(pf?11:14), valido=pf?cpfValido(dig):cnpjValido(dig);
  if(!completo){docMsg.textContent=`Digite os ${pf?11:14} dígitos.`;docMsg.className='hint mt-1 text-slate-400';return}
  docInput.classList.add(valido?'ok':'err');
  docMsg.textContent=valido?`✓ ${pf?'CPF':'CNPJ'} válido`:`✗ ${pf?'CPF':'CNPJ'} inválido`;
  docMsg.className=`hint mt-1 ${valido?'text-green-700':'text-red-700'} font-medium`;
}
docInput.addEventListener('input',atualizaDocumento);
function setPessoa(pf){
  pessoaInput.value=pf?'PF':'PJ';
  btnPF.className='px-4 py-1.5 rounded-md text-sm font-semibold '+(pf?'bg-navy text-white':'text-slate-600');
  btnPJ.className='px-4 py-1.5 rounded-md text-sm font-semibold '+(!pf?'bg-navy text-white':'text-slate-600');
  document.getElementById('lblNome').textContent=pf?'Nome completo':'Razão social';
  document.getElementById('lblDoc').textContent=pf?'CPF':'CNPJ';
  docInput.placeholder=pf?'000.000.000-00':'00.000.000/0000-00';
  document.getElementById('wrapNasc').style.display=pf?'':'none';
  atualizaDocumento();
}
btnPF.onclick=()=>setPessoa(true); btnPJ.onclick=()=>setPessoa(false);

document.getElementById('estado_civil').addEventListener('change',e=>{
  document.getElementById('conjugeBox').classList.toggle('hidden',!['CASADO','UNIAO_ESTAVEL'].includes(e.target.value));
});
document.getElementById('temCnh').addEventListener('change',e=>{
  document.getElementById('cnhBox').classList.toggle('hidden',!e.target.checked);
});

const elEnd=document.getElementById('enderecos'); let endSeq=0;
function novoEndereco(data={},forcedIndex=null){
  const index=forcedIndex===null?endSeq++:forcedIndex; endSeq=Math.max(endSeq,index+1);
  const i=document.createElement('div'); i.className='rounded-lg border border-line p-4 bg-surface/40';
  i.innerHTML=`
    <div class="flex items-center justify-between mb-3">
      <label class="inline-flex items-center gap-2 text-xs text-slate-600">
        <input type="radio" name="endereco_padrao" value="${index}" class="accent-navy"> Endereço padrão</label>
      <button type="button" class="rmEnd text-xs text-red-600 hover:underline">Remover</button>
    </div>
    <div class="grid md:grid-cols-6 gap-3">
      <div class="md:col-span-2"><label class="lbl">Tipo</label><select name="enderecos[${index}][tipo]" class="inp mt-1 tipo"><option value="RESIDENCIAL">Residencial</option><option value="COMERCIAL">Comercial</option><option value="COBRANCA">Cobrança</option><option value="OUTRO">Outro</option></select></div>
      <div class="md:col-span-2"><label class="lbl">CEP</label>
        <div class="relative"><input name="enderecos[${index}][cep]" class="inp mt-1 cep" inputmode="numeric" placeholder="00000-000">
        <span class="cepStatus absolute right-3 top-3 text-xs"></span></div></div>
      <div class="md:col-span-2"><label class="lbl">Logradouro</label><input name="enderecos[${index}][logradouro]" class="inp mt-1 logradouro" placeholder="Rua / Avenida"></div>
      <div><label class="lbl">Número</label><input name="enderecos[${index}][numero]" class="inp mt-1 numero"></div>
      <div class="md:col-span-2"><label class="lbl">Bairro</label><input name="enderecos[${index}][bairro]" class="inp mt-1 bairro"></div>
      <div class="md:col-span-2"><label class="lbl">Cidade</label><input name="enderecos[${index}][cidade]" class="inp mt-1 cidade"></div>
      <div><label class="lbl">UF</label><input name="enderecos[${index}][uf]" class="inp mt-1 uf" maxlength="2"></div>
      <div><label class="lbl">Complemento</label><input name="enderecos[${index}][complemento]" class="inp mt-1 compl"></div>
    </div>`;
  for(const [key,selector] of Object.entries({tipo:'.tipo',cep:'.cep',logradouro:'.logradouro',numero:'.numero',bairro:'.bairro',cidade:'.cidade',uf:'.uf',complemento:'.compl'})){
    if(data[key]!==undefined&&data[key]!==null)i.querySelector(selector).value=data[key];
  }
  const radio=i.querySelector('input[type="radio"]');
  radio.checked=String(window.initialAddressDefault)===String(index)||(window.initialAddressDefault===null&&elEnd.children.length===0);
  const cep=i.querySelector('.cep'),st=i.querySelector('.cepStatus');
  cep.value=maskCEP(cep.value);
  cep.addEventListener('input',()=>{cep.value=maskCEP(cep.value)});
  cep.addEventListener('blur',async()=>{
    const d=cep.value.replace(/\D/g,'');if(d.length!==8)return;
    st.textContent='buscando…';st.className='cepStatus absolute right-3 top-3 text-xs text-slate-400';
    try{
      const r=await fetch(`https://viacep.com.br/ws/${d}/json/`),j=await r.json();
      if(j.erro){st.textContent='não encontrado';st.className='cepStatus absolute right-3 top-3 text-xs text-red-600';return}
      i.querySelector('.logradouro').value=j.logradouro||'';
      i.querySelector('.bairro').value=j.bairro||'';
      i.querySelector('.cidade').value=j.localidade||'';
      i.querySelector('.uf').value=j.uf||'';
      st.textContent='✓';st.className='cepStatus absolute right-3 top-3 text-xs text-green-700 font-semibold';
      i.querySelector('.numero').focus();
    }catch(e){st.textContent='falha';st.className='cepStatus absolute right-3 top-3 text-xs text-red-600'}
  });
  i.querySelector('.rmEnd').onclick=()=>i.remove();elEnd.appendChild(i);
}
document.getElementById('addEnd').onclick=()=>novoEndereco();

const elTel=document.getElementById('telefones'),elEml=document.getElementById('emails');let telSeq=0,emlSeq=0;
function novoTel(data={},forcedIndex=null){
  const index=forcedIndex===null?telSeq++:forcedIndex;telSeq=Math.max(telSeq,index+1);
  const i=document.createElement('div');i.className='contact-row';
  i.innerHTML=`<select name="telefones[${index}][tipo]" class="inp tipo phone-type" aria-label="Tipo de telefone"><option value="CELULAR">Celular</option><option value="WHATSAPP">WhatsApp</option><option value="RESIDENCIAL">Residencial</option><option value="COMERCIAL">Comercial</option><option value="0800">0800</option><option value="OUTRO">Outro</option></select>
    <input name="telefones[${index}][numero]" class="inp fone phone-number" inputmode="tel" autocomplete="tel" maxlength="20" aria-label="Número do telefone" placeholder="(00) 00000-0000">
    <button type="button" class="rm remove-contact text-red-600" aria-label="Remover telefone">✕</button>`;
  i.querySelector('.tipo').value=data.tipo||'CELULAR';const f=i.querySelector('.fone');f.value=maskFone(data.numero||'');
  f.addEventListener('input',()=>f.value=maskFone(f.value));i.querySelector('.rm').onclick=()=>i.remove();elTel.appendChild(i);
}
function novoEml(data={},forcedIndex=null){
  const index=forcedIndex===null?emlSeq++:forcedIndex;emlSeq=Math.max(emlSeq,index+1);
  const i=document.createElement('div');i.className='email-row';
  i.innerHTML=`<input type="email" name="emails[${index}][email]" class="inp email" autocomplete="email" aria-label="Endereço de e-mail" placeholder="email@exemplo.com">
    <button type="button" class="rm remove-contact text-red-600" aria-label="Remover e-mail">✕</button>`;
  i.querySelector('.email').value=data.email||'';i.querySelector('.rm').onclick=()=>i.remove();elEml.appendChild(i);
}
document.getElementById('addTel').onclick=()=>novoTel();
document.getElementById('addEml').onclick=()=>novoEml();

const draftKey=@json($isEditing ? 'cliente-edit-'.$cliente->getKey() : 'cliente-create');
const hasServerOldInput=@json(session()->hasOldInput());
let draftData=null;
if(!hasServerOldInput){
  try{draftData=JSON.parse(localStorage.getItem(draftKey)||'null')}catch(e){draftData=null}
}
window.initialAddressDefault=draftData?.enderecoPadrao??@json($field('endereco_padrao'));
const initialEnderecos=draftData?.enderecos??@json($field('enderecos', [[]]));
const initialTelefones=draftData?.telefones??@json($field('telefones', [[]]));
const initialEmails=draftData?.emails??@json($field('emails', [[]]));
Object.entries(initialEnderecos).forEach(([index,data])=>novoEndereco(data,Number(index)));
Object.entries(initialTelefones).forEach(([index,data])=>novoTel(data,Number(index)));
Object.entries(initialEmails).forEach(([index,data])=>novoEml(data,Number(index)));

const F=document.getElementById('form');
function salvarRascunho(){
  const fields={};
  F.querySelectorAll('input[id],select[id]').forEach(el=>fields[el.id]=el.type==='checkbox'?el.checked:el.value);
  const enderecoPadrao=[...elEnd.children].findIndex(row=>row.querySelector('input[name="endereco_padrao"]')?.checked);
  const enderecos=[...elEnd.children].map(row=>({
    tipo:row.querySelector('.tipo').value,cep:row.querySelector('.cep').value,
    logradouro:row.querySelector('.logradouro').value,numero:row.querySelector('.numero').value,
    bairro:row.querySelector('.bairro').value,cidade:row.querySelector('.cidade').value,
    uf:row.querySelector('.uf').value,complemento:row.querySelector('.compl').value
  }));
  const telefones=[...elTel.children].map(row=>({tipo:row.querySelector('.tipo').value,numero:row.querySelector('.fone').value}));
  const emails=[...elEml.children].map(row=>({email:row.querySelector('.email').value}));
  localStorage.setItem(draftKey,JSON.stringify({fields,enderecoPadrao,enderecos,telefones,emails}));
}
F.addEventListener('input',()=>{clearTimeout(window._t);window._t=setTimeout(salvarRascunho,800)});
document.getElementById('btnDraft').onclick=()=>{salvarRascunho();alert('Rascunho salvo neste navegador.')};
if(draftData?.fields){
  Object.entries(draftData.fields).forEach(([k,v])=>{
    const el=document.getElementById(k);if(!el)return;
    if(el.type==='checkbox')el.checked=Boolean(v);else el.value=v??'';
  });
}

setPessoa(pessoaInput.value!=='PJ');
document.getElementById('estado_civil').dispatchEvent(new Event('change'));
document.getElementById('temCnh').dispatchEvent(new Event('change'));
if(docInput.value)atualizaDocumento();
if(document.getElementById('conjCpf').value)document.getElementById('conjCpf').dispatchEvent(new Event('input'));

F.addEventListener('submit',e=>{
  const dig=docInput.value.replace(/\D/g,''),pf=pessoaInput.value==='PF';
  if(!document.getElementById('nome').value.trim()){e.preventDefault();return alert('Informe o nome do cliente.')}
  if(!(pf?cpfValido(dig):cnpjValido(dig))){e.preventDefault();return alert(`O ${pf?'CPF':'CNPJ'} informado é inválido.`)}
  const button=document.getElementById('btnSubmit');button.disabled=true;button.textContent='Salvando…';
});
</script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script>
CI.autocomplete(
  document.getElementById('profissao'),
  document.getElementById('profissaoList'),
  {url: @json(route('api.profissoes.index')), field: 'titulo', min: 3}
);
</script>
  </div>
</div>
</body>
</html>
