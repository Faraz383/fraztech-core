(function(){
  var ICONS={
    'Figma':'https://api.iconify.design/logos/figma.svg',
    'React Native':'https://api.iconify.design/logos/react.svg',
    'Flutter':'https://api.iconify.design/logos/flutter.svg',
    'Dart':'https://api.iconify.design/logos/dart.svg',
    'Swift':'https://api.iconify.design/logos/swift.svg',
    'Kotlin':'https://api.iconify.design/logos/kotlin-icon.svg',
    'Node.js':'https://api.iconify.design/logos/nodejs-icon.svg',
    'Laravel / PHP':'https://api.iconify.design/logos/laravel.svg',
    'Laravel':'https://api.iconify.design/logos/laravel.svg',
    'PHP':'https://api.iconify.design/logos/php.svg',
    'Python':'https://api.iconify.design/logos/python.svg',
    'Firebase':'https://api.iconify.design/logos/firebase.svg',
    'Supabase':'https://api.iconify.design/logos/supabase-icon.svg',
    'PostgreSQL':'https://api.iconify.design/logos/postgresql.svg',
    'MySQL':'https://api.iconify.design/logos/mysql-icon.svg',
    'MongoDB':'https://api.iconify.design/logos/mongodb-icon.svg',
    'Stripe':'https://api.iconify.design/logos/stripe.svg',
    'Stripe billing':'https://api.iconify.design/logos/stripe.svg',
    'PayPal':'https://api.iconify.design/logos/paypal.svg',
    'Apple Pay':'https://api.iconify.design/logos/apple-pay.svg',
    'Google Pay':'https://api.iconify.design/logos/google-pay.svg',
    'REST APIs':'https://api.iconify.design/tabler/api.svg?color=%230e6f4f',
    'React':'https://api.iconify.design/logos/react.svg',
    'Next.js':'https://api.iconify.design/logos/nextjs-icon.svg',
    'TypeScript':'https://api.iconify.design/logos/typescript-icon.svg',
    'HTML / CSS / JS':'https://api.iconify.design/logos/html-5.svg',
    'GitHub':'https://api.iconify.design/logos/github-icon.svg',
    'Cloud / hosting':'https://api.iconify.design/tabler/cloud.svg?color=%230e6f4f',
    'WordPress':'https://api.iconify.design/logos/wordpress-icon.svg',
    'Elementor':'https://api.iconify.design/logos/elementor.svg',
    'WooCommerce':'https://api.iconify.design/logos/woocommerce-icon.svg',
    'Caching':'https://api.iconify.design/tabler/bolt.svg?color=%230e6f4f',
    'Security':'https://api.iconify.design/tabler/shield-lock.svg?color=%230e6f4f',
    'SEO tools':'https://api.iconify.design/tabler/seo.svg?color=%230e6f4f',
    'Shopify':'https://api.iconify.design/logos/shopify.svg',
    'Liquid':'https://api.iconify.design/tabler/droplet.svg?color=%230e6f4f',
    'Shopify Payments':'https://api.iconify.design/logos/shopify.svg',
    'Metafields':'https://api.iconify.design/tabler/code-dots.svg?color=%230e6f4f',
    'Analytics':'https://api.iconify.design/tabler/chart-bar.svg?color=%230e6f4f',
    'Email / CRM':'https://api.iconify.design/tabler/mail-cog.svg?color=%230e6f4f',
    'Shopify Apps':'https://api.iconify.design/tabler/apps.svg?color=%230e6f4f',
    'RBAC / Auth':'https://api.iconify.design/tabler/user-lock.svg?color=%230e6f4f',
    'Webhooks':'https://api.iconify.design/tabler/webhook.svg?color=%230e6f4f',
    'OpenAI':'https://api.iconify.design/logos/openai-icon.svg',
    'RAG / vectors':'https://api.iconify.design/tabler/vector.svg?color=%230e6f4f',
    'Zapier':'https://api.iconify.design/logos/zapier-icon.svg',
    'n8n':'https://api.iconify.design/logos/n8n-icon.svg',
    'Make':'https://api.iconify.design/logos/make.svg',
    'Evaluation':'https://api.iconify.design/tabler/checklist.svg?color=%230e6f4f',
    'Monitoring':'https://api.iconify.design/tabler/activity-heartbeat.svg?color=%230e6f4f',
    'Guardrails':'https://api.iconify.design/tabler/shield-check.svg?color=%230e6f4f',
    'FigJam':'https://api.iconify.design/logos/figma.svg',
    'Adobe XD':'https://api.iconify.design/logos/adobe-xd.svg',
    'Photoshop':'https://api.iconify.design/logos/adobe-photoshop.svg',
    'Illustrator':'https://api.iconify.design/logos/adobe-illustrator.svg',
    'Prototype':'https://api.iconify.design/tabler/route.svg?color=%230e6f4f',
    'Components':'https://api.iconify.design/tabler/components.svg?color=%230e6f4f',
    'Design tokens':'https://api.iconify.design/tabler/color-swatch.svg?color=%230e6f4f'
  };

  function hydrateIcons(root){
    root.querySelectorAll('.ft-hww-tech').forEach(function(item){
      var labelEl=item.querySelector(':scope > span:last-child');
      if(!labelEl)return;
      var label=(labelEl.textContent||'').trim();
      var src=ICONS[label];
      if(!src)return;
      var old=item.querySelector(':scope > img, :scope > .ft-hww-tech-fallback, :scope > .ft-hww-tech-logo');
      if(old && old.classList && old.classList.contains('ft-hww-tech-logo') && old.getAttribute('src')===src)return;
      var img=document.createElement('img');
      img.className='ft-hww-tech-logo';
      img.src=src;
      img.alt='';
      img.width=22;img.height=22;img.loading='lazy';img.decoding='async';
      img.onerror=function(){
        img.style.display='none';
        if(old)old.style.display='grid';
      };
      if(old){old.style.display='none';item.insertBefore(img,old);}else item.insertBefore(img,item.firstChild);
    });
  }

  function init(root){
    hydrateIcons(root);
    var tabs=[].slice.call(root.querySelectorAll('[data-ft-hww-tab]'));
    var panels=[].slice.call(root.querySelectorAll('[data-ft-hww-panel]'));
    if(!tabs.length||!panels.length)return;
    function select(key,updateHash){
      tabs.forEach(function(t){var on=t.getAttribute('data-ft-hww-tab')===key;t.classList.toggle('is-active',on);t.setAttribute('aria-selected',on?'true':'false');});
      panels.forEach(function(p){var on=p.getAttribute('data-ft-hww-panel')===key;p.classList.toggle('is-active',on);p.hidden=!on;if(on)hydrateIcons(p);});
      if(updateHash && history.replaceState) history.replaceState(null,'','#'+key);
    }
    tabs.forEach(function(t){t.addEventListener('click',function(e){e.preventDefault();select(t.getAttribute('data-ft-hww-tab'),true);});});
    var key=(location.hash||'').replace('#','');
    if(key && panels.some(function(p){return p.getAttribute('data-ft-hww-panel')===key;}))select(key,false);
  }
  function boot(){document.querySelectorAll('.ft-hww-selector').forEach(init);}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();
  document.addEventListener('elementor/popup/show',boot);
})();
