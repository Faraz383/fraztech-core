(()=>{
 const cfg=window.FT_LANG_ADMIN_V544;if(!cfg)return;
 const btn=document.getElementById('ft-v544-prepare-all'),box=document.getElementById('ft-v544-progress'),bar=document.getElementById('ft-v544-progress-bar'),title=document.getElementById('ft-v544-progress-title'),txt=document.getElementById('ft-v544-progress-text');
 if(!btn||!box)return;

 const enabled=(cfg.enabled||[]).filter(c=>c!=='en');
 const toPrepare=enabled.filter(c=>!cfg.packReady?.[c]);
 const skipSelector='script,style,noscript,template,code,pre,textarea,select,option,svg,#wpadminbar,.ft-language-switcher,.notranslate,[data-no-translate],[translate="no"],.brand,.ft-brand-image,.brand-mark,.brand-word';

 function setProgress(p,t,d=''){box.style.display='block';bar.style.width=Math.max(0,Math.min(100,p))+'%';title.textContent=t;txt.textContent=d}
 function meaningful(s){if(!s)return false;s=s.trim();return s.length>1&&s.length<=5000&&/[\p{L}]/u.test(s)&&!/^(https?:\/\/|www\.|mailto:|tel:|#[0-9a-f]{3,8}$|rgba?\(|hsla?\(|var\()/i.test(s)}
 function collectDoc(doc){
  const set=new Set(),root=doc.body||doc;
  const walker=doc.createTreeWalker(root,NodeFilter.SHOW_TEXT,{acceptNode(n){const p=n.parentElement;if(!p||p.closest(skipSelector))return NodeFilter.FILTER_REJECT;return meaningful((n.nodeValue||'').trim())?NodeFilter.FILTER_ACCEPT:NodeFilter.FILTER_REJECT}});
  let n;while(n=walker.nextNode())set.add((n.nodeValue||'').trim());
  root.querySelectorAll('[placeholder],[title],[aria-label],[alt]').forEach(el=>{if(el.closest(skipSelector))return;['placeholder','title','aria-label','alt'].forEach(a=>{const v=(el.getAttribute(a)||'').trim();if(meaningful(v))set.add(v)})});
  return [...set];
 }
 async function post(action,fields,nonce){
  const f=new FormData();f.append('action',action);f.append('nonce',nonce);
  Object.entries(fields||{}).forEach(([k,v])=>{if(Array.isArray(v))v.forEach(x=>f.append(k+'[]',x));else f.append(k,String(v))});
  const r=await fetch(cfg.ajaxUrl,{method:'POST',credentials:'same-origin',body:f});const j=await r.json();
  if(!j?.success)throw new Error(j?.data?.message||'Request failed');return j.data||{};
 }
 async function lookup(texts,target){
  const out={},missing=[];
  for(let i=0;i<texts.length;i+=120){const d=await post('ft_v541_lookup',{target,texts:texts.slice(i,i+120)},cfg.lookupNonce);Object.assign(out,d.translations||{});missing.push(...(d.missing||[]))}
  return {out,missing:[...new Set(missing)]};
 }
 async function store(target,pairs){
  for(let i=0;i<pairs.length;i+=100)await post('ft_v541_store',{target,pairs:JSON.stringify(pairs.slice(i,i+100))},cfg.storeNonce)
 }

 // Common Hindi-to-Urdu vocabulary layer. It runs before script conversion so
 // website/product terminology sounds much more natural than raw transliteration.
 const urduWords=[
  ['कृत्रिम बुद्धिमत्ता','مصنوعی ذہانت'],['आर्टिफिशियल इंटेलिजेंस','مصنوعی ذہانت'],['उपयोगकर्ता अनुभव','صارف کا تجربہ'],
  ['उपयोगकर्ता','صارف'],['ग्राहक','کلائنٹ'],['वेबसाइट','ویب سائٹ'],['मोबाइल ऐप','موبائل ایپ'],['ऐप','ایپ'],
  ['डिज़ाइन सिस्टम','ڈیزائن سسٹم'],['डिज़ाइन','ڈیزائن'],['डेवलपमेंट','ڈیولپمنٹ'],['विकास','ڈیولپمنٹ'],
  ['फ्रंटएंड','فرنٹ اینڈ'],['बैकएंड','بیک اینڈ'],['डेटाबेस','ڈیٹا بیس'],['एपीआई','API'],['प्रोटोटाइप','پروٹوٹائپ'],
  ['वायरफ्रेम','وائر فریم'],['प्रोजेक्ट','پروجیکٹ'],['परियोजना','پروجیکٹ'],['सर्विसेज','سروسز'],['सेवाएँ','سروسز'],
  ['सेवा','سروس'],['समाधान','حل'],['प्रक्रिया','عمل'],['आवश्यकताएँ','ضروریات'],['आवश्यकता','ضرورت'],
  ['उपकरण','ٹولز'],['टूल्स','ٹولز'],['तकनीक','ٹیکنالوجی'],['प्रौद्योगिकी','ٹیکنالوجی'],['सामग्री','مواد'],
  ['लेख','مضمون'],['जानकारी','معلومات'],['प्रदर्शन','کارکردگی'],['गोपनीयता','رازداری'],['भुगतान','ادائیگی'],
  ['डिलीवरी','ڈیلیوری'],['परीक्षण','ٹیسٹنگ'],['लॉन्च','لانچ'],['टीम','ٹیم'],['व्यवसाय','کاروبار'],
  ['अनुभव','تجربہ'],['संपर्क','رابطہ'],['समीक्षा','جائزہ'],['परिणाम','نتیجہ'],['लक्ष्य','مقصد'],
  ['सफलता','کامیابی'],['समर्थन','سپورٹ'],['सुरक्षा','سیکیورٹی'],['गुणवत्ता','کوالٹی'],['मूल्य','قیمت'],
  ['समय','وقت'],['शुरू','شروع'],['समाप्त','مکمل'],['अगला','اگلا'],['अगली','اگلی'],['बेहतर','بہتر'],
  ['चुनें','منتخب کریں'],['देखें','دیکھیں'],['पढ़ें','پڑھیں'],['और','اور'],['के लिए','کے لیے'],['के साथ','کے ساتھ']
 ];

 const cons={
  'क':'ک','ख':'کھ','ग':'گ','घ':'گھ','ङ':'نگ','च':'چ','छ':'چھ','ज':'ج','झ':'جھ','ञ':'ن',
  'ट':'ٹ','ठ':'ٹھ','ड':'ڈ','ढ':'ڈھ','ण':'ن','त':'ت','थ':'تھ','द':'د','ध':'دھ','न':'ن',
  'प':'پ','फ':'ف','ब':'ب','भ':'بھ','म':'م','य':'ی','र':'ر','ल':'ل','व':'و','श':'ش','ष':'ش','स':'س','ह':'ہ',
  'क़':'ق','ख़':'خ','ग़':'غ','ज़':'ز','ड़':'ڑ','ढ़':'ڑھ','फ़':'ف','य़':'ی'
 };
 const indep={'अ':'ا','आ':'آ','इ':'اِ','ई':'ای','उ':'اُ','ऊ':'او','ऋ':'رِ','ए':'اے','ऐ':'ای','ओ':'او','औ':'اؤ'};
 const signs={'ा':'ا','ि':'','ी':'ی','ु':'','ू':'و','ृ':'ر','े':'ے','ै':'َے','ो':'و','ौ':'اؤ','ं':'ں','ँ':'ں','ः':'ہ','़':'','्':''};
 const digits={'०':'0','१':'1','२':'2','३':'3','४':'4','५':'5','६':'6','७':'7','८':'8','९':'9'};

 function hindiToUrdu(input){
  let s=' '+String(input||'').normalize('NFC')+' ';
  for(const [h,u] of urduWords)s=s.split(h).join(u);
  let out='';
  for(let i=0;i<s.length;i++){
   const ch=s[i];
   if(cons[ch]){out+=cons[ch];continue}
   if(indep[ch]){out+=indep[ch];continue}
   if(Object.prototype.hasOwnProperty.call(signs,ch)){out+=signs[ch];continue}
   if(digits[ch]){out+=digits[ch];continue}
   // punctuation and any Latin product/technology names remain unchanged
   out+=ch;
  }
  return out.replace(/\s+/g,' ').replace(/ہہ/g,'ہ').replace(/یی/g,'ی').trim();
 }

 async function makeTranslator(lang){
  const target=lang==='ur'?'hi':lang;
  const availability=await Translator.availability({sourceLanguage:'en',targetLanguage:target});
  if(availability==='unavailable')throw new Error((cfg.languages[lang]?.native||lang)+' is not available in this Chrome installation.');
  return await Translator.create({sourceLanguage:'en',targetLanguage:target});
 }

 btn.addEventListener('click',async()=>{
  if(!('Translator' in self)){setProgress(0,cfg.messages.browser);return}
  if(!toPrepare.length){setProgress(100,'All language packs are already READY.','No preparation is required.');return}
  btn.disabled=true;box.style.display='block';
  try{
   setProgress(2,cfg.messages.start,'Starting all required local translators from this administrator click…');

   const translatorPromises={};
   for(const lang of toPrepare)translatorPromises[lang]=makeTranslator(lang);

   const inv=await post('ft_v543_site_urls',{},cfg.sitePackNonce),urls=inv.urls||[],revision=inv.revision;
   setProgress(7,cfg.messages.scan,'0 / '+urls.length+' pages');
   const all=new Set();let done=0,next=0;
   async function worker(){
    while(true){
     const idx=next++;if(idx>=urls.length)return;
     try{const r=await fetch(urls[idx],{credentials:'same-origin',cache:'no-store'});if(r.ok){const doc=new DOMParser().parseFromString(await r.text(),'text/html');collectDoc(doc).forEach(t=>all.add(t))}}catch(e){}
     done++;setProgress(7+Math.round((done/Math.max(1,urls.length))*13),cfg.messages.scan,done+' / '+urls.length+' pages');
    }
   }
   await Promise.all(Array.from({length:Math.min(4,Math.max(1,urls.length))},worker));
   const texts=[...all];let languageIndex=0;

   for(const lang of toPrepare){
    languageIndex++;
    const base=20+((languageIndex-1)/toPrepare.length)*75,span=75/toPrepare.length;
    setProgress(base,cfg.messages.model,(cfg.languages[lang]?.native||lang)+' — checking existing cache');
    let translator;
    try{translator=await translatorPromises[lang]}catch(e){setProgress(base,cfg.messages.unsupported,(cfg.languages[lang]?.native||lang)+' skipped');continue}
    const data=await lookup(texts,lang),missing=data.missing||[],pairs=[];
    for(let i=0;i<missing.length;i++){
     const source=missing[i];let tr='';
     try{
      tr=(await translator.translate(source))?.trim()||'';
      if(lang==='ur'&&tr)tr=hindiToUrdu(tr);
     }catch(e){}
     if(tr)pairs.push({source,translation:tr});
     if(pairs.length>=80){await store(lang,pairs.splice(0,pairs.length))}
     if(i%5===0)setProgress(base+span*((i+1)/Math.max(1,missing.length)),cfg.messages.translate,(cfg.languages[lang]?.native||lang)+' — '+(i+1)+' / '+missing.length+' new phrases');
    }
    if(pairs.length)await store(lang,pairs);
    const verify=await lookup(texts,lang);
    if((verify.missing||[]).length)throw new Error((cfg.languages[lang]?.native||lang)+' pack is incomplete.');
    await post('ft_v543_mark_complete',{target:lang,revision,count:texts.length,pages:urls.length},cfg.sitePackNonce);
    setProgress(base+span,cfg.messages.save,(cfg.languages[lang]?.native||lang)+' READY');
   }

   setProgress(100,cfg.messages.done,'Refresh this page to see READY status. Frontend switching is instant for prepared languages.');
   btn.textContent='Language packs ready';
  }catch(e){
   console.error(e);setProgress(0,'Preparation stopped',e?.message||'Something went wrong.');
   btn.disabled=false;
  }
 });
})();