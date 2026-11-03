(function(){
 'use strict';
 function pwin(){try{return window.parent&&window.parent!==window&&window.parent.elementor?window.parent:null;}catch(e){return null;}}
 function settingsItems(settings){
  if(!settings||typeof settings.get!=='function')return[];
  var v=settings.get('boxes');if(!v)return[];
  if(Array.isArray(v))return v;
  if(Array.isArray(v.models))return v.models.map(function(m){return m&&typeof m.toJSON==='function'?m.toJSON():(m&&m.attributes?m.attributes:m);});
  if(typeof v.toJSON==='function'){var j=v.toJSON();return Array.isArray(j)?j:[];}
  return[];
 }
 function findIndex(items,row){for(var i=0;i<items.length;i++)if(String((items[i]||{})._id||'')===String(row))return i;return-1;}
 function visible(root,selector){var all=root.querySelectorAll(selector);for(var i=0;i<all.length;i++){var cs=root.defaultView?root.defaultView.getComputedStyle(all[i]):null;if(!cs||(cs.display!=='none'&&cs.visibility!=='hidden'))return all[i];}return all[0]||null;}
 function openRow(p,index){
  var control=visible(p.document,'.elementor-control-boxes');if(!control)return false;
  var rows=control.querySelectorAll('.elementor-repeater-fields-wrapper > .elementor-repeater-fields');
  if(!rows.length)rows=control.querySelectorAll('.elementor-repeater-fields');
  var row=rows[index];if(!row)return false;
  var body=row.querySelector('.elementor-repeater-row-controls, .elementor-repeater-fields-wrapper-inner');
  var shown=false;if(body&&p.getComputedStyle){var cs=p.getComputedStyle(body);shown=cs.display!=='none'&&cs.visibility!=='hidden'&&body.getBoundingClientRect().height>0;}
  if(!row.classList.contains('editable')&&!shown){var opener=row.querySelector('.elementor-repeater-row-item-title, .elementor-repeater-row-tools, .elementor-repeater-row-tool');if(opener&&opener.click)opener.click();}
  p.document.querySelectorAll('.ft-v49-panel-selected').forEach(function(x){x.classList.remove('ft-v49-panel-selected');});
  row.classList.add('ft-v49-panel-selected');
  try{row.scrollIntoView({behavior:'smooth',block:'center'});}catch(e){row.scrollIntoView();}
  return true;
 }
 function focus(target){
  var p=pwin();if(!p||!target)return;
  var row=target.getAttribute('data-ft-v48-row');if(!row)return;
  var shell=target.closest('.elementor-element[data-id]');if(!shell)return;
  var c=null;try{c=p.elementor.getContainer(shell.getAttribute('data-id'));}catch(e){}
  if(!c||!c.model)return;
  var list=settingsItems(c.model.get('settings')),idx=findIndex(list,row);if(idx<0)return;
  var done=function(){var tries=0,t=p.setInterval(function(){tries++;if(openRow(p,idx)||tries>25)p.clearInterval(t);},60);};
  try{
   if(p.$e&&p.$e.run){var r=p.$e.run('panel/editor/open',{model:c.model,view:c.view});if(r&&r.then)r.then(done).catch(done);else done();}
   else{if(c.view&&c.view.$el&&c.view.$el[0])c.view.$el[0].click();done();}
  }catch(e){done();}
 }
 function allBoxTargets(){return Array.prototype.slice.call(document.querySelectorAll('.ft-v48-box-target[data-ft-v48-row]'));}
 function depth(el){var d=0;while(el){d++;el=el.parentElement;}return d;}
 function rectDistance(r,x,y){
  var dx=x<r.left?r.left-x:(x>r.right?x-r.right:0),dy=y<r.top?r.top-y:(y>r.bottom?y-r.bottom:0);
  return Math.sqrt(dx*dx+dy*dy);
 }
 function boxAtPoint(x,y,allowNear){
  var stack=document.elementsFromPoint?document.elementsFromPoint(x,y):[];
  for(var i=0;i<stack.length;i++){
   var t=stack[i].closest&&stack[i].closest('.ft-v48-box-target[data-ft-v48-row]');
   if(t)return t;
  }
  if(!allowNear)return null;
  var best=null,bestScore=1e9;
  allBoxTargets().forEach(function(el){
   var r=el.getBoundingClientRect();if(r.width<1||r.height<1)return;
   var dist=rectDistance(r,x,y);if(dist>18)return;
   var area=Math.max(1,r.width*r.height),score=dist*100000 + area/1000 - depth(el)*10;
   if(score<bestScore){bestScore=score;best=el;}
  });
  return best;
 }
 function parentBox(target){if(!target)return null;var p=target.parentElement;while(p){if(p.matches&&p.matches('.ft-v48-box-target[data-ft-v48-row]'))return p;p=p.parentElement;}return null;}
 var overlay=null,label=null,lastHover=null,lastSelected=null;
 function ensureOverlay(){
  if(overlay)return;
  overlay=document.createElement('div');overlay.id='ft-v49-box-overlay';
  overlay.style.cssText='position:fixed;pointer-events:none;z-index:2147483000;border:2px solid #a855f7;border-radius:3px;display:none;box-sizing:border-box;';
  label=document.createElement('div');label.style.cssText='position:absolute;left:-2px;top:-22px;background:#a855f7;color:#fff;font:600 11px/18px Arial,sans-serif;padding:1px 7px;border-radius:4px 4px 0 0;white-space:nowrap;';label.textContent='Container';overlay.appendChild(label);document.body.appendChild(overlay);
 }
 function showOverlay(el,selected){
  ensureOverlay();if(!el){overlay.style.display='none';lastHover=null;return;}
  var r=el.getBoundingClientRect();overlay.style.display='block';overlay.style.left=r.left+'px';overlay.style.top=r.top+'px';overlay.style.width=r.width+'px';overlay.style.height=r.height+'px';
  overlay.style.borderStyle=selected?'solid':'dashed';overlay.style.opacity=selected?'1':'0.82';
  var cls=(el.getAttribute('class')||'').split(/\s+/).filter(function(c){return c&&!/^ft-v|^elementor-repeater-item-/.test(c);});
  label.textContent=(selected?'Selected: ':'Container: ')+(cls.slice(0,2).join('.')||el.tagName.toLowerCase());
  lastHover=el;
 }
 function selectBox(target){
  if(!target)return false;
  document.querySelectorAll('.ft-v49-canvas-selected').forEach(function(x){x.classList.remove('ft-v49-canvas-selected');});
  target.classList.add('ft-v49-canvas-selected');lastSelected=target;showOverlay(target,true);focus(target);return true;
 }
 function onMove(e){
  if(!pwin())return;
  if(lastSelected&&e.target&&lastSelected.contains(e.target))return;
  var direct=e.target&&e.target.closest?e.target.closest('.ft-v48-box-target[data-ft-v48-row]'):null;
  var target=direct||boxAtPoint(e.clientX,e.clientY,true);showOverlay(target,false);
 }
 function onLeave(){if(!lastSelected)showOverlay(null,false);}
 function onClick(e){
  if(!pwin())return;
  var specific=e.target&&e.target.closest?e.target.closest('.ft-v46-style-target[data-ft-v47-row]'):null;
  // Normal click on editable text/link/image stays with V4.7. Alt/Option-click promotes to its parent card/container.
  if(specific&&!e.altKey)return;
  var target=null;
  if(e.altKey&&specific){target=specific.closest('.ft-v48-box-target[data-ft-v48-row]');if(target===specific)target=parentBox(target);}
  if(!target)target=e.target&&e.target.closest?e.target.closest('.ft-v48-box-target[data-ft-v48-row]'):null;
  if(!target)target=boxAtPoint(e.clientX,e.clientY,true);
  if(!target)return;
  e.preventDefault();e.stopPropagation();if(e.stopImmediatePropagation)e.stopImmediatePropagation();selectBox(target);
 }
 function install(){
  if(!pwin())return;ensureOverlay();
  document.addEventListener('mousemove',onMove,true);document.addEventListener('mouseleave',onLeave,true);document.addEventListener('click',onClick,true);
  var s=document.createElement('style');s.id='ft-v49-box-click-style';s.textContent='.ft-v48-box-target{cursor:pointer}.ft-v49-canvas-selected{box-shadow:0 0 0 2px #a855f7 inset!important;}';document.head.appendChild(s);
  try{var p=pwin(),ps=p.document.createElement('style');ps.id='ft-v49-box-panel-style';ps.textContent='.ft-v49-panel-selected{box-shadow:0 0 0 2px #a855f7 inset;border-radius:4px;}';if(!p.document.getElementById(ps.id))p.document.head.appendChild(ps);}catch(e){}
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',install);else install();
})();
