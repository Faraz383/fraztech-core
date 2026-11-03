(()=>{
 const toc=document.querySelector('.editorial-layout .article-toc');
 if(!toc)return;
 const links=[...toc.querySelectorAll('a[href^="#"]')];
 const entries=links.map(link=>{
  let id='';
  try{id=decodeURIComponent(link.getAttribute('href').slice(1))}catch(e){id=link.getAttribute('href').slice(1)}
  const section=id?document.getElementById(id):null;
  return section?{link,section}:null;
 }).filter(Boolean);
 if(!entries.length)return;

 toc.classList.add('ft-scrollspy-ready');
 let ticking=false,last=null;

 function activate(item){
  if(!item||last===item)return;
  last=item;
  entries.forEach(x=>{
   const active=x===item;
   x.link.classList.toggle('is-active',active);
   if(active)x.link.setAttribute('aria-current','location');else x.link.removeAttribute('aria-current');
  });
  // Keep the current TOC point visible inside a long/sticky sidebar without moving the page.
  const r=item.link.getBoundingClientRect(),tr=toc.getBoundingClientRect();
  if(r.top<tr.top+38||r.bottom>tr.bottom-18){
   try{item.link.scrollIntoView({block:'nearest',behavior:'smooth'})}catch(e){}
  }
 }

 function update(){
  ticking=false;
  const offset=Math.min(220,Math.max(110,window.innerHeight*.22));
  let active=entries[0];
  for(const item of entries){
   if(item.section.getBoundingClientRect().top<=offset)active=item;
   else break;
  }
  // At the very end, force the last item active.
  if(window.innerHeight+window.scrollY>=document.documentElement.scrollHeight-6)active=entries[entries.length-1];
  activate(active);

  const article=document.querySelector('.editorial-layout .reading');
  if(article){
   const ar=article.getBoundingClientRect(),total=Math.max(1,article.offsetHeight-window.innerHeight*.45);
   const passed=Math.min(total,Math.max(0,-ar.top+offset));
   toc.style.setProperty('--ft-toc-progress',(passed/total*100).toFixed(2)+'%');
  }
 }
 function requestUpdate(){if(ticking)return;ticking=true;requestAnimationFrame(update)}
 window.addEventListener('scroll',requestUpdate,{passive:true});
 window.addEventListener('resize',requestUpdate,{passive:true});
 links.forEach(link=>link.addEventListener('click',()=>setTimeout(update,80)));
 update();
})();