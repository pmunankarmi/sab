(function(){
'use strict';
if (!window.SAB) return;


const ITEMS = window.SAB.items;
if (!ITEMS.length) { document.body.classList.add('sab-no-milestones'); document.querySelectorAll('.lead-nav-link').forEach(a=>a.setAttribute('href','#leadershipSection')); return; }

// ───────── BUILD DOM ─────────
const wrap    = document.getElementById('itemsWrap');
const dotRail = document.getElementById('dotRail');


// ───────── LANGUAGE STATE ─────────
let currentLang = window.SAB.lang;

const TRANSLATIONS = { [window.SAB.lang]: window.SAB.labels };

function applyLang(lang){
  currentLang = lang;
  document.documentElement.lang = lang;
  document.documentElement.dir  = lang === 'ar' ? 'rtl' : 'ltr';

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.dataset.i18n;
    if (TRANSLATIONS[lang][key] !== undefined) {
      el.innerHTML = TRANSLATIONS[lang][key];
    }
  });



  // Rebuild all milestones with new language content
  if (typeof itemEls !== 'undefined' && itemEls.length){
    itemEls.forEach((el, i) => {
      const layout = ITEMS[i].layout || LAYOUTS[i % LAYOUTS.length];
      el.innerHTML = buildItemHTML(ITEMS[i], i, layout);
    });
  }

  // Update the active milestone year (it's a year number, no translation needed,
  // but flash effect should still fire)
  lastActive = -1;
  if (window.kineticRefresh) kineticRefresh();
  document.querySelectorAll('a[data-href-en]').forEach(a => {
    const u = lang === 'ar' ? a.dataset.hrefAr : a.dataset.hrefEn;
    if (u) a.href = u;
  });
}

// 5 cycling layouts: left, pair-year-left, center, pair-year-right, right
const LAYOUTS = [
  'layout-stack-left',
  'layout-pair-left',
  'layout-stack-center',
  'layout-pair-right',
  'layout-stack-right',
];

// where each layout settles, in viewport % (every item starts at 50)
const LAYOUT_TARGET_LEFT = {
  'layout-stack-left':    24,   // drifts LEFT
  'layout-pair-left':     50,   // stays CENTER (wide composition)
  'layout-stack-center':  50,   // stays CENTER
  'layout-pair-right':    50,   // stays CENTER (wide composition)
  'layout-stack-right':   76,   // drifts RIGHT
};

function buildItemHTML(it, i, layout) {
  const t = it[currentLang];

  const year     = `<div class="item-year-big">${it.year}</div>`;
  const textBody = `
      <h3 class="item-title">${t.title}</h3>
      <p class="item-desc">${t.desc}</p>`;

  if (window.M_IMGS && window.M_IMGS[i]){
    const useVid = window.M_VIDS && window.M_VIDS[i]
        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const vmask = useVid && window.M_VMASK && window.M_VMASK[i];
    const maskStyle = vmask
      ? ` style="-webkit-mask-image:url('${vmask}');mask-image:url('${vmask}');"`
      : '';
    const media = useVid
      ? `<video src="${window.M_VIDS[i]}" poster="${window.M_IMGS[i]}"${maskStyle} muted loop playsinline preload="none" disablepictureinpicture></video>`
      : `<img src="${window.M_IMGS[i]}" alt="" loading="lazy"/>`;
    const cut = ((window.M_CUT && window.M_CUT[i]) || vmask) ? ' cut' : '';
    const vcut = vmask ? ' vcut' : '';
    const bare = !cut ? ' bare' : '';   // every milestone photo is frameless per user
    return `<figure class="m-photo${cut}${vcut}${bare}">${media}</figure>${year}${textBody}`;
  }
  if (layout === 'layout-pair-left') {
    return `<div class="pair-row">
        ${year}
        <div class="pair-text">${textBody}</div>
      </div>`;
  }
  if (layout === 'layout-pair-right') {
    return `<div class="pair-row">
        <div class="pair-text">${textBody}</div>
        ${year}
      </div>`;
  }
  return `${year}${textBody}`;
}

wrap.replaceChildren();
ITEMS.forEach((it, i) => {
  const layout = ITEMS[i].layout || LAYOUTS[i % LAYOUTS.length];
  const el = document.createElement('div');
  el.className = `item ${layout}`;
  el.dataset.i = i;
  el.dataset.targetLeft = LAYOUT_TARGET_LEFT[layout];
  el.innerHTML = buildItemHTML(it, i, layout);
  wrap.appendChild(el);

  const d = document.createElement('i');
  dotRail.appendChild(d);
});

const itemEls = [...wrap.querySelectorAll('.item')];
const dotEls  = [...dotRail.querySelectorAll('i')];

// ───────── SCROLL DRIVER ─────────
const eraYear    = document.getElementById('eraYear');
const nowYear    = document.getElementById('nowYear');
const frameNum   = document.getElementById('frameNum');
const yearRail   = document.getElementById('yearRail');
const timeline   = document.getElementById('timeline');
const heroBg     = document.getElementById('heroBg');
const curtain    = document.getElementById('curtain');

// Progressive load — bar fills over ~900ms, then curtain fades
const loadBar = document.getElementById('loadBar');

function runLoadSequence(){
  const duration = 900;
  const t0 = performance.now();
  function step(now){
    const t = Math.min(1, (now - t0) / duration);
    const eased = 1 - Math.pow(1 - t, 2);
    if (loadBar) loadBar.style.width = (eased * 100).toFixed(1) + '%';
    if (t < 1) requestAnimationFrame(step);
    else setTimeout(() => { curtain.classList.add('gone'); startHeroEntrance(); }, 180);
  }
  requestAnimationFrame(step);
}

if (document.readyState === 'complete') runLoadSequence();
else window.addEventListener('load', runLoadSequence);

const clamp = (v, lo=0, hi=1) => Math.max(lo, Math.min(hi, v));
const lerp  = (a, b, t) => a + (b - a) * t;

const N = ITEMS.length;
let lastActive = -1;

function update(){
  const r = timeline.getBoundingClientRect();
  const runway = timeline.offsetHeight - window.innerHeight;
  const g = clamp(-r.top / runway, 0, 1);

  const pos = g * (N - 0.5);   // at the very end the last milestone rests fully visible
  const activeIndex = Math.min(N - 1, Math.floor(pos));

  const VIS_HALF = 0.7;
  window.__stageEl = window.__stageEl || document.getElementById('stage');
  if (window.__lastPos === undefined) window.__lastPos = pos;
  window.__velSm = (window.__velSm || 0) * 0.84 + (pos - window.__lastPos) * 0.16;
  window.__lastPos = pos;
  if (window.__stageEl) window.__stageEl.style.setProperty('--vel', Math.max(-1, Math.min(1, window.__velSm * 2.4)).toFixed(3));
  // the control rail + dark toggle appear once 1926 has actually arrived
  document.body.classList.toggle('ms-live', pos >= 0.25);
  // upcoming milestone surfaces deep in the background this early.
  // On phones the side lanes collapse to one column, so a long look-ahead
  // would sit on top of the live milestone's text — keep it symmetric there.
  const LOOKAHEAD = window.innerWidth <= 900 ? VIS_HALF : 1.75;
  itemEls.forEach((el, i) => {
    el.classList.toggle('live', i === activeIndex);
    const dist = pos - (i + 0.5);
    const p = dist < 0
      ? clamp(0.5 * (dist + LOOKAHEAD) / LOOKAHEAD, 0, 0.5)
      : clamp(0.5 + 0.5 * dist / VIS_HALF, 0.5, 1);
    if (el.classList.contains('has-photo')){
      if (p > 0 && p < 1){
        el.style.setProperty('--pp', (p - 0.5).toFixed(3));
        el.style.setProperty('--pa', (1 - Math.abs(p - 0.5) * 2).toFixed(3));
      }
      const v = el.querySelector('video');
      if (v){
        const on = i === activeIndex;
        if (on && v.paused){ v.muted = true; v.play().catch(() => {}); }
        else if (!on && !v.paused) v.pause();
      }
    }

    if (p === 0 || p === 1){
      if (el.style.opacity !== '0'){
        el.style.opacity = '0';
        el.style.transform = 'translate(-50%,-50%) scale(0)';
      }
      return;
    }

    let scale;
    if (p < 0.5) {
      // approach — emerge tiny in the distance, grow steadily toward the viewer
      const t = p / 0.5;
      const eased = Math.pow(t, 1.55);
      scale = lerp(0.08, 1.0, eased);
    } else {
      // depart — accelerates past the viewer
      const t = (p - 0.5) / 0.5;
      const eased = Math.pow(t, 1.7);
      scale = lerp(1.0, 3.8, eased);
    }

    let opacity;
    {
      const ad = Math.abs(dist);
      opacity = ad <= 0.44 ? 1 : 1 - (ad - 0.44) / 0.08;   // clean hand-off, no overlap
    }
    opacity = clamp(opacity, 0, 1);

    const yDrift = (p - 0.5) * 30;

    // Horizontal drift: approaches from the layout side, then ALL settle CENTERED
    // through the readable peak and fade-out.
    const lane = (i % 2 === 0) ? 10 : 90;
    let posLeft;
    if (p < 0.5) {
      const tDrift = p / 0.5;
      const easedDrift = Math.pow(tDrift, 2.2);
      posLeft = lane + (50 - lane) * easedDrift;
    } else {
      posLeft = 50;
    }

    el.style.left = posLeft.toFixed(2) + '%';
    el.style.opacity = opacity.toFixed(3);
    el.style.transform =
      `translate(-50%, calc(-50% + ${yDrift.toFixed(1)}px)) ` +
      `scale(${scale.toFixed(3)})`;
    el.style.zIndex = Math.round(p * 100);
  });

  const cur = ITEMS[activeIndex];
  if (cur && activeIndex !== lastActive){
    nowYear.textContent  = cur.year;
    eraYear.textContent  = cur.year;
    frameNum.textContent = String(activeIndex + 1).padStart(2, '0');
    nowYear.classList.add('flash');
    setTimeout(() => nowYear.classList.remove('flash'), 350);
    lastActive = activeIndex;
  }

  const localFrac = pos - activeIndex;
  const yearY = (localFrac - 0.5) * 80;
  const yearScale = 1 + Math.sin(localFrac * Math.PI) * 0.04;
  eraYear.style.transform =
    `translate(-50%, calc(-50% + ${yearY}px)) scale(${yearScale.toFixed(3)})`;

  yearRail.style.setProperty('--p', (g * 100).toFixed(2) + '%');

  dotEls.forEach((d, i) => {
    d.classList.toggle('active', i === activeIndex);
    d.classList.toggle('passed', i < activeIndex);
  });

  if (window.scrubSync) scrubSync(g, activeIndex);

  requestAnimationFrame(update);
}
requestAnimationFrame(update);

window.addEventListener('scroll', () => {
  const y = window.scrollY;
  if (y < window.innerHeight * 1.2){
    heroBg.style.transform =
      `translate(-50%, calc(-50% + ${y * 0.3}px)) scale(${1 + y * 0.0003})`;
    heroBg.style.opacity = (1 - (y / window.innerHeight) * 0.5).toFixed(3);
  }
}, { passive:true });


// ───────── LANG TOGGLE EVENTS ─────────
// Polylang performs full navigation to the linked translation.

// Initialise on load
applyLang(window.SAB.lang);



// ───────── SCROLL-DRIVEN REVEAL FOR INTRO + LEADERSHIP ─────────
(function(){
  const revealEls = document.querySelectorAll('.intro-statement, .span-line, .leadership-header, .leader-block');
  if (!('IntersectionObserver' in window) || !revealEls.length) {
    revealEls.forEach(el => el.classList.add('in-view'));
    return;
  }
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting){
        entry.target.classList.add('in-view');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -12% 0px' });
  revealEls.forEach(el => io.observe(el));
})();



/* ───── enhancement pack: hero entrance + kinetic text ───── */
window.__heroEntered = false;

function startHeroEntrance(){
  document.querySelector('.hero').classList.add('enter');
  const el = document.getElementById('heroBg');
  const dur = 1600, t0 = performance.now();
  (function tick(now){
    const t = Math.min(1, (now - t0) / dur);
    const e = 1 - Math.pow(1 - t, 3);
    el.textContent = Math.round(e * (Number(document.getElementById("heroBg").dataset.total) || 0));
    if (t < 1) requestAnimationFrame(tick);
    else { el.textContent = el.dataset.total; window.__heroEntered = true; }
  })(t0);
}

function kineticRefresh(){
  // hero: wrap each <br>-separated headline line in an overflow mask
  const h1 = document.querySelector('.hero h1');
  if (h1 && !h1.querySelector('.hl')){
    h1.innerHTML = h1.innerHTML.split(/<br\s*\/?>/i)
      .map(l => '<span class="hl"><span class="hl-in">' + l + '</span></span>').join('');
  }

  // option 1: split intro headlines into masked words (idempotent, i18n-safe)
  document.querySelectorAll('.intro-statement h2').forEach(h2 => {
    if (h2.querySelector('.w')) return;
    let wi = 0;
    (function wrapWords(node){
      [...node.childNodes].forEach(ch => {
        if (ch.nodeType === 3){
          const frag = document.createDocumentFragment();
          ch.textContent.split(/(\s+)/).forEach(tok => {
            if (!tok) return;
            if (/^\s+$/.test(tok)){ frag.appendChild(document.createTextNode(tok)); return; }
            const w = document.createElement('span'); w.className = 'w';
            const inn = document.createElement('span'); inn.className = 'w-in';
            inn.textContent = tok;
            inn.style.transitionDelay = (wi++ * 0.05).toFixed(2) + 's';
            w.appendChild(inn); frag.appendChild(w);
          });
          node.replaceChild(frag, ch);
        } else if (ch.nodeType === 1) wrapWords(ch);
      });
    })(h2);
  });

}
kineticRefresh();

/* the logo rides large at the top and shrinks as soon as the page scrolls */
(function(){
  const nav = document.querySelector('.sab-nav');
  if (!nav) return;
  const setNav = () => nav.classList.toggle('scrolled', window.scrollY > 30);
  window.addEventListener('scroll', setNav, {passive: true});
  setNav();
})();

/* ───── option 1: interactive timeline scrubber ───── */
(function(){
  const track = document.getElementById('scrubTrack');
  if (!track) return;
  const N = ITEMS.length;
  const tlEl = document.getElementById('timeline');

  const fill = document.createElement('div'); fill.className = 'scrub-fill'; track.appendChild(fill);
  const ticks = ITEMS.map((it, i) => {
    const t = document.createElement('span');
    t.className = 'scrub-tick'; t.dataset.y = it.year;
    t.style.setProperty('--p', (((i + 0.5) / (N - 0.5)) * 100).toFixed(3) + '%');
    track.appendChild(t);
    return t;
  });
  const isVertical = window.matchMedia('(min-width: 901px)');
  const handle = document.createElement('div'); handle.className = 'scrub-handle'; track.appendChild(handle);
  const yearEl = document.createElement('div'); yearEl.className = 'scrub-year'; yearEl.textContent = ITEMS[0].year; track.appendChild(yearEl);

  const runway = () => tlEl.offsetHeight - window.innerHeight;
  const tlTop  = () => tlEl.getBoundingClientRect().top + window.scrollY;
  const yFor   = i  => tlTop() + runway() * ((i + 0.5) / (N - 0.5));
  const curG   = () => Math.max(0, Math.min(1, -tlEl.getBoundingClientRect().top / runway()));
  const curIdx = () => Math.max(0, Math.min(N - 1, Math.floor(curG() * (N - 0.5))));

  // eased programmatic flight (native smooth scroll is too slow over a 30k-px runway)
  let anim = null;
  function flyTo(y, ms){
    if (anim) cancelAnimationFrame(anim);
    const y0 = window.scrollY, dy = y - y0, t0 = performance.now();
    ms = ms || Math.max(500, Math.min(1600, Math.abs(dy) * 0.05));
    const ease = t => t < .5 ? 4*t*t*t : 1 - Math.pow(-2*t + 2, 3) / 2;
    (function step(now){
      const t = Math.min(1, (now - t0) / ms);
      window.scrollTo({top: y0 + dy * ease(t), behavior: 'instant'});
      anim = t < 1 ? requestAnimationFrame(step) : null;
    })(t0);
  }
  window.addEventListener('wheel', () => { if (anim){ cancelAnimationFrame(anim); anim = null; } }, {passive: true});

  const jumpBy = d => flyTo(yFor(Math.max(0, Math.min(N - 1, curIdx() + d))), 800);
  document.getElementById('scrubPrev').addEventListener('click', () => jumpBy(-1));
  document.getElementById('scrubNext').addEventListener('click', () => jumpBy(1));
  document.getElementById('scrubEnd').addEventListener('click', () => flyTo(yFor(N - 1)));

  // drag to scrub, click to jump to the nearest milestone (works in both orientations)
  let dragging = false, moved = 0, downX = 0, downY = 0;
  const fracFrom = e => {
    const r = track.getBoundingClientRect();
    let f;
    if (isVertical.matches){
      f = (e.clientY - r.top) / r.height;                       // top = 1926 in both LTR & RTL
    } else {
      f = (e.clientX - r.left) / r.width;
      if (document.documentElement.dir === 'rtl') f = 1 - f;
    }
    return Math.max(0, Math.min(1, f));
  };
  track.addEventListener('pointerdown', e => {
    dragging = true; moved = 0; downX = e.clientX; downY = e.clientY;
    track.setPointerCapture(e.pointerId);
    if (anim){ cancelAnimationFrame(anim); anim = null; }
    e.preventDefault();
  });
  track.addEventListener('pointermove', e => {
    if (!dragging) return;
    moved = Math.max(moved, Math.abs(e.clientX - downX), Math.abs(e.clientY - downY));
    if (moved > 3) window.scrollTo({top: tlTop() + runway() * fracFrom(e), behavior: 'instant'});
  });
  track.addEventListener('pointerup', e => {
    dragging = false;
    if (moved <= 3){
      const i = Math.max(0, Math.min(N - 1, Math.floor(fracFrom(e) * (N - 0.5))));
      flyTo(yFor(i), 700);
    }
  });

  // ← / → step through milestones while the timeline is pinned
  window.addEventListener('keydown', e => {
    if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
    const tfo = document.getElementById('tfOverlay');
    if (tfo && !tfo.hidden) return;   // the timeframe overlay owns the arrows while open
    const r = tlEl.getBoundingClientRect();
    if (r.top > 1 || r.bottom < window.innerHeight - 1) return;
    const fwd = (e.key === 'ArrowRight') !== (document.documentElement.dir === 'rtl');
    jumpBy(fwd ? 1 : -1);
    e.preventDefault();
  });

  // ── autoplay: the century walks itself (event / lobby mode) ──
  const IC_PLAY = '<svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M3.1 1.5l7.2 4.5-7.2 4.5z"/></svg>';
  const IC_PAUSE = '<svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><rect x="2.3" y="1.7" width="2.7" height="8.6"/><rect x="7" y="1.7" width="2.7" height="8.6"/></svg>';
  const playBtn = document.createElement('button');
  playBtn.type = 'button';
  playBtn.className = 'scrub-btn scrub-play';
  playBtn.innerHTML = IC_PLAY;
  const scrubEl = document.getElementById('scrub');
  scrubEl.insertBefore(playBtn, scrubEl.firstChild);
  const syncPlayLabel = () => {
    const label = (TRANSLATIONS[currentLang] && TRANSLATIONS[currentLang][playBtn.getAttribute('aria-pressed') === 'true' ? 'pause_century' : 'play_century']) || '';
    playBtn.setAttribute('aria-label', label);
    playBtn.setAttribute('title', label);
  };
  syncPlayLabel();

  let playing = false, dwellT = null;
  const DWELL = 7000, FLIGHT = 1800;
  function autoStep(){
    const next = (curIdx() + 1) % N;    // wraps at the end — the century rewinds and replays
    flyTo(yFor(next), FLIGHT);
    dwellT = setTimeout(autoStep, FLIGHT + DWELL);
  }
  function startAuto(){
    if (playing) return;
    playing = true;
    playBtn.setAttribute('aria-pressed','true'); syncPlayLabel();
    playBtn.innerHTML = IC_PAUSE;
    const r = tlEl.getBoundingClientRect();
    const pinned = r.top <= 1 && r.bottom >= window.innerHeight - 1;
    if (!pinned){
      flyTo(yFor(0), 2000);             // enter the timeline on milestone one
      dwellT = setTimeout(autoStep, 2000 + DWELL);
    } else {
      dwellT = setTimeout(autoStep, 900);
    }
  }
  function stopAuto(){
    if (!playing) return;
    playing = false;
    playBtn.setAttribute('aria-pressed','false'); syncPlayLabel();
    clearTimeout(dwellT);
    playBtn.innerHTML = IC_PLAY;
  }
  window.__stopCenturyAuto = stopAuto;
  playBtn.addEventListener('click', () => { playing ? stopAuto() : startAuto(); });
  ['wheel', 'touchstart'].forEach(ev => window.addEventListener(ev, () => stopAuto(), {passive: true}));
  track.addEventListener('pointerdown', () => stopAuto());
  [document.getElementById('scrubPrev'), document.getElementById('scrubNext'), document.getElementById('scrubEnd')]
    .forEach(b => b.addEventListener('click', () => stopAuto()));
  window.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') stopAuto();
    if (e.code === 'Space'){
      const tfo = document.getElementById('tfOverlay');
      if (tfo && !tfo.hidden) return;
      if (/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) return;
      e.preventDefault();
      playing ? stopAuto() : startAuto();
    }
  });
  document.querySelectorAll('.lang-btn').forEach(b => b.addEventListener('click', syncPlayLabel));

  // called from the main rAF loop with live progress
  let lastA = -1, lastPct = -1;
  window.scrubSync = function(g, a){
    document.body.classList.toggle('tl-pinned', g > 0 && g < 1);
    const pct = Math.round(g * 1000) / 10;
    if (pct !== lastPct){
      track.style.setProperty('--g', pct + '%');   // fill, handle & year label all follow via CSS
      lastPct = pct;
    }
    if (a !== lastA){
      ticks.forEach((t, i) => {
        t.classList.toggle('active', i === a);
        t.classList.toggle('passed', i < a);
      });
      yearEl.textContent = ITEMS[a].year;
      lastA = a;
    }
  };
})();



/* ───── option 3: leadership as its own page ───── */
(function(){
  const sec = document.getElementById('leadershipSection');
  if (!sec || !document.querySelector('.lead-nav-link')) return;
  const ov = document.createElement('div');
  ov.className = 'lead-overlay'; ov.id = 'leadOverlay'; ov.hidden = true;
  const chrome = document.createElement('div');
  chrome.className = 'lead-chrome';
  chrome.innerHTML = `<button class="lead-close" id="leadClose" type="button" aria-label="${window.SAB.labels.close}">✕</button>`;
  ov.appendChild(chrome);
  ov.appendChild(sec);                       // lifts the section out of the page flow
  document.body.appendChild(ov);

  function openLead(e){
    if (e) e.preventDefault();
    if (window.__stopCenturyAuto) window.__stopCenturyAuto();
    ov.hidden = false;
    ov.scrollTop = 0;
    setTimeout(() => {
      ov.classList.add('in');
      sec.querySelectorAll('.leadership-header, .leader-block').forEach(el => el.classList.add('in-view'));
    }, 40);
    document.documentElement.style.overflow = 'hidden';
  }
  function closeLead(){
    ov.classList.remove('in');
    document.documentElement.style.overflow = '';
    setTimeout(() => {
      ov.hidden = true;
      sec.querySelectorAll('.leadership-header, .leader-block').forEach(el => el.classList.remove('in-view'));
    }, 340);
  }
  document.querySelectorAll('.lead-nav-link').forEach(a => a.addEventListener('click', openLead));

  document.getElementById('leadClose').addEventListener('click', closeLead);
  window.addEventListener('keydown', e => {
    if (!ov.hidden && e.key === 'Escape'){ closeLead(); e.preventDefault(); }
  });
})();

/* ── dark mode, milestones view only ──────────────────────────────────────
   The button is revealed by .tl-pinned in CSS, and body.dark only takes
   effect together with .tl-pinned — so leaving the timeline (or opening
   Leadership) returns to the light palette without clearing the choice. */
(function(){
  const btn = document.getElementById('darkToggle');
  if (!btn) return;
  const label = btn.querySelector('[data-i18n]');
  /* The rail is built inside #stage, which carries perspective + preserve-3d,
     so the browser rasterises it as a 3D texture and it renders soft/pixelated.
     Lifting it to <body> as position:fixed takes it out of the 3D context and
     it paints crisply. Listeners are bound by id and survive the move. */
  const rail = document.querySelector('.scrub');
  if (rail){
    document.body.appendChild(rail);
    rail.classList.add('lifted');
    rail.appendChild(btn);
    btn.classList.add('docked');
  }
  const on = () => document.body.classList.contains('dark');
  function syncLabel(){
    /* TRANSLATIONS/currentLang are top-level let/const in the main script:
       reachable by name across script tags, but not as window properties */
    let t = {};
    try { t = TRANSLATIONS[currentLang] || {}; } catch(e){}
    label.dataset.i18n = on() ? 'light_label' : 'dark_label';
    label.textContent = (on() ? t.light_label : t.dark_label) || '';
    btn.setAttribute('aria-pressed', String(on()));
  }
  try { if (localStorage.getItem('sabDark') === '1') document.body.classList.add('dark'); } catch(e){}
  syncLabel();
  btn.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    try { localStorage.setItem('sabDark', on() ? '1' : '0'); } catch(e){}
    syncLabel();
  });
  // the toggle label is bilingual: re-render it whenever the language flips
  document.querySelectorAll('.lang-btn').forEach(b =>
    b.addEventListener('click', () => setTimeout(syncLabel, 30)));
})();


/* ───── centenary film: holds the page and plays through ─────────────────────
   Client brief: it starts as it centres on scroll, plays in full, and the page
   does not scroll while it runs. So the first time it reaches the middle of the
   viewport we snap it to centre, lock scrolling, and play; the lock lifts when
   the film ends — or at once if the viewer skips or presses Escape. It only
   ever holds the page once, so scrolling back later never re-traps anyone.   */
(function(){
  const film = document.getElementById('centenaryFilm');
  if (!film) return;
  const btn    = document.getElementById('filmSound');
  const skip   = document.getElementById('filmSkip');
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  film.muted = true;                 /* autoplay is only allowed while muted */
  let held = false, done = false;

  const block = e => { e.preventDefault(); e.stopPropagation(); };
  const KEYS = [' ','PageDown','PageUp','ArrowDown','ArrowUp','Home','End'];
  const keyBlock = e => {
    if (e.key === 'Escape'){ release(); return; }
    if (KEYS.includes(e.key)) block(e);
  };

  /* overflow:hidden alone does not stop a programmatic scroll, so the locked
     position is also re-asserted on any scroll that slips through */
  let lockY = 0;
  const pin = () => { if (held && window.scrollY !== lockY) window.scrollTo(0, lockY); };

  /* ── grow the frame to fill the screen (FLIP) ──────────────────────────────
     The frame is pinned over its stage, so going fixed never moves the page.
     We take its rect, switch it to fixed/full-viewport, invert that change with
     a transform so it still looks inline, then let the transform animate away:
     the growth runs entirely on transform, which the compositor can carry.    */
  const frame = document.getElementById('filmFrame');
  function flipTo(full){
    if (!frame) return;
    const first = frame.getBoundingClientRect();
    frame.classList.remove('flip');
    frame.style.transform = '';
    frame.classList.toggle('is-full', full);
    const last = frame.getBoundingClientRect();
    const dx = first.left - last.left, dy = first.top - last.top;
    const sx = last.width  ? first.width  / last.width  : 1;
    const sy = last.height ? first.height / last.height : 1;
    frame.style.transform = `translate(${dx}px, ${dy}px) scale(${sx}, ${sy})`;
    void frame.offsetWidth;                      /* commit the inverted state */
    frame.classList.add('flip');
    frame.style.transform = '';
    if (!full){
      const tidy = () => { frame.classList.remove('flip'); frame.removeEventListener('transitionend', tidy); };
      frame.addEventListener('transitionend', tidy);
    }
  }

  function hold(){
    if (held || done || reduce) return;
    held = true;
    lockY = window.scrollY;
    document.documentElement.classList.add('film-locked');
    window.addEventListener('wheel', block, { passive:false });
    window.addEventListener('touchmove', block, { passive:false });
    window.addEventListener('keydown', keyBlock, { passive:false });
    window.addEventListener('scroll', pin, { passive:true });
    if (skip) skip.hidden = false;
    flipTo(true);                      /* grow to full screen as it starts */
    const p = film.play();
    if (p && p.catch) p.catch(release);   /* if the browser refuses, never trap */
  }

  function release(){
    if (!held) return;
    held = false; done = true;
    document.documentElement.classList.remove('film-locked');
    window.removeEventListener('wheel', block, { passive:false });
    window.removeEventListener('touchmove', block, { passive:false });
    window.removeEventListener('keydown', keyBlock, { passive:false });
    window.removeEventListener('scroll', pin, { passive:true });
    if (skip) skip.hidden = true;
    flipTo(false);                     /* settle back into the page */
  }

  /* progress fill — timeupdate is coarse (~4/s), the CSS transition smooths it */
  const bar = document.getElementById('filmBar');
  if (bar){
    film.addEventListener('timeupdate', () => {
      if (!film.duration) return;
      bar.style.transform = 'scaleX(' + (film.currentTime / film.duration).toFixed(4) + ')';
    });
  }

  film.addEventListener('ended', release);
  film.addEventListener('error', release);
  film.addEventListener('stalled', release);
  if (skip) skip.addEventListener('click', () => { film.pause(); release(); });

  /* The film must not take over the moment it peeks into view: it waits until
     the viewer has scrolled far enough that the END of it is on screen — i.e.
     the whole film is visible — and only then opens out. The several
     thresholds just give the callback enough chances to see that happen.     */
  const stage = frame ? frame.parentElement : film;
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (done || held) return;
      const r = e.boundingClientRect;
      const endOnScreen = r.bottom <= window.innerHeight + 4 && r.top < window.innerHeight;
      /* fallback for windows too short to ever show it whole */
      const tallerThanScreen = r.height > window.innerHeight - 8;
      if (endOnScreen || (tallerThanScreen && e.intersectionRatio >= 0.92)){
        const rect = film.getBoundingClientRect();
        window.scrollTo({ top: window.scrollY + rect.top + rect.height/2 - innerHeight/2, behavior:'instant' });
        hold();                      /* lockY is read after the snap */
      }
    });
  }, { threshold: [0, 0.25, 0.5, 0.75, 0.92, 1] });
  io.observe(stage);

  /* a muted film needs a way back to its soundtrack */
  if (btn){
    const label = btn.querySelector('[data-i18n]');
    const sync = () => {
      const on = !film.muted;
      btn.setAttribute('aria-pressed', String(on));
      label.dataset.i18n = on ? 'film_sound_off' : 'film_sound_on';
      let t = {};
      try { t = TRANSLATIONS[currentLang] || {}; } catch(e){}
      label.textContent = (on ? t.film_sound_off : t.film_sound_on)
                          || '';
    };
    btn.addEventListener('click', e => {
      e.stopPropagation();
      film.muted = !film.muted; sync();
      if (!film.muted && film.paused){ const p = film.play(); if (p && p.catch) p.catch(()=>{}); }
    });
    document.querySelectorAll('.lang-btn').forEach(b =>
      b.addEventListener('click', () => setTimeout(sync, 30)));
    sync();
  }
})();

/* ───── option 3: milestone photos from the historical archive ───── */
(function(){
  window.M_IMGS = window.SAB.media.IMGS;
  window.M_VIDS = window.SAB.media.VIDS;
  window.M_CUT = window.SAB.media.CUT;
  window.M_VMASK = window.SAB.media.VMASK;
  window.M_BARE = window.SAB.media.BARE;
  itemEls.forEach((el, i) => { if (window.M_IMGS[i]) el.classList.add('has-photo'); });
  // the last milestone closes the century as a full-bleed hero
  // the finale treatment belongs to the 2026 key visual only — a truncated
  // build (e.g. the TEST cut) must not turn its last milestone into a hero
  itemEls.forEach((el,i) => el.classList.toggle('finale', !!ITEMS[i].finale));
  applyLang(currentLang);   // rebuild milestones so the photos render on first load

  // desktop pointer-tilt: the diorama leans toward the cursor
  const stageEl = document.getElementById('stage');
  if (stageEl && window.matchMedia('(hover: hover) and (pointer: fine)').matches
      && !window.matchMedia('(prefers-reduced-motion: reduce)').matches){
    stageEl.addEventListener('pointermove', e => {
      const r = stageEl.getBoundingClientRect();
      stageEl.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 2 - 1).toFixed(3));
      stageEl.style.setProperty('--my', ((e.clientY - r.top) / r.height * 2 - 1).toFixed(3));
    }, {passive: true});
    stageEl.addEventListener('pointerleave', () => {
      stageEl.style.setProperty('--mx', 0);
      stageEl.style.setProperty('--my', 0);
    });
  }
})();


})();
