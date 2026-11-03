(function(){
  function init(root){
    var tabs=[].slice.call(root.querySelectorAll('[data-ft-hww-tab]'));
    var panels=[].slice.call(root.querySelectorAll('[data-ft-hww-panel]'));
    if(!tabs.length||!panels.length)return;
    function select(key,updateHash){
      tabs.forEach(function(t){var on=t.getAttribute('data-ft-hww-tab')===key;t.classList.toggle('is-active',on);t.setAttribute('aria-selected',on?'true':'false');});
      panels.forEach(function(p){var on=p.getAttribute('data-ft-hww-panel')===key;p.classList.toggle('is-active',on);p.hidden=!on;});
      if(updateHash && history.replaceState) history.replaceState(null,'','#'+key);
    }
    tabs.forEach(function(t){t.addEventListener('click',function(e){e.preventDefault();select(t.getAttribute('data-ft-hww-tab'),true);});});
    var key=(location.hash||'').replace('#','');
    if(key && panels.some(function(p){return p.getAttribute('data-ft-hww-panel')===key;}))select(key,false);
  }
  function boot(){document.querySelectorAll('.ft-hww-selector').forEach(init);}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();
})();
