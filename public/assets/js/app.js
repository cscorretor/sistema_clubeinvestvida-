/* Clube Investvida — helpers de frontend (app.js) */
window.CI=(function(){
  function onlyD(v){return (v||'').replace(/\D/g,'');}
  function mCPF(v){return onlyD(v).slice(0,11).replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2');}
  function mCNPJ(v){return onlyD(v).slice(0,14).replace(/(\d{2})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1/$2').replace(/(\d{4})(\d{1,2})$/,'$1-$2');}
  function mCEP(v){return onlyD(v).slice(0,8).replace(/(\d{5})(\d)/,'$1-$2');}
  function mFone(v){v=onlyD(v).slice(0,11);return v.length>10?v.replace(/(\d{2})(\d{5})(\d{4})/,'($1) $2-$3'):v.replace(/(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3').trim();}
  function cpfValido(c){c=onlyD(c);if(c.length!=11||/^(\d)\1{10}$/.test(c))return false;var s=0,i,d;for(i=0;i<9;i++)s+=c[i]*(10-i);d=(s*10)%11;if(d==10)d=0;if(d!=c[9])return false;s=0;for(i=0;i<10;i++)s+=c[i]*(11-i);d=(s*10)%11;if(d==10)d=0;return d==c[10];}
  function viaCEP(cep){var x=onlyD(cep);if(x.length!=8)return Promise.reject('cep');return fetch('https://viacep.com.br/ws/'+x+'/json/').then(function(r){return r.json();});}
  /* autocomplete genérico. opts={url:'/api/profissoes', field:'titulo', min:3, fallback:[]} */
  function autocomplete(input,listEl,opts){
    opts=opts||{};var field=opts.field||'titulo',min=opts.min||3,fb=opts.fallback||[],tmr=null,idx=-1;
    function close(){listEl.style.display='none';input.setAttribute('aria-expanded','false');idx=-1;}
    function pick(v){input.value=v;close();}
    function render(arr){
      if(!arr.length){listEl.innerHTML='<div class="ac-empty">Nenhum resultado encontrado</div>';}
      else{listEl.innerHTML=arr.slice(0,10).map(function(v){return '<div class="ac-item" role="option">'+v+'</div>';}).join('');}
      listEl.style.display='block';input.setAttribute('aria-expanded','true');idx=-1;
      Array.prototype.forEach.call(listEl.querySelectorAll('.ac-item'),function(el){el.onclick=function(){pick(el.textContent);};});
    }
    function search(q){
      fetch(opts.url+'?q='+encodeURIComponent(q)).then(function(r){if(!r.ok)throw 0;return r.json();})
        .then(function(d){var a=(Array.isArray(d)?d:(d.items||d.data||[])).map(function(o){return typeof o==='string'?o:(o[field]||'');}).filter(Boolean);render(a);})
        .catch(function(){render(fb.filter(function(p){return p.toLowerCase().indexOf(q.toLowerCase())>=0;}));});
    }
    input.setAttribute('role','combobox');input.setAttribute('aria-autocomplete','list');input.setAttribute('autocomplete','off');
    input.addEventListener('input',function(){var q=input.value.trim();if(tmr)clearTimeout(tmr);if(q.length<min){close();return;}tmr=setTimeout(function(){search(q);},250);});
    input.addEventListener('keydown',function(e){var els=listEl.querySelectorAll('.ac-item');if(listEl.style.display=='none'||!els.length)return;
      if(e.key=='ArrowDown'){e.preventDefault();idx=(idx+1)%els.length;}else if(e.key=='ArrowUp'){e.preventDefault();idx=(idx-1+els.length)%els.length;}
      else if(e.key=='Enter'){if(idx>=0){e.preventDefault();pick(els[idx].textContent);}return;}else if(e.key=='Escape'){close();return;}else return;
      Array.prototype.forEach.call(els,function(el,i){el.setAttribute('aria-selected',i==idx);});els[idx].scrollIntoView({block:'nearest'});});
    document.addEventListener('click',function(e){if(!e.target.closest('.ac')&&e.target!==input)close();});
  }
  function toast(msg){alert(msg);} /* placeholder — backend trocará por toast real */
  function mobileNavigation(){
    var sidebar=document.getElementById('appSidebar'),toggle=document.querySelector('.mobile-nav-toggle');
    if(!sidebar||!toggle)return;
    function setOpen(open){
      sidebar.classList.toggle('is-open',open);
      document.body.classList.toggle('nav-open',open);
      toggle.setAttribute('aria-expanded',open?'true':'false');
      toggle.setAttribute('aria-label',open?'Fechar menu':'Abrir menu');
    }
    toggle.addEventListener('click',function(){setOpen(!sidebar.classList.contains('is-open'));});
    Array.prototype.forEach.call(document.querySelectorAll('[data-sidebar-close],#appSidebar a'),function(el){
      el.addEventListener('click',function(){setOpen(false);});
    });
    document.addEventListener('keydown',function(e){if(e.key==='Escape')setOpen(false);});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',mobileNavigation);else mobileNavigation();
  return {mCPF:mCPF,mCNPJ:mCNPJ,mCEP:mCEP,mFone:mFone,cpfValido:cpfValido,viaCEP:viaCEP,autocomplete:autocomplete,toast:toast,mobileNavigation:mobileNavigation};
})();
