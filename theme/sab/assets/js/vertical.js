(function(){
'use strict';
if (!window.SAB) return;

const ITEMS = window.SAB.items;
  window.M_IMGS = window.SAB.media.IMGS;
  window.M_VIDS = window.SAB.media.VIDS;
  window.M_CUT = window.SAB.media.CUT;
  window.M_VMASK = window.SAB.media.VMASK;

const TR = { [window.SAB.lang]: window.SAB.labels };
let lang = window.SAB.lang;
const vt = document.getElementById('vt');
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* build one row per milestone — identical data and order to the cinematic view */
function buildRows(){
  const frag = document.createDocumentFragment();
  ITEMS.forEach((it, i) => {
    const row = document.createElement('article');
    row.className = 'vrow' + (it.finale ? ' finale' : '');
    row.dataset.i = i;

    const media = document.createElement('div');
    media.className = 'vmedia';
    const cut = (window.M_CUT && window.M_CUT[i]) || (window.M_VMASK && window.M_VMASK[i]);
    if (!cut) media.classList.add('shadow');
    const src = window.M_IMGS[i];
    const vid = (window.M_VIDS && window.M_VIDS[i] && !reduce) ? window.M_VIDS[i] : null;
    const mask = vid && window.M_VMASK && window.M_VMASK[i];
    const maskCss = mask ? `-webkit-mask-image:url('${mask}');mask-image:url('${mask}');-webkit-mask-size:100% 100%;mask-size:100% 100%;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;` : '';
    media.innerHTML = src ? '<figure>' + (vid
      ? `<video src="${vid}" poster="${src}" style="${maskCss}" muted loop playsinline preload="none" disablepictureinpicture></video>`
      : `<img src="${src}" alt="" loading="lazy"/>`) + '</figure>' : '';

    const node = document.createElement('div');
    node.className = 'vnode';
    node.innerHTML = '<span class="vdot"></span>';

    const text = document.createElement('div');
    text.className = 'vtext';
    text.innerHTML = '<div class="vyear"></div><h3 class="vtitle"></h3><p class="vdesc"></p>';

    row.append(media, node, text);
    frag.appendChild(row);
  });
  vt.appendChild(frag);
}

function applyLang(l){
  lang = l;
  document.documentElement.lang = l;
  document.documentElement.dir = (l === 'ar') ? 'rtl' : 'ltr';
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const v = TR[l][el.dataset.i18n];
    if (v !== undefined) el.innerHTML = v;
  });
  document.querySelectorAll('.vrow').forEach(row => {
    const it = ITEMS[+row.dataset.i], t = it[l];
    row.querySelector('.vyear').textContent  = it.year;
    row.querySelector('.vtitle').innerHTML = t.title;
    row.querySelector('.vdesc').innerHTML  = t.desc;
  });

  document.querySelectorAll('a[data-href-en]').forEach(a => {
    const u = l === 'ar' ? a.dataset.hrefAr : a.dataset.hrefEn;
    if (u) a.href = u;
  });
}

vt.querySelectorAll(':scope > article').forEach(el => el.remove());
buildRows();
applyLang(window.SAB.lang);

/* reveal + play as each row enters the viewport (with a scroll fail-safe:
   IntersectionObserver can stall in embedded preview panes) */
const rows = [...document.querySelectorAll('.vrow')];
function activate(row, on){
  row.classList.toggle('in', on);
  const v = row.querySelector('video');
  if (!v) return;
  if (on){ v.muted = true; const p = v.play(); if (p) p.catch(() => {}); }
  else if (!v.paused) v.pause();
}
const spineEl = document.querySelector('.vt-spine');
const finaleEl = document.querySelector('.vrow.finale');
/* the spine belongs to the dated milestones — stop it above the closing
   key visual instead of drawing a line straight through the artwork */
function sizeSpine(){
  if (!finaleEl) return;
  const vtTop = vt.getBoundingClientRect().top;
  const fTop = finaleEl.getBoundingClientRect().top;
  spineEl.style.bottom = 'auto';
  spineEl.style.height = Math.max(0, Math.round(fTop - vtTop - 28)) + 'px';
}
function sweep(){
  const vh = window.innerHeight;
  sizeSpine();
  rows.forEach(row => {
    const r = row.getBoundingClientRect();
    activate(row, r.top < vh * 0.85 && r.bottom > vh * 0.12);
  });
  const b = vt.getBoundingClientRect();
  const span = spineEl.offsetHeight;
  const total = Math.max(span - vh * 0.5, 1);
  const done = Math.min(Math.max(-b.top + vh * 0.5, 0), total);
  document.getElementById('vtFill').style.height =
    ((done / total) * 100).toFixed(2) + '%';
  document.getElementById('nav').classList.toggle('scrolled', window.scrollY > 30);
}
if ('IntersectionObserver' in window){
  const io = new IntersectionObserver(es => es.forEach(e => activate(e.target, e.isIntersecting)),
                                      {rootMargin:'-12% 0px -15% 0px'});
  rows.forEach(r => io.observe(r));
}
window.addEventListener('scroll', sweep, {passive:true});
window.addEventListener('resize', sweep);
setTimeout(sweep, 60);

// Polylang owns language navigation; no client-side language switching.

/* ── leadership overlay ── */
(function(){
  const ov = document.getElementById('leadOverlay');
  const openIt = e => {
    if (e) e.preventDefault();
    ov.hidden = false; ov.scrollTop = 0;
    setTimeout(() => {
      ov.classList.add('in');
      /* arm the section's own entrance choreography (setTimeout, not rAF —
         rAF stalls while the tab/pane is hidden) */
      ov.querySelectorAll('.leadership-header, .leader-block').forEach(e => e.classList.add('in-view'));
    }, 30);
    document.documentElement.style.overflow = 'hidden';
  };
  const closeIt = () => {
    ov.classList.remove('in');
    document.documentElement.style.overflow = '';
    setTimeout(() => {
      ov.hidden = true;
      ov.querySelectorAll('.leadership-header, .leader-block').forEach(e => e.classList.remove('in-view'));
    }, 340);
  };
  document.querySelectorAll('.lead-open').forEach(a => a.addEventListener('click', openIt));
  document.getElementById('leadClose').addEventListener('click', closeIt);
  window.addEventListener('keydown', e => {
    if (!ov.hidden && e.key === 'Escape'){ closeIt(); e.preventDefault(); }
  });
})();

window.addEventListener('load', () => {
  setTimeout(() => document.getElementById('curtain').classList.add('gone'), 200);
  setTimeout(sweep, 260);
});
setTimeout(() => document.getElementById('curtain').classList.add('gone'), 1800);

})();
