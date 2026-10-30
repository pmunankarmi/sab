(function(){
 'use strict';
 document.documentElement.classList.add('sab-js');
 const reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
 if(reduce){const hero=document.getElementById('heroLoop');if(hero){hero.removeAttribute('autoplay');hero.pause();}}
 const toggle=document.querySelector('.navbar-toggler'), nav=document.getElementById('mainNav');
 if(toggle && nav)toggle.addEventListener('click',()=>{const open=nav.classList.toggle('is-open');toggle.setAttribute('aria-expanded',String(open));});
 // An empty timeline or unrelated page must still release the loading screen.
 window.setTimeout(()=>document.getElementById('curtain')?.classList.add('gone'),2200);
 const overlay=document.getElementById('leadOverlay');
 if(overlay){
  overlay.setAttribute('role','dialog');overlay.setAttribute('aria-modal','true');overlay.setAttribute('aria-labelledby','leadership-heading');
  let lastFocus;
  const observer=new MutationObserver(()=>{
   if(!overlay.hidden){lastFocus=document.activeElement;overlay.querySelector('button')?.focus();}
   else if(lastFocus?.isConnected)lastFocus.focus();
  });observer.observe(overlay,{attributes:true,attributeFilter:['hidden']});
  overlay.addEventListener('keydown',e=>{
   if(e.key!=='Tab')return;
   const nodes=[...overlay.querySelectorAll('a[href],button:not([disabled]),input,select,textarea,[tabindex="0"]')].filter(el=>el.getClientRects().length);
   if(!nodes.length)return;
   if(e.shiftKey && document.activeElement===nodes[0]){e.preventDefault();nodes.at(-1).focus();}
   else if(!e.shiftKey && document.activeElement===nodes.at(-1)){e.preventDefault();nodes[0].focus();}
  });
 }
 if(location.hash==='#leadership')document.querySelector('.lead-nav-link')?.click();
})();
