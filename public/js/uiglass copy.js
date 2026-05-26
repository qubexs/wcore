(function ($) {
    'use strict';

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       STATE
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    var openPanelId = null;
    var panelState  = {};
    var navigating  = false;

    var $dock     = $('#macos-dock');
    var $backdrop = $('#dock-backdrop');
    var $loader   = $('#spa-loader');

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       PANEL HELPERS
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    function positionPanel($panel, $item) {
        var ir   = $item[0].getBoundingClientRect();
        var pw   = $panel.outerWidth()  || 300;
        var ph   = $panel.outerHeight() || 340;
        var dr   = $dock[0].getBoundingClientRect();
        var left = Math.max(12, Math.min(ir.left + ir.width / 2 - pw / 2, window.innerWidth - pw - 12));
        var top  = Math.max(12, dr.top - ph - 16);
        $panel.css({ left: left, top: top });
        $panel[0].style.setProperty('--origin-x', (ir.left + ir.width / 2 - left) + 'px');
    }

    function openPanel($item, panelId) {
        var $panel = $('#' + panelId);
        if (!$panel.length) return;
        if (openPanelId === panelId) { closePanel(); return; }
        if (openPanelId) {
            animClose($('#' + openPanelId), function(){});
            $('.dock-item').removeClass('panel-open-source');
        }
        panelState[panelId] = { minimized: false, fullscreen: false };
        $panel.removeClass('panel-minimized panel-fullscreen panel-closing');
        openPanelId = panelId;
        $item.addClass('panel-open-source');
        $panel.css({ visibility: 'hidden', display: 'flex' });
        positionPanel($panel, $item);
        $panel.css('visibility', '');
        requestAnimationFrame(function () { $panel.addClass('panel-open'); });
        $backdrop.addClass('active');
    }

    function animClose($panel, cb) {
        $panel.removeClass('panel-open').addClass('panel-closing');
        $panel.one('animationend', function () {
            $panel.removeClass('panel-closing').css('display', '');
            if (cb) cb();
        });
    }

    function closePanel() {
        if (!openPanelId) return;
        animClose($('#' + openPanelId), function(){});
        $('.dock-item').removeClass('panel-open-source');
        $('.dock-item').each(function () {
            if ($(this).find('.dock-dot.visible').length) $(this).addClass('active');
        });
        $backdrop.removeClass('active');
        openPanelId = null;
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       TRAFFIC LIGHTS
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    $(document).on('click', '.tl-btn', function (e) {
        e.stopPropagation();
        var $panel  = $(this).closest('.dock-panel');
        var id      = $panel.attr('id');
        var action  = $(this).data('action');
        var state   = panelState[id] || { minimized: false, fullscreen: false };

        if (action === 'close') {
            animClose($panel, function () {
                $('.dock-item').removeClass('panel-open-source');
                $backdrop.removeClass('active');
                openPanelId = null;
                delete panelState[id];
            });
        } else if (action === 'minimize') {
            state.minimized = !state.minimized;
            $panel.toggleClass('panel-minimized', state.minimized);
        } else if (action === 'maximize') {
            state.fullscreen = !state.fullscreen;
            $panel.toggleClass('panel-fullscreen', state.fullscreen);
            if (!state.fullscreen) {
                var $src = $('.dock-item.panel-open-source');
                if ($src.length) positionPanel($panel, $src);
            }
        }
        panelState[id] = state;
    });

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       MAGNIFICATION
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    $dock.on('mousemove', function (e) {
        $('.dock-item').each(function () {
            var r    = $(this).find('.dock-icon')[0].getBoundingClientRect();
            var dist = Math.hypot(e.clientX - (r.left + r.width / 2), e.clientY - (r.top + r.height / 2));
            this.style.setProperty('--mag', dist < 110 ? 1.6 - 0.6 * (dist / 110) : 1);
        });
    }).on('mouseleave', function () {
        $('.dock-item').each(function () { this.style.setProperty('--mag', 1); });
    });

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       ANIMATIONS
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    /** Bounce — 3-hop spring, returns Promise that resolves on animationend */
    function bounce($item) {
        return new Promise(function (resolve) {
            var $icon = $item.find('.dock-icon');
            $icon.removeClass('bouncing')[0].offsetWidth;
            $icon.addClass('bouncing').one('animationend', function () {
                $icon.removeClass('bouncing');
                resolve();
            });
        });
    }

    /** Jiggle — locked-item shake */
    function jiggle($item) {
        $item.addClass('dock-jiggle').one('animationend', function () {
            $item.removeClass('dock-jiggle');
        });
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       SPA ENGINE
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    var CONTENT_EXIT_MS  = 260;   
    var LOADER_START_MS  = 80;    

    function loaderStart() {
        $loader.removeClass('spa-loader-done').addClass('spa-loader-active');
    }
    function loaderDone() {
        $loader.addClass('spa-loader-done');
        setTimeout(function () { $loader.removeClass('spa-loader-active spa-loader-done'); }, 400);
    }

    function spaNavigate(href, direction) {
        if (navigating) return;
        if (href === window.location.href) return;  
        navigating = true;
        direction  = direction || 'forward';

        var $content = $('#content');

        loaderStart();

        $content
            .removeClass('content-enter-forward content-enter-back')
            .addClass(direction === 'back' ? 'content-exit-back' : 'content-exit-forward');

        fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-SPA': '1' } })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(function (html) {
                var doc          = new DOMParser().parseFromString(html, 'text/html');
                var newContentEl = doc.getElementById('content');
                if (!newContentEl) throw new Error('no #content in response');

                setTimeout(function () {
                    $content.html(newContentEl.innerHTML);

                    var newTitle = doc.title || document.title;
                    window.history.pushState({ href: href, direction: direction }, newTitle, href);
                    document.title = newTitle;

                    $content
                        .removeClass('content-exit-forward content-exit-back')
                        .addClass(direction === 'back' ? 'content-enter-back' : 'content-enter-forward');

                    $content.one('animationend', function () {
                        $content.removeClass('content-enter-forward content-enter-back');
                        navigating = false;
                        loaderDone();
                        try {
                            $content.find('[data-toggle="tooltip"]').tooltip();
                            $content.find('[data-toggle="popover"]').popover();
                            $(document).trigger('spa:loaded', [$content]);
                        } catch(e) {}
                    });

                    updateDockActive(href);

                }, CONTENT_EXIT_MS);
            })
            .catch(function (err) {
                console.warn('[SPA] fallback to hard nav:', err);
                window.location.href = href;
                navigating = false;
                loaderDone();
            });
    }

    function updateDockActive(href) {
        $('.dock-item').each(function () {
            var dh = $(this).data('href');
            var on = dh && (href === dh || href.startsWith(dh + '?') || href.startsWith(dh + '/'));
            $(this).find('.dock-dot').toggleClass('visible', on);
            $(this).toggleClass('active', on);
        });
        $('.panel-nav-item').each(function () {
            var ih = $(this).attr('href');
            $(this).toggleClass('active', !!ih && ih !== '#' && href === ih);
            if (ih && href === ih) {
                var panelId   = $(this).closest('.dock-panel').attr('id');
                var $dockItem = $('.dock-item[data-panel="' + panelId + '"]');
                $dockItem.find('.dock-dot').addClass('visible');
                $dockItem.addClass('active');
            }
        });
    }

    window.addEventListener('popstate', function (e) {
        var dir = (e.state && e.state.direction === 'forward') ? 'back' : 'forward';
        spaNavigate(window.location.href, dir);
    });

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       CLICK HANDLERS
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    $(document).on('click', '.panel-nav-item', function (e) {
        var href    = $(this).attr('href');
        var isModal = $(this).data('toggle') === 'modal';
        if (!href || href === '#' || isModal || navigating) return;
        e.preventDefault();

        var $navItem  = $(this);
        var panelId   = $navItem.closest('.dock-panel').attr('id');
        var $dockItem = panelId ? $('.dock-item[data-panel="' + panelId + '"]') : $();

        $navItem.addClass('nav-item-launching');

        var doNav = function () { spaNavigate(href); };

        if ($dockItem.length) {
            bounce($dockItem).then(function () {
                if (openPanelId) {
                    animClose($('#' + openPanelId), doNav);
                    $('.dock-item').removeClass('panel-open-source');
                    $backdrop.removeClass('active');
                    openPanelId = null;
                } else {
                    doNav();
                }
            });
        } else {
            doNav();
        }
    });

    $(document).on('click keydown', '.dock-item', function (e) {
        if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
        e.preventDefault();
        e.stopPropagation();

        var $item   = $(this);
        var panelId = $item.data('panel');
        var href    = $item.data('href');

        if ($item.hasClass('dock-locked')) { jiggle($item); return; }
        if (navigating && !panelId) return;

        if (panelId) {
            bounce($item).then(function () { openPanel($item, panelId); });
        } else if (href && href !== '#') {
            bounce($item).then(function () { spaNavigate(href); });
        }
    });

    $backdrop.on('click', closePanel);
    $(document).on('keydown', function (e) { if (e.key === 'Escape') closePanel(); });
    $(document).on('click', '.dock-panel', function (e) { e.stopPropagation(); });

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       PANEL DRAG
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    var drag = {};
    $(document).on('mousedown', '.panel-titlebar', function (e) {
        if ($(e.target).closest('.tl-btn').length) return;
        var $p = $(this).closest('.dock-panel');
        if ($p.hasClass('panel-fullscreen')) return;
        drag = { on: true, x: e.clientX, y: e.clientY,
                 l: $p.offset().left, t: $p.offset().top, $p: $p };
        $p.css('transition', 'none');
        $('body').css('user-select', 'none');
    });

    $(document).on('mousemove', function (e) {
        if (!drag.on) return;
        drag.$p.css({ left: drag.l + e.clientX - drag.x, top: drag.t + e.clientY - drag.y });
    }).on('mouseup', function () {
        if (!drag.on) return;
        drag.on = false;
        if (drag.$p) drag.$p.css('transition', '');
        $('body').css('user-select', '');
        drag = {};
    });

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       HAPTIC & SMOOTH SCROLL (From admin.blade.php)
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    $(document).ready(function() {
        document.querySelectorAll('.btn, .nav-link, .dropdown-item, .sidebar .nav-item').forEach(el => {
            el.addEventListener('click', function() {
                if (window.navigator.vibrate) {
                    window.navigator.vibrate(10);
                }
            });
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                // Ignore hash links meant for toggling or empty hrefs
                if (this.getAttribute('href') === '#') return;
                
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   REALISTIC LIQUID GLASS BACKGROUND GENERATOR  v3
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

   Key feature: VARIABLE-WIDTH EDGES
   Instead of uniform stroke-width (which looks like a drawn line),
   every wave edge is a FILLED RIBBON PATH whose width changes
   organically along its length — thick here, almost invisible there —
   just like the edge of a real bent pane of glass or an ocean wave lip.

   How variable-width ribbons work:
   1. Sample N points along the bezier curve.
   2. At each point compute the unit normal (perpendicular to the tangent).
   3. Compute a smooth width value using a sum of sine waves with random
      frequencies & phases — this is "organic" because it has multiple
      harmonics, just like real surface variation.
   4. Offset the sampled point outward (above the wave) by that width.
   5. The upper hull (offset points) + lower hull (original points reversed)
      form a closed filled polygon — the ribbon.
   6. Three such ribbons per wave: glow, hard edge, shadow underside.
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

function generateRealisticGlassWaves() {
    const W = 1920;
    const H = 1080;
    const hueBase = Math.floor(Math.random() * 360);
    const rand = (min, max) => min + Math.random() * (max - min);

    // ── Bezier helpers ─────────────────────────────────────────────────

    /** Point on a cubic bezier at parameter t */
    function bezierPt(t, x0,y0, cx1,cy1, cx2,cy2, x1,y1) {
        const mt = 1 - t;
        return {
            x: mt*mt*mt*x0 + 3*mt*mt*t*cx1 + 3*mt*t*t*cx2 + t*t*t*x1,
            y: mt*mt*mt*y0 + 3*mt*mt*t*cy1 + 3*mt*t*t*cy2 + t*t*t*y1
        };
    }

    /** Tangent of a cubic bezier at t (not normalised) */
    function bezierTangent(t, x0,y0, cx1,cy1, cx2,cy2, x1,y1) {
        const mt = 1 - t;
        return {
            x: 3*(mt*mt*(cx1-x0) + 2*mt*t*(cx2-cx1) + t*t*(x1-cx2)),
            y: 3*(mt*mt*(cy1-y0) + 2*mt*t*(cy2-cy1) + t*t*(y1-cy2))
        };
    }

    /**
     * Build a variable-width ribbon along a cubic bezier.
     *
     * widthFn(t) → number   returns the half-width at parameter t
     * direction: +1 = offset above (outward from wave body), -1 = below
     *
     * Returns an SVG polygon points string.
     */
    function ribbonPath(x0,y0, cx1,cy1, cx2,cy2, x1,y1, widthFn, direction, samples) {
        const N = samples || 80;
        const upper = [];
        const lower = [];

        for (let k = 0; k <= N; k++) {
            const t  = k / N;
            const pt = bezierPt(t, x0,y0, cx1,cy1, cx2,cy2, x1,y1);
            const tg = bezierTangent(t, x0,y0, cx1,cy1, cx2,cy2, x1,y1);
            const len = Math.sqrt(tg.x*tg.x + tg.y*tg.y) || 1;
            // Unit normal pointing "up" (away from wave body)
            const nx = -tg.y / len * direction;
            const ny =  tg.x / len * direction;

            const w = widthFn(t);
            upper.push({ x: pt.x + nx * w, y: pt.y + ny * w });
            lower.push({ x: pt.x,           y: pt.y           });
        }

        // Closed polygon: upper forward, lower reversed
        const pts = [...upper, ...[...lower].reverse()];
        return pts.map(p => `${p.x.toFixed(2)},${p.y.toFixed(2)}`).join(' ');
    }

    /**
     * Build an organic width function from a sum of sines.
     * maxW    – peak width in pixels
     * Returns a function t → width
     */
    function organicWidth(maxW) {
        // 3–5 harmonics at random frequencies and phases
        const harmonics = Array.from({ length: 3 + Math.floor(rand(0,3)) }, () => ({
            freq:  rand(1.2, 6),
            phase: rand(0, Math.PI * 2),
            amp:   rand(0.2, 1.0)
        }));
        const totalAmp = harmonics.reduce((s, h) => s + h.amp, 0);

        return (t) => {
            let v = 0;
            for (const h of harmonics) {
                v += h.amp * (0.5 + 0.5 * Math.sin(h.freq * t * Math.PI * 2 + h.phase));
            }
            v /= totalAmp; // 0..1
            // Fade to near-zero at both ends so ribbon doesn't clip abruptly
            const fade = Math.min(1, t * 8, (1 - t) * 8);
            return v * fade * maxW;
        };
    }

    // ── Background ────────────────────────────────────────────────────
    const bg1 = `hsl(${hueBase}, 75%, 10%)`;
    const bg2 = `hsl(${(hueBase + 50) % 360}, 85%, 5%)`;
    const bg3 = `hsl(${(hueBase + 20) % 360}, 60%, 7%)`;

    let defs = '';
    let body = '';

    defs += `
      <linearGradient id="bg-grad" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%"   stop-color="${bg1}"/>
        <stop offset="55%"  stop-color="${bg3}"/>
        <stop offset="100%" stop-color="${bg2}"/>
      </linearGradient>`;

    body += `<rect width="${W}" height="${H}" fill="url(#bg-grad)"/>`;

    // ── Grain overlay filter ───────────────────────────────────────────
    defs += `
      <filter id="topgrain" x="0%" y="0%" width="100%" height="100%"
              color-interpolation-filters="sRGB">
        <feTurbulence type="fractalNoise" baseFrequency="0.82" numOctaves="4"
                      stitchTiles="stitch" result="n"/>
        <feColorMatrix type="saturate" values="0" in="n" result="gn"/>
        <feBlend in="SourceGraphic" in2="gn" mode="soft-light"/>
        <feComposite in2="SourceGraphic" operator="in"/>
      </filter>`;

    // ── Waves ──────────────────────────────────────────────────────────
    const waveCount = 5 + Math.floor(Math.random() * 4);

    for (let i = 0; i < waveCount; i++) {
        const depth    = i / (waveCount - 1);   // 0 = back, 1 = front
        const wid      = `w${i}`;
        const waveHue  = (hueBase + rand(-60, 60) + 360) % 360;
        const L1       = 25 + depth * 45;
        const L2       = 10 + depth * 30;
        const alpha1   = 0.12 + depth * 0.30;
        const alpha2   = 0.04 + depth * 0.12;

        // Wave bezier control points
        const y1  = H * rand(0.05, 0.85);
        const y2  = H * rand(0.05, 0.85);
        const cx1 = W * rand(0.10, 0.35);
        const cy1 = H * rand(-0.1, 1.1);
        const cx2 = W * rand(0.55, 0.80);
        const cy2 = H * rand(-0.1, 1.1);

        const wavePath = `M 0,${H} L 0,${y1} C ${cx1},${cy1} ${cx2},${cy2} ${W},${y2} L ${W},${H} Z`;

        // ── Wave body gradient ─────────────────────────────────────────
        defs += `
          <linearGradient id="wg-${wid}" x1="0%" y1="0%" x2="${rand(0,40).toFixed(0)}%" y2="${rand(60,100).toFixed(0)}%">
            <stop offset="0%"   stop-color="hsl(${waveHue},100%,${L1}%)" stop-opacity="${alpha1}"/>
            <stop offset="60%"  stop-color="hsl(${(waveHue+25)%360},90%,${L2}%)" stop-opacity="${(alpha1*0.5).toFixed(3)}"/>
            <stop offset="100%" stop-color="hsl(${(waveHue+50)%360},80%,${L2}%)" stop-opacity="${alpha2}"/>
          </linearGradient>`;

        // ── Refraction filter ──────────────────────────────────────────
        const freq = (0.004 + depth * 0.008 + rand(-0.002, 0.002)).toFixed(4);
        const disp = 8 + depth * 12;
        defs += `
          <filter id="refract-${wid}" x="-5%" y="-5%" width="110%" height="110%"
                  color-interpolation-filters="sRGB" primitiveUnits="userSpaceOnUse">
            <feTurbulence type="turbulence" baseFrequency="${freq} ${(parseFloat(freq)*1.5).toFixed(4)}"
                          numOctaves="3" seed="${Math.floor(rand(1,99))}" result="tb"/>
            <feDisplacementMap in="SourceGraphic" in2="tb"
                               scale="${disp}" xChannelSelector="R" yChannelSelector="G"/>
          </filter>`;

        // ── Scatter / glow filter ──────────────────────────────────────
        defs += `
          <filter id="scatter-${wid}" x="-10%" y="-15%" width="120%" height="130%">
            <feGaussianBlur stdDeviation="${6 + depth * 10}"/>
          </filter>`;

        // ── Clip path ──────────────────────────────────────────────────
        defs += `
          <clipPath id="clip-${wid}">
            <path d="${wavePath}"/>
          </clipPath>`;

        // ── Caustic blobs ──────────────────────────────────────────────
        const causticCount = 2 + Math.floor(rand(0, 3));
        let caustics = '';
        for (let c = 0; c < causticCount; c++) {
            const cid = `caust-${wid}-${c}`;
            const t   = rand(0.1, 0.9);
            const mt  = 1 - t;
            const ccx = mt*mt*mt*0 + 3*mt*mt*t*cx1 + 3*mt*t*t*cx2 + t*t*t*W;
            const ccy = mt*mt*mt*y1 + 3*mt*mt*t*cy1 + 3*mt*t*t*cy2 + t*t*t*y2
                        + rand(-H*0.12, H*0.12);
            const rx  = rand(60, 220);
            const ry  = rand(20, 80);
            const rot = rand(-25, 25);
            const ca  = (0.04 + depth * 0.10).toFixed(3);
            defs += `
              <radialGradient id="${cid}" cx="50%" cy="50%" r="50%">
                <stop offset="0%"   stop-color="hsl(${waveHue+20},100%,95%)" stop-opacity="${ca}"/>
                <stop offset="100%" stop-color="hsl(${waveHue},100%,70%)"    stop-opacity="0"/>
              </radialGradient>`;
            caustics += `<ellipse cx="${ccx.toFixed(1)}" cy="${ccy.toFixed(1)}"
                           rx="${rx.toFixed(1)}" ry="${ry.toFixed(1)}"
                           transform="rotate(${rot.toFixed(1)},${ccx.toFixed(1)},${ccy.toFixed(1)})"
                           fill="url(#${cid})"
                           style="mix-blend-mode:color-dodge; clip-path:url(#clip-${wid})"/>`;
        }

        // ── Build variable-width ribbon edges ──────────────────────────
        //
        // We create THREE ribbons, each with its own organic width profile:
        //
        //   1. Glow ribbon   – wide, soft, blurred, screen-blended
        //   2. Bright ribbon – narrow, hard, overlay-blended  (the "glass lip")
        //   3. Shadow ribbon – thin, dark, offset below, multiply-blended

        // Glow: max width 18–40px depending on depth
        const glowMaxW  = rand(18, 40) * (0.4 + depth * 0.8);
        const glowRibPts = ribbonPath(
            0, y1, cx1, cy1, cx2, cy2, W, y2,
            organicWidth(glowMaxW), +1, 100
        );

        // Bright edge: max width 1–6px — narrow but organic
        const edgeMaxW  = rand(1, 6) * (0.5 + depth * 0.7);
        const edgeRibPts = ribbonPath(
            0, y1, cx1, cy1, cx2, cy2, W, y2,
            organicWidth(edgeMaxW), +1, 100
        );

        // Shadow: max width 2–8px, offset by a small amount downward
        // We do this by building a second ribbon starting slightly below the curve.
        // Hack: shift cy1,cy2,y1,y2 down by a few pixels.
        const shOff     = 2 + depth * 4;
        const shadowRibPts = ribbonPath(
            0, y1+shOff, cx1, cy1+shOff, cx2, cy2+shOff, W, y2+shOff,
            organicWidth(rand(2, 8)), -1, 100  // direction -1 = below the line
        );

        // Specular glint circles (still useful for point highlights)
        const glintCount = 3 + Math.floor(rand(0, 4));
        let glints = '';
        for (let g = 0; g < glintCount; g++) {
            const t  = rand(0.05, 0.95);
            const mt = 1 - t;
            const gx = mt*mt*mt*0 + 3*mt*mt*t*cx1 + 3*mt*t*t*cx2 + t*t*t*W;
            const gy = mt*mt*mt*y1 + 3*mt*mt*t*cy1 + 3*mt*t*t*cy2 + t*t*t*y2;
            const gr = rand(3, 10) * (0.5 + depth * 0.5);
            const ga = (0.3 + depth * 0.5 + rand(-0.1, 0.1)).toFixed(2);
            defs += `
              <filter id="gblur-${wid}-${g}">
                <feGaussianBlur stdDeviation="${(gr*0.5).toFixed(1)}"/>
              </filter>`;
            glints += `<circle cx="${gx.toFixed(1)}" cy="${gy.toFixed(1)}" r="${gr.toFixed(1)}"
                         fill="white" fill-opacity="${ga}"
                         filter="url(#gblur-${wid}-${g})"
                         style="mix-blend-mode:overlay"/>`;
        }

        // ── Assemble ────────────────────────────────────────────────────
        body += `
          <!-- ── Wave ${i}  depth=${depth.toFixed(2)} ── -->

          <!-- Sub-surface scatter glow -->
          <path d="${wavePath}"
                fill="hsl(${waveHue},100%,${L1}%)"
                fill-opacity="${(0.06 + depth * 0.12).toFixed(3)}"
                filter="url(#scatter-${wid})"
                style="mix-blend-mode:screen"/>

          <!-- Wave body -->
          <path d="${wavePath}"
                fill="url(#wg-${wid})"
                filter="url(#refract-${wid})"
                style="mix-blend-mode:color-dodge"/>

          <!-- Caustic light patches -->
          ${caustics}

          <!-- Glow ribbon (variable-width soft halo) -->
          <polygon points="${glowRibPts}"
                   fill="hsl(${waveHue},100%,85%)"
                   fill-opacity="${(0.08 + depth * 0.14).toFixed(3)}"
                   filter="url(#scatter-${wid})"
                   style="mix-blend-mode:screen; clip-path:url(#clip-${wid})"/>

          <!-- Bright edge ribbon (variable-width hard glass lip) -->
          <polygon points="${edgeRibPts}"
                   fill="rgba(255,255,255,${(0.22 + depth * 0.38).toFixed(2)})"
                   style="mix-blend-mode:overlay"/>

          <!-- Shadow underside ribbon (variable-width dark underedge) -->
          <polygon points="${shadowRibPts}"
                   fill="rgba(0,0,0,0.28)"
                   style="mix-blend-mode:multiply"/>

          <!-- Specular glint dots -->
          ${glints}`;
    }

    // ── Grain overlay ──────────────────────────────────────────────────
    body += `<rect width="${W}" height="${H}" fill="transparent"
               filter="url(#topgrain)" opacity="0.16"/>`;

    // ── Assemble SVG ───────────────────────────────────────────────────
    const svg = `<svg xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 ${W} ${H}"
                      preserveAspectRatio="none">
        <defs>${defs}</defs>
        ${body}
      </svg>`;

    const enc = encodeURIComponent(svg).replace(/'/g, '%27').replace(/"/g, '%22');
    return `url("data:image/svg+xml;charset=utf-8,${enc}")`;
}

// ── Apply on load ──────────────────────────────────────────────────────
$(document).ready(function () {
    $('body').css({
        'background-image':      generateRealisticGlassWaves(),
        'background-size':       'cover',
        'background-attachment': 'fixed',
        'background-position':   'center'
    });
});

})(jQuery);

