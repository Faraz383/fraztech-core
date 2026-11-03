(function(){'use strict';
function init(){
 if(!document.body.classList.contains('home') || document.documentElement.classList.contains('elementor-editor-active'))return;
 var visual=document.querySelector('.hero .hero-visual');
 if(!visual||visual.classList.contains('ft-liquid-motion')||!visual.querySelector('img'))return;
 visual.classList.add('ft-liquid-motion');
 var layer=document.createElement('div');layer.className='ft-liquid-effects';layer.setAttribute('aria-hidden','true');
 ['ft-liquid-orbit','ft-liquid-glint','ft-liquid-card-glow card-a','ft-liquid-card-glow card-b','ft-liquid-card-glow card-c'].forEach(function(c){var el=document.createElement('span');el.className=c;layer.appendChild(el)});
 visual.appendChild(layer);
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
