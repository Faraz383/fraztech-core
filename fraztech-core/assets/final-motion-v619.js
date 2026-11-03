(()=>{
'use strict';
const d=document,b=d.body;if(!b)return;
const fine=matchMedia('(hover: hover) and (pointer: fine)').matches;
const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
const coarse=matchMedia('(hover: none), (pointer: coarse)').matches;

function editor(){
  if(b.classList.contains('elementor-editor-active'))return true;
  try{return !!(window.frameElement&&window.frameElement.id==='elementor-preview-iframe')}
  catch(e){return false}
}
if(editor())return;

/* Remove old visual cursor overlays. Native OS 2D cursor remains visible. */
d.querySelectorAll(
  '[class*="ft-v60"][class*="cursor"],[class*="ft-v61"][class*="cursor"],' +
  '[class*="ft-v61"][class*="trail"],[class*="ft-v61"][class*="burst"]'
).forEach(el=>el.remove());
[...b.classList].forEach(c=>{if(/^ft-v6\d\d-cursor-on$/.test(c)||/^ft-v61\d-cursor-on$/.test(c))b.classList.remove(c)});
[...d.documentElement.classList].forEach(c=>{if(/^ft-v6\d\d-cursor-on$/.test(c)||/^ft-v61\d-cursor-on$/.test(c))d.documentElement.classList.remove(c)});

if(fine){

  /* Native 2D arrow is drawn by the operating system at its normal size.
   * Trail and click particles are decorative and never intercept clicks. */
  const actionSel='a,button,summary,[role="button"],.button,.text-link,.ft-language-trigger,.ft-language-option,input[type="submit"],input[type="button"]';

  let x=-100,y=-100,visible=false,action=false;
  const N=reduced?0:11,nodes=[],pos=[];

  for(let i=0;i<N;i++){
    const el=d.createElement('span');
    el.className='ft-v619-trail-dot';
    const t=i/Math.max(1,N-1);
    el.style.setProperty('--t',t.toFixed(3));
    b.appendChild(el);nodes.push(el);pos.push({x:-100,y:-100});
  }

  let trailRAF=0,settling=false,stopTimer=0;
  /* Keep the decorative trail just behind the native arrow hotspot. */
  const BACK_X=-10;
  const BACK_Y=7;
  function origin(){return {x:x+BACK_X,y:y+BACK_Y}}

  function renderTrail(){
    trailRAF=0;if(!N)return;
    const o=origin();let px=o.x,py=o.y,maxErr=0;
    for(let i=0;i<N;i++){
      const p=pos[i];
      const follow=i===0?.52:Math.max(.24,.38-i*.013);
      p.x+=(px-p.x)*follow;p.y+=(py-p.y)*follow;px=p.x;py=p.y;
      const target=i===0?o:pos[i-1];
      maxErr=Math.max(maxErr,Math.hypot(target.x-p.x,target.y-p.y));
      nodes[i].style.transform=`translate3d(${p.x.toFixed(2)}px,${p.y.toFixed(2)}px,0)`;
    }
    if(visible&&(maxErr>.15||!settling))trailRAF=requestAnimationFrame(renderTrail);
    else if(visible)nodes.forEach(n=>n.classList.add('is-settled'));
  }
  function wakeTrail(){if(!N)return;nodes.forEach(n=>n.classList.remove('is-settled'));if(!trailRAF)trailRAF=requestAnimationFrame(renderTrail)}

  window.addEventListener('pointermove',e=>{
    if(e.pointerType&&e.pointerType!=='mouse'&&e.pointerType!=='pen')return;
    x=e.clientX;y=e.clientY;
    if(!visible){
      visible=true;
      const o=origin();for(const p of pos){p.x=o.x;p.y=o.y}
      nodes.forEach(n=>n.classList.add('is-visible'));
    }
    settling=false;wakeTrail();clearTimeout(stopTimer);
    stopTimer=setTimeout(()=>{settling=true;wakeTrail()},70);
    const next=!!e.target?.closest?.(actionSel);
    if(next!==action){action=next;nodes.forEach(n=>n.classList.toggle('is-action',action))}
  },{passive:true});

  if(!reduced){
    window.addEventListener('pointerdown',e=>{
      if(e.pointerType&&e.pointerType!=='mouse'&&e.pointerType!=='pen')return;
      /* Native cursor click position is exactly e.clientX / e.clientY. */
      const burst=d.createElement('span');
      burst.className='ft-v619-burst';
      burst.style.transform=`translate3d(${e.clientX}px,${e.clientY}px,0)`;
      [[8,-8],[14,-5],[20,-2],[28,0],[22,4],[16,7],[11,9],[18,1]].forEach((v,i)=>{
        const s=d.createElement('i');
        s.style.setProperty('--dx',v[0]+'px');s.style.setProperty('--dy',v[1]+'px');s.style.setProperty('--delay',(i%4*10)+'ms');burst.appendChild(s);
      });
      b.appendChild(burst);setTimeout(()=>burst.remove(),500);
    },{passive:true});
  }

  const hide=()=>{
    visible=false;clearTimeout(stopTimer);
    if(trailRAF){cancelAnimationFrame(trailRAF);trailRAF=0}
    nodes.forEach(n=>n.classList.remove('is-visible'));
  };
  window.addEventListener('blur',hide,{passive:true});
  window.addEventListener('pointerout',e=>{if(!e.relatedTarget)hide()},{passive:true});
}

if(!coarse&&!reduced&&'IntersectionObserver'in window){
  const nodes=[...d.querySelectorAll('main section,main .section-heading,main .service-card,main .category-card,main .article-card,main .solution-card')]
    .filter((el,i,a)=>a.indexOf(el)===i).filter(el=>!el.closest('.ft-language-switcher,#wpadminbar')).slice(0,20);
  const fold=innerHeight*.92;
  nodes.forEach((el,i)=>{el.classList.add('ft-v619-reveal');if(el.getBoundingClientRect().top<fold)el.classList.add('is-visible');else el.style.setProperty('--ft-v619-delay',`${(i%3)*16}ms`)});
  const io=new IntersectionObserver(es=>{for(const e of es){if(e.isIntersecting){e.target.classList.add('is-visible');io.unobserve(e.target)}}},{rootMargin:'0px 0px -4% 0px',threshold:.03});
  nodes.forEach(el=>{if(!el.classList.contains('is-visible'))io.observe(el)});
}
})();