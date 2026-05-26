/**
 * uiglass.js  — v4
 * ─────────────────────────────────────────────────────────────────────────────
 * All UI logic for the glass design system. No inline scripts in any blade.
 *
 * Sections
 *   A. State & refs
 *   B. Panel helpers  (position, open, close, traffic lights, drag)
 *   C. Genie effect   (SVG clip-path morph: icon → panel, panel → icon)
 *   D. Dock magnification
 *   E. SPA engine     (fetch-swap #content, loader bar, popstate)
 *   F. Click handlers (dock items, panel nav, backdrop, keyboard)
 *   G. Sidebar2       (accordion, mobile toggle, SPA links)
 *   H. Nav-mode switcher (AJAX save → reload)
 *   I. Haptic & smooth scroll
 *   J. Liquid glass background generator
 *   K. Module Dropzone (drag & drop file upload)
 * ─────────────────────────────────────────────────────────────────────────────
 */
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

    var openPanel = function openPanel($item, panelId) {
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

    var animClose = function animClose($panel, cb) {
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
    // Reset magnification on load and after SPA navigation
    function resetDockMagnification() {
        $('.dock-item').each(function () { 
            this.style.setProperty('--mag', 1); 
        });
    }
    
    // Initial reset on page load
    if ($dock.length) {
        resetDockMagnification();
        
        // Also reset sidebar2 icons if present
        function resetSidebar2Styles() {
            $('.sb2-icon, .sb2-icon i').css('transform', '');
        }
        resetSidebar2Styles();
    }
    
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

    // Force hard refresh navigation (for pages with complex scripts)
    function forceNavigate(href) {
        window.location.href = href;
    }

    // SPA navigation - use for simple pages
    function spaNavigate(href, direction) {
        if (navigating) return;
        if (href === window.location.href) return;  
        navigating = true;
        direction  = direction || 'forward';

        // Trigger cleanup event for current page
        $(document).trigger('spa:before-navigate');

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
                // ⭐ CLEANUP OLD DASHBOARD STATE BEFORE SWAPPING CONTENT
                if (typeof window.destroyDashboard === 'function') {
                    console.log('[SPA] Destroying old dashboard state');
                    window.destroyDashboard();
                }
                
                // ⭐ NOW SWAP CONTENT
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
                        
                        // ⭐ RE-INITIALIZE DASHBOARD IF WE'RE BACK ON DASHBOARD
                        setTimeout(function() {
                            var isDashboard = document.querySelector('#homeDashboardGrid') !== null;
                            if (isDashboard && typeof window.initDashboard === 'function') {
                                console.log('[SPA] Detected dashboard, reinitializing');
                                window.initDashboard();
                            }
                        }, 100);
                        
                    } catch(e) {}
                });

                updateDockActive(href);
                
                // Reset dock magnification after SPA navigation
                if ($dock.length) {
                    resetDockMagnification();
                }
                // Reset sidebar2 styles if present
                if ($('#sidebar2').length) {
                    resetSidebar2Styles();
                }
                
                // Force styles to apply after SPA navigation
                document.body.style.display = 'none';
                document.body.offsetHeight; // trigger reflow
                document.body.style.display = '';
                
                console.log('[SPA] Navigation complete to:', href);

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
        // ── Dock items & panel nav ────────────────────────────────────────
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

        // ── Sidebar2 items & children ─────────────────────────────────────
        $('.sb2-item').each(function () {
            var ih = $(this).attr('href');
            $(this).toggleClass('active', !!ih && ih !== '#' && href === ih);
        });
        $('.sb2-child').each(function () {
            var ih = $(this).attr('href');
            var on = !!ih && ih !== '#' && href === ih;
            $(this).toggleClass('active', on);
        });
        // Sync parent group trigger: active if any child is active
        $('.sb2-group').each(function () {
            var hasActive = $(this).find('.sb2-child.active').length > 0;
            $(this).find('.sb2-group-trigger').toggleClass('active', hasActive);
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
        if ($(this).hasClass('locked')) { 
            e.preventDefault();
            jiggle($(this));
            return; 
        }
        e.preventDefault();
        e.stopPropagation();
        
        closePanel();
        
        forceNavigate(href);
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

// J. Liquid glass background generator — removed for day theme (plain white background)



    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       GENIE EFFECT
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Replaces the plain CSS panelOpen/panelClose animations with a
       SVG clip-path morph that grows from the dock icon up to the full
       panel size — just like macOS Genie.

       How it works:
         1. On openPanel()  → playGenie('open',  $item, $panel, done)
         2. On closePanel() → playGenie('close', $item, $panel, done)
         3. Each rAF frame builds a quad bezier SVG path that morphs
            between [icon rect] ↔ [panel rect], with curved sides that
            "bulge" outward at the midpoint of travel.
         4. This path is applied as clip-path on the panel every frame.
         5. A matching scaleY squish on the panel content reinforces depth.
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    var GENIE_MS = 400;

    function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }

    function easeOutExpo(t) { return t >= 1 ? 1 : 1 - Math.pow(2, -10 * t); }
    function easeInExpo(t)  { return t <= 0 ? 0 : Math.pow(2, 10 * t - 10); }

    function lerpN(a, b, t) { return a + (b - a) * t; }

    /**
     * Build the SVG path string for the genie shape at progress p (0→1).
     * iRect = icon bounding rect, pRect = panel bounding rect
     * opening = true means icon→panel (else panel→icon reversed)
     */
    function geniePathAt(iRect, pRect, p, opening) {
        // ease the progress
        var ep = opening ? easeOutExpo(p) : 1 - easeOutExpo(1 - p);

        // icon centre x
        var icx = iRect.left + iRect.width / 2;
        var ihw = iRect.width  / 2;  // icon half-width

        // Four corners interpolate between icon rect and panel rect
        var topY = lerpN(iRect.top,    pRect.top,    ep);
        var botY = lerpN(iRect.bottom, pRect.bottom, ep);
        var topL = lerpN(icx - ihw,    pRect.left,   ep);
        var topR = lerpN(icx + ihw,    pRect.right,  ep);
        var botL = lerpN(icx - ihw,    pRect.left,   ep);
        var botR = lerpN(icx + ihw,    pRect.right,  ep);

        // Bulge — peaks mid-travel, zero at start and end.
        // The shape swells outward like smoke, then settles flat.
        var raw   = Math.sin(p * Math.PI);
        var bulge = raw * (pRect.width * 0.22) * (1 - ep * 0.8);

        // Left cubic bezier control points (push outward = subtract x)
        var lc1x = lerpN(topL, botL, 0.33) - bulge;
        var lc1y = lerpN(topY, botY, 0.33);
        var lc2x = lerpN(topL, botL, 0.66) - bulge;
        var lc2y = lerpN(topY, botY, 0.66);

        // Right cubic bezier control points (push outward = add x)
        var rc1x = lerpN(topR, botR, 0.33) + bulge;
        var rc1y = lerpN(topY, botY, 0.33);
        var rc2x = lerpN(topR, botR, 0.66) + bulge;
        var rc2y = lerpN(topY, botY, 0.66);

        return [
            'M ' + topL.toFixed(1) + ',' + topY.toFixed(1),
            'L ' + topR.toFixed(1) + ',' + topY.toFixed(1),
            'C ' + rc1x.toFixed(1) + ',' + rc1y.toFixed(1) +
              ' ' + rc2x.toFixed(1) + ',' + rc2y.toFixed(1) +
              ' ' + botR.toFixed(1) + ',' + botY.toFixed(1),
            'L ' + botL.toFixed(1) + ',' + botY.toFixed(1),
            'C ' + lc2x.toFixed(1) + ',' + lc2y.toFixed(1) +
              ' ' + lc1x.toFixed(1) + ',' + lc1y.toFixed(1) +
              ' ' + topL.toFixed(1) + ',' + topY.toFixed(1),
            'Z'
        ].join(' ');
    }

    /**
     * Animate genie morph on $panel, originating from $item (dock icon).
     * dir = 'open' | 'close'
     * done = callback when complete
     */
    function playGenie(dir, $item, $panel, done) {
        // Ensure the SVG genie elements exist in DOM
        var $svg  = $('#genie-svg');
        var $path = $('#genie-path');
        if (!$svg.length || !$path.length) { done && done(); return; }

        var iconEl  = $item.find('.dock-icon')[0];
        var panelEl = $panel[0];

        if (!iconEl || !panelEl) { done && done(); return; }

        var iRect = iconEl.getBoundingClientRect();
        var pRect = panelEl.getBoundingClientRect();

        // Attach clip to panel
        panelEl.style.clipPath  = 'url(#genie-clip)';
        panelEl.style.willChange = 'clip-path, transform';
        $svg[0].style.display   = 'block';

        var start   = null;
        var opening = (dir === 'open');

        function frame(now) {
            if (!start) start = now;
            var rawT = clamp((now - start) / GENIE_MS, 0, 1);
            var p    = opening ? rawT : 1 - rawT;   // p=0→1 for open, 1→0 for close

            // Re-measure panel each frame so dragged panels still clip correctly
            var currentPRect = panelEl.getBoundingClientRect();

            // Update clip path
            $path[0].setAttribute('d', geniePathAt(iRect, currentPRect, p, opening));

            // Squish panel content — stretches out of the icon
            var sy = opening ? easeOutExpo(rawT) : 1 - easeOutExpo(rawT);
            sy = clamp(sy, 0.01, 1);
            var originX = (iRect.left + iRect.width  / 2).toFixed(0) + 'px';
            var originY = opening ? currentPRect.bottom.toFixed(0) + 'px'
                                  : currentPRect.bottom.toFixed(0) + 'px';
            panelEl.style.transformOrigin = originX + ' ' + originY;
            panelEl.style.transform       = 'scaleY(' + sy.toFixed(4) + ')';

            if (rawT < 1) {
                requestAnimationFrame(frame);
            } else {
                // Cleanup
                panelEl.style.clipPath        = '';
                panelEl.style.transform       = '';
                panelEl.style.transformOrigin = '';
                panelEl.style.willChange      = '';
                $svg[0].style.display         = 'none';
                $path[0].setAttribute('d', '');
                done && done();
            }
        }

        requestAnimationFrame(frame);
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       PATCH: replace openPanel / animClose to use genie
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    // Override openPanel defined earlier in this file
    openPanel = function ($item, panelId) {
        var $panel = $('#' + panelId);
        if (!$panel.length) return;

        // Toggle: clicking the same icon closes it
        if (openPanelId === panelId) {
            // play genie close
            var $src = $('.dock-item.panel-open-source');
            playGenie('close', $src.length ? $src : $item, $panel, function () {
                $panel.removeClass('panel-open').css('display', '');
                $('.dock-item').removeClass('panel-open-source');
                $backdrop.removeClass('active');
            });
            openPanelId = null;
            return;
        }

        // Close any other open panel instantly (snap out)
        if (openPanelId) {
            $('#' + openPanelId)
                .removeClass('panel-open panel-closing panel-minimized panel-fullscreen')
                .css({ display: '', clipPath: '', transform: '', transformOrigin: '' });
            $('.dock-item').removeClass('panel-open-source');
        }

        panelState[panelId] = { minimized: false, fullscreen: false };
        $panel.removeClass('panel-minimized panel-fullscreen panel-closing panel-open');

        openPanelId = panelId;
        $item.addClass('panel-open-source');

        // Show panel (invisible) so we can measure it
        $panel.css({ display: 'flex', visibility: 'hidden', opacity: 0 });
        positionPanel($panel, $item);
        $panel.css({ visibility: '', opacity: 1 });
        $backdrop.addClass('active');

        // Play genie open
        playGenie('open', $item, $panel, function () {
            $panel.addClass('panel-open');
        });
    };

    // Override animClose to use genie
    animClose = function ($panel, cb) {
        var panelId = $panel.attr('id');
        var $src    = $('.dock-item[data-panel="' + panelId + '"]');

        // If we can't find source item, just snap out
        if (!$src.length) {
            $panel.removeClass('panel-open').addClass('panel-closing');
            $panel.one('animationend', function () {
                $panel.removeClass('panel-closing').css('display', '');
                if (cb) cb();
            });
            return;
        }

        playGenie('close', $src, $panel, function () {
            $panel
                .removeClass('panel-open panel-minimized panel-fullscreen panel-closing')
                .css('display', '');
            if (cb) cb();
        });
    };

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       NAV-MODE SWITCHER
       Reads the AJAX URL from a data attribute so we don't need Blade
       template syntax inside this static JS file.

       Add to your sidebar blade:
         <div id="macos-dock" data-navmode-url="/settings/nav-mode" ...>

       Or fall back to a conventional path if the attribute is absent.
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    $(document).on('click', '.nav-mode-btn', function () {
        var mode      = $(this).data('mode');
        var $switcher = $(this).closest('.nav-mode-switcher');
        var current   = $switcher.data('current') || $switcher.attr('data-current');

        // Ignore if already on this mode
        if (!mode || current === mode) return;

        // Visual feedback — disable buttons while saving
        var $btns = $switcher.find('.nav-mode-btn').prop('disabled', true);
        $switcher.css('opacity', 0.6);

        // Optimistic UI
        $btns.each(function () {
            $(this).toggleClass('active', $(this).data('mode') === mode);
            $(this).attr('aria-pressed', $(this).data('mode') === mode ? 'true' : 'false');
        });

        /*
         * URL resolution order:
         *   1. data-navmode-url on #macos-dock (dock mode)
         *   2. data-navmode-url on #sidebar2   (sidebar mode)
         *   3. <meta name="navmode-url"> in <head>
         *   4. Hardcoded fallback
         *
         * IMPORTANT: send as application/x-www-form-urlencoded so
         * Laravel's $request->input() can read the body.
         * JSON body requires $request->json() and the controller
         * previously used $request->input() which ignores JSON.
         */
        var url  = $('#macos-dock').data('navmode-url')
                || $('#sidebar2').data('navmode-url')
                || $('meta[name="navmode-url"]').attr('content')
                || '/settings/nav-mode';

        var csrf = $('meta[name="csrf-token"]').attr('content') || '';

        $.ajax({
            url:         url,
            method:      'POST',
            // Form-encoded — Laravel $request->input() reads this correctly
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data:        { _token: csrf, mode: mode },
            dataType:    'json',
            success: function (res) {
                if (res && res.success) {
                    window.location.reload();
                } else {
                    // Server responded but didn't confirm — reload anyway
                    window.location.reload();
                }
            },
            error: function (xhr) {
                console.warn('[nav-mode] AJAX error', xhr.status, xhr.responseText);
                // Re-enable buttons so user can retry
                $btns.prop('disabled', false);
                $switcher.css('opacity', 1);
                // Revert optimistic UI
                $btns.each(function () {
                    $(this).toggleClass('active', $(this).data('mode') === current);
                    $(this).attr('aria-pressed', $(this).data('mode') === current ? 'true' : 'false');
                });
            }
        });
    });


    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       SIDEBAR 2  —  Floating liquid glass sidebar (nav_mode = "sidebar")
       All logic runs harmlessly when sidebar2 is NOT in the DOM.
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    /* ── LocalStorage key for accordion state ─────────────────────────── */
    var SB2_STATE_KEY = 'sb2_accordion_state';

    /* ── Load saved accordion state on page load ──────────────────────── */
    function loadSb2State() {
        try {
            var saved = localStorage.getItem(SB2_STATE_KEY);
            if (saved) {
                var openGroups = JSON.parse(saved);
                openGroups.forEach(function (index) {
                    var $group = $('.sb2-group').eq(index);
                    if ($group.length) {
                        $group.addClass('open');
                        $group.find('.sb2-group-trigger').attr('aria-expanded', 'true');
                    }
                });
            }
        } catch (e) {
            console.warn('[sidebar2] Failed to load accordion state:', e);
        }
    }

    /* ── Save current accordion state to localStorage ─────────────────── */
    function saveSb2State() {
        try {
            var openIndexes = [];
            $('.sb2-group.open').each(function (i) {
                var index = $(this).index('.sb2-group');
                openIndexes.push(index);
            });
            localStorage.setItem(SB2_STATE_KEY, JSON.stringify(openIndexes));
        } catch (e) {
            console.warn('[sidebar2] Failed to save accordion state:', e);
        }
    }

    /* ── Accordion groups with auto-close and state persistence ────────── */
    $(document).on('click', '.sb2-group-trigger', function () {
        if ($(this).hasClass('locked')) return;
        
        var $trigger = $(this);
        var $group   = $trigger.closest('.sb2-group');
        var wasOpen  = $group.hasClass('open');

        // Close ALL other groups (accordion behavior)
        $('.sb2-group').not($group).each(function () {
            $(this).removeClass('open');
            $(this).find('.sb2-group-trigger').attr('aria-expanded', 'false');
        });

        // Toggle current group
        if (wasOpen) {
            $group.removeClass('open');
            $trigger.attr('aria-expanded', 'false');
        } else {
            $group.addClass('open');
            $trigger.attr('aria-expanded', 'true');
        }

        // Save state to localStorage
        saveSb2State();
    });

    /* ── Mobile sidebar open / close ──────────────────────────────────── */
    var $sb2     = $('#sidebar2');
    var $sb2Back = $('#sb2-backdrop');
    var $sb2Tog  = $('#sb2-toggle');

    function openSb2Mobile() {
        $sb2.addClass('mobile-open');
        $sb2Back.addClass('active');
        $('body').css('overflow', 'hidden');
    }
    
    function closeSb2Mobile() {
        $sb2.removeClass('mobile-open');
        $sb2Back.removeClass('active');
        $('body').css('overflow', '');
    }

    $sb2Tog.on('click', function () {
        if ($sb2.hasClass('mobile-open')) {
            closeSb2Mobile();
        } else {
            openSb2Mobile();
        }
    });
    
    $sb2Back.on('click', closeSb2Mobile);
    
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $sb2.hasClass('mobile-open')) {
            closeSb2Mobile();
        }
    });

    /* ── Force hard refresh for sidebar2 links (for pages with inline scripts) ─ */
    $(document).on('click', '.sb2-child, .sb2-item', function (e) {
        var href = $(this).attr('href');
        if (!href || href === '#' || $(this).hasClass('locked')) return;
        e.preventDefault();
        closeSb2Mobile();
        forceNavigate(href);      // Force hard refresh for pages with complex scripts
    });

    /* ── Initialize sidebar2 state on document ready ──────────────────── */
    $(document).ready(function () {
        if ($('#sidebar2').length) {
            loadSb2State();
        }
    });
    
    /* ── Settings Tabs — glass-tab switching with sessionStorage persistence ── */

    var SETTINGS_TAB_KEY = 'settings_active_tab';

    function initSettingsTabs() {
        var tabWrapper = document.getElementById('settingsTab');
        if (!tabWrapper) return;

        // Guard: already wired up this exact DOM node
        if (tabWrapper._glassTabsInit) return;
        tabWrapper._glassTabsInit = true;

        function switchTab(tabName, save) {
            // Buttons
            document.querySelectorAll('#settingsTab .glass-tab').forEach(function (b) {
                b.classList.toggle('active', b.getAttribute('data-tab') === tabName);
            });
            // Panes
            document.querySelectorAll('#settingsTabContent .tab-pane').forEach(function (p) {
                p.classList.remove('show', 'active');
            });
            requestAnimationFrame(function () {
                var pane = document.getElementById(tabName);
                if (pane) pane.classList.add('show', 'active');
            });
            // Persist so the tab survives SPA navigation away and back
            if (save !== false) {
                try { sessionStorage.setItem(SETTINGS_TAB_KEY, tabName); } catch(e) {}
            }
        }

        // Wire click handlers
        tabWrapper.querySelectorAll('.glass-tab').forEach(function (btn) {
            btn.addEventListener('click', function () {
                switchTab(this.getAttribute('data-tab'));
            });
        });

        // Restore last active tab (after form submit redirect or SPA back-nav)
        var saved = null;
        try { saved = sessionStorage.getItem(SETTINGS_TAB_KEY); } catch(e) {}
        if (saved && document.getElementById(saved)) {
            switchTab(saved, false); // don't re-save, just restore
        }
    }

    /* Auto-detect when settings page appears (SPA swap injects new #content) */
    var _settingsTabObserver = new MutationObserver(function () {
        if (document.getElementById('settingsTab')) {
            initSettingsTabs();
        }
    });
    _settingsTabObserver.observe(document.body, { childList: true, subtree: true });

    /* Also run immediately on first hard load */
    document.addEventListener('DOMContentLoaded', initSettingsTabs);
    /* And on SPA spa:loaded event fired by spaNavigate */
    $(document).on('spa:loaded', function () { initSettingsTabs(); });

    /**
 * MODULE DROPZONE FIX - Replace the existing MODULE DROPZONE section in uiglass.js
 * (Lines 867-1133) with this improved version
 *
 * FIXES:
 * 1. Browse button not working - Added explicit event handling
 * 2. Drag & drop file not being processed - Fixed file attachment
 * 3. AJAX response handling - Better handling for both JSON and redirect responses
 */

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       MODULE DROPZONE  —  modules/index.blade.php
       Event delegation on $(document) — works on hard load AND SPA nav,
       no init function, no timing issues.
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    // Dropzone state
    var dropzoneState = {
        files: [],
        hasFile: false
    };

    // Prevent default drag behaviors on document
    $(document).on('dragenter dragover dragleave drop', function(e) {
        // Only prevent if we're not over a dropzone area
        if (!$(e.target).closest('#moduleDropzone').length) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    // Highlight dropzone on drag enter/over
    $(document).on('dragenter dragover', '#moduleDropzone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over').css({
            'border-color': '#22c55e',
            'background': 'rgba(34, 197, 94, 0.1)'
        });
    });

    // Unhighlight on drag leave
    $(document).on('dragleave', '#moduleDropzone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Only remove highlight if actually leaving the dropzone
        var rect = this.getBoundingClientRect();
        if (e.originalEvent.clientX < rect.left || e.originalEvent.clientX >= rect.right ||
            e.originalEvent.clientY < rect.top || e.originalEvent.clientY >= rect.bottom) {
            $(this).removeClass('drag-over').css({
                'border-color': '#a5b4fc',
                'background': 'rgba(255, 255, 255, 0.05)'
            });
        }
    });

    // Handle file drop - ✅ FIX: Improved drop handling
    $(document).on('drop', '#moduleDropzone', function(e) {
        e.preventDefault();
        e.stopPropagation();

        $(this).removeClass('drag-over').css({
            'border-color': '#a5b4fc',
            'background': 'rgba(255, 255, 255, 0.05)'
        });

        var files = e.originalEvent.dataTransfer.files;
        console.log('[Dropzone] Dropped files:', files.length);

        if (files.length === 0) {
            console.warn('[Dropzone] No files in drop event');
            return;
        }

        // ✅ FIX: Clear previous files before adding new ones
        dropzoneState.files = [];
        dropzoneState.hasFile = false;

        for (var i = 0; i < files.length; i++) {
            handleDropzoneFile(files[i]);
        }
    });

    // ✅ FIX: Browse button click - with explicit prevention and triggering
    $(document).on('click', '#dropzoneBrowseBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('[Dropzone] Browse button clicked');

        var $fileInput = $('#moduleFileInput');
        if ($fileInput.length) {
            // ✅ FIX: Clear the input first to allow re-selecting same file
            $fileInput.val('');
            $fileInput[0].click();
        } else {
            console.error('[Dropzone] File input not found!');
        }
    });

    // File input change - ✅ FIX: Clear previous state before adding
    $(document).on('change', '#moduleFileInput', function() {
        console.log('[Dropzone] File input changed:', this.files.length, 'file(s)');

        if (this.files.length === 0) {
            console.warn('[Dropzone] No files selected');
            return;
        }

        // ✅ FIX: Clear previous files
        dropzoneState.files = [];
        dropzoneState.hasFile = false;

        for (var i = 0; i < this.files.length; i++) {
            handleDropzoneFile(this.files[i]);
        }
    });

    // Handle the file (validation + UI update)
    function handleDropzoneFile(file) {
        console.log('[Dropzone] Handling file:', file.name, 'Size:', file.size);

        // Validate file type
        var accept = $('#moduleDropzone').data('accept') || '.zip';
        if (accept !== '*') {
            var allowed = accept.split(',').map(function(e){ return e.trim().toLowerCase(); });
            var ext = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowed.includes(ext)) {
                showDropzoneError('Allowed file types: ' + accept);
                console.error('[Dropzone] Invalid file type:', ext);
                return;
            }
        }

        // Validate size (50MB max by default)
        var maxMB = parseInt($('#moduleDropzone').data('max-size')) || 50;
        if (file.size > maxMB * 1024 * 1024) {
            showDropzoneError('File too large! Max size is ' + maxMB + 'MB.');
            console.error('[Dropzone] File too large:', file.size);
            return;
        }

        // Store file in state
        dropzoneState.files.push(file);
        dropzoneState.hasFile = true;

        // Update UI
        updateDropzoneUI();
        hideDropzoneError();

        console.log('[Dropzone] File accepted:', file.name, 'Total files:', dropzoneState.files.length);
    }

    // Update UI to show file info
    function updateDropzoneUI() {
        var files  = dropzoneState.files;
        var $dropzone = $('#moduleDropzone');
        var $content = $dropzone.find('.dropzone-content');
        var $fileInfo = $('#dropzoneFileInfo');
        var $fileName = $('#dropzoneFileName');
        var $fileSize = $('#dropzoneFileSize');
        var $uploadBtn = $('#moduleUploadBtn');
        var $icon = $('#dropzoneIcon');

        // Hide content, show file info
        $content.hide();
        $fileInfo.show();

        // Show name or count
        if (files.length === 1) {
            $fileName.text(files[0].name);
            $fileSize.text('(' + formatFileSize(files[0].size) + ')');
        } else {
            var totalSize = files.reduce(function(sum, f){ return sum + f.size; }, 0);
            $fileName.text(files.length + ' files selected');
            $fileSize.text('(' + formatFileSize(totalSize) + ' total)');
        }

        // Show upload button
        $uploadBtn.show();

        // Update icon
        $icon.removeClass('fa-cloud-upload-alt').addClass('fa-check-circle').css('color', '#22c55e');

        // Update dropzone style
        $dropzone.addClass('has-file').css({
            'border-style': 'solid',
            'border-color': '#22c55e',
            'background': 'rgba(34, 197, 94, 0.05)'
        });
    }

    // Remove file
    $(document).on('click', '#dropzoneFileRemove', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('[Dropzone] Remove file clicked');
        resetDropzone();
    });

    // Reset dropzone
    function resetDropzone() {
        var $dropzone = $('#moduleDropzone');
        var $content = $dropzone.find('.dropzone-content');
        var $fileInfo = $('#dropzoneFileInfo');
        var $uploadBtn = $('#moduleUploadBtn');
        var $fileInput = $('#moduleFileInput');
        var $icon = $('#dropzoneIcon');

        // Clear state
        dropzoneState.files = [];
        dropzoneState.hasFile = false;

        // Reset file input
        $fileInput.val('');

        // Reset UI
        $content.show();
        $fileInfo.hide();
        $uploadBtn.hide();

        // Reset icon
        $icon.removeClass('fa-check-circle').addClass('fa-cloud-upload-alt').css('color', '#a5b4fc');

        // Reset dropzone style
        $dropzone.removeClass('has-file').css({
            'border-style': 'dashed',
            'border-color': '#a5b4fc',
            'background': 'rgba(255, 255, 255, 0.05)'
        });

        hideDropzoneError();
        console.log('[Dropzone] Reset complete');
    }

    // Click on dropzone (browse if no file) - ✅ FIX: Better event handling
    $(document).on('click', '#moduleDropzone', function(e) {
        // Don't trigger if clicking on interactive elements
        if ($(e.target).closest('#dropzoneFileRemove, #dropzoneFileInfo, #dropzoneBrowseBtn, #moduleUploadBtn').length) {
            return;
        }

        // Only open file dialog if no file selected
        if (!dropzoneState.hasFile) {
            console.log('[Dropzone] Dropzone clicked, opening file dialog');
            var $fileInput = $('#moduleFileInput');
            $fileInput.val(''); // Clear first
            $fileInput[0].click();
        }
    });

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Show error
    function showDropzoneError(message) {
        $('#dropzoneError').text(message).show();
        $('#moduleDropzone').css('border-color', '#ef4444');
        setTimeout(hideDropzoneError, 5000);
    }

    // Hide error
    function hideDropzoneError() {
        $('#dropzoneError').hide().text('');
        $('#moduleDropzone').css('border-color', '#a5b4fc');
    }

    // ✅ FIX: Form submission with better response handling
    $(document).on('submit', '#moduleUploadForm', function(e) {
        e.preventDefault();

        if (!dropzoneState.hasFile || dropzoneState.files.length === 0) {
            showDropzoneError('Please select a file first!');
            console.error('[Dropzone] No files to submit');
            return false;
        }

        console.log('[Dropzone] Submitting form with', dropzoneState.files.length, 'file(s)');

        // Build FormData
        var formData = new FormData();
        var $form = $(this);
        var fieldName = $('#moduleDropzone').data('field-name') || 'module_zip';
        var isMultiple = dropzoneState.files.length > 1;

        dropzoneState.files.forEach(function(file, index) {
            console.log('[Dropzone] Appending file:', file.name);
            formData.append(isMultiple ? fieldName + '[]' : fieldName, file);
        });

        // Add all other form fields (folder_id, visibility, description, etc.)
        $form.find('input, select, textarea').not('[type="file"]').not('[name="_token"]').each(function() {
            var $el = $(this);
            var name = $el.attr('name');
            var value = $el.val();
            if (name && value) {
                console.log('[Dropzone] Adding form field:', name, '=', value);
                formData.append(name, value);
            }
        });

        // Add CSRF token
        var csrfToken = $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content');
        if (csrfToken) {
            formData.append('_token', csrfToken);
        } else {
            console.error('[Dropzone] CSRF token not found!');
            showDropzoneError('Security token missing. Please refresh the page.');
            return false;
        }

        var $uploadBtn = $('#moduleUploadBtn');
        var originalText = $uploadBtn.html();

        // Disable button and show loading
        $uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Uploading...');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: 120000, // 2 minute timeout for large files
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = (evt.loaded / evt.total) * 100;
                        console.log('[Dropzone] Upload progress: ' + percentComplete.toFixed(1) + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                console.log('[Dropzone] Upload success:', response);

                // ✅ FIX: Handle JSON response properly
                if (response.success) {
                    // Show success message briefly
                    $uploadBtn.html('<i class="fas fa-check me-2"></i>Success!');

                    // Redirect or reload
                    setTimeout(function() {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 500);
                } else {
                    // Server returned success:false
                    $uploadBtn.prop('disabled', false).html(originalText);
                    showDropzoneError(response.message || 'Upload failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('[Dropzone] Upload error:', status, error);
                console.log('[Dropzone] Response status:', xhr.status);
                console.log('[Dropzone] Response text:', xhr.responseText);

                $uploadBtn.prop('disabled', false).html(originalText);

                var message = 'Upload failed! ';

                // ✅ Show raw response for debugging
                if (xhr.responseText) {
                    try {
                        var json = JSON.parse(xhr.responseText);
                        console.log('[Dropzone] Parsed JSON:', json);
                        if (json.message) {
                            message = 'Error: ' + json.message;
                        }
                    } catch(e) {
                        message = 'Error: ' + xhr.responseText.substring(0, 100);
                    }
                }

                // ✅ FIX: Better error message handling
                if (xhr.status === 422) {
                    // Validation error
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var firstError = Object.values(errors)[0];
                        message = Array.isArray(firstError) ? firstError[0] : firstError;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else {
                        message += 'Validation failed.';
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 413) {
                    message = 'File too large for server.';
                } else if (xhr.status === 500) {
                    message += 'Server error. Check logs.';
                } else if (xhr.status === 0) {
                    message += 'Network error. Check connection.';
                } else {
                    message += error || 'Unknown error.';
                }

                showDropzoneError(message);
            }
        });

        return false;
    });


/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       UNIFIED VIEW TOGGLE (Users & Roles & Future Pages)
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    
    // Global state for view toggle
    var viewToggleState = {
        container: null,
        storageKey: null,
        buttons: null
    };
    
    /**
     * Global switchViewToggle function - available immediately
     * Can be called from inline onclick handlers
     */
    window.switchViewToggle = function(view) {
        console.log('[UnifiedViewToggle] switchViewToggle called with:', view);
        
        // If not initialized yet, try to initialize
        if (!viewToggleState.container) {
            console.log('[UnifiedViewToggle] Not initialized, initializing now...');
            initUnifiedViewToggle();
        }
        
        if (!viewToggleState.container) {
            console.error('[UnifiedViewToggle] No container found!');
            return;
        }
        
        console.log('[UnifiedViewToggle] Switching to view:', view);
        
        // Update button states
        if (viewToggleState.buttons) {
            viewToggleState.buttons.removeClass('active');
            viewToggleState.buttons.filter('[data-view="' + view + '"]').addClass('active');
        } else {
            $('.view-toggle').removeClass('active');
            $('.view-toggle[data-view="' + view + '"]').addClass('active');
        }
        
        // Update container class
        viewToggleState.container.removeClass('list-view');
        if (view === 'list') {
            viewToggleState.container.addClass('list-view');
        }
        
        // Save preference
        if (viewToggleState.storageKey) {
            localStorage.setItem(viewToggleState.storageKey, view);
        }
        
        console.log('[UnifiedViewToggle] View switched successfully');
    };
    
    /**
     * Initialize the unified view toggle system
     * Detects page type and sets up event handlers
     */
    function initUnifiedViewToggle() {
        var $viewToggleButtons = $('.view-toggle');
        
        if (!$viewToggleButtons.length) {
            console.log('[UnifiedViewToggle] No toggle buttons found');
            return;
        }
        
        // Detect which page we're on by checking for specific containers
        var $container = null;
        var storageKey = null;
        var defaultView = 'tile';
        
        if ($('#umUserGrid').length) {
            $container = $('#umUserGrid');
            storageKey = 'umViewMode';
            defaultView = 'tile';
            console.log('[UnifiedViewToggle] Detected Users page');
        } else if ($('#rolesViewContainer').length) {
            $container = $('#rolesViewContainer');
            storageKey = 'rolesViewMode';
            defaultView = 'tile';
            console.log('[UnifiedViewToggle] Detected Roles page');
        }
        
        if (!$container || !storageKey) {
            console.log('[UnifiedViewToggle] No recognized container found');
            return;
        }
        
        // Store in global state
        viewToggleState.container = $container;
        viewToggleState.storageKey = storageKey;
        viewToggleState.buttons = $viewToggleButtons;
        
        console.log('[UnifiedViewToggle] Initialized for container:', $container.attr('id'));
        
        // Load saved preference
        var savedView = localStorage.getItem(storageKey) || defaultView;
        console.log('[UnifiedViewToggle] Loaded saved view:', savedView);
        
        // Apply initial view
        window.switchViewToggle(savedView);
        
        // Add jQuery click handlers (in addition to inline onclick)
        $viewToggleButtons.off('click.unifiedView');
        $viewToggleButtons.on('click.unifiedView', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var view = $(this).data('view');
            console.log('[UnifiedViewToggle] Button clicked:', view);
            window.switchViewToggle(view);
        });
        
        console.log('[UnifiedViewToggle] Initialization complete');
    }
    
    // Initialize on DOM ready
    $(document).ready(function() {
        console.log('[UnifiedViewToggle] DOM ready, initializing...');
        initUnifiedViewToggle();
    });
    
    // Re-initialize after SPA navigation
    $(document).on('spa:loaded', function() {
        console.log('[UnifiedViewToggle] SPA loaded, re-initializing...');
        initUnifiedViewToggle();
    });
    
    /* ══════════════════════════════════════════════════════════════════════
       DASHBOARD BUILDER - Drag & Drop Widget System
       ══════════════════════════════════════════════════════════════════════ */
    
    var dashboardBuilder = {
        grid: null,
        widgetCounter: 0,
        
        // Widget Templates
        widgetTemplates: {
            total_users: {
                title: 'Total Users',
                icon: 'fa-users',
                color: '#007AFF',
                value: '0',
                defaultSize: { w: 3, h: 2 }
            },
            active_users: {
                title: 'Active Users',
                icon: 'fa-user-check',
                color: '#34C759',
                value: '0',
                defaultSize: { w: 3, h: 2 }
            },
            total_roles: {
                title: 'Total Roles',
                icon: 'fa-shield-alt',
                color: '#5AC8FA',
                value: '0',
                defaultSize: { w: 3, h: 2 }
            },
            total_departments: {
                title: 'Departments',
                icon: 'fa-sitemap',
                color: '#FF9500',
                value: '0',
                defaultSize: { w: 3, h: 2 }
            },
            total_permissions: {
                title: 'Permissions',
                icon: 'fa-lock',
                color: '#FF3B30',
                value: '0',
                defaultSize: { w: 3, h: 2 }
            },
            recent_logins: {
                title: 'Recent Logins',
                icon: 'fa-sign-in-alt',
                color: '#5856D6',
                value: 'List',
                defaultSize: { w: 6, h: 4 }
            },
            activity_log: {
                title: 'Activity Log',
                icon: 'fa-history',
                color: '#007AFF',
                value: 'Timeline',
                defaultSize: { w: 6, h: 4 }
            },
            pending_requests: {
                title: 'Pending Requests',
                icon: 'fa-bell',
                color: '#FF9500',
                value: '0',
                defaultSize: { w: 3, h: 2 }
            },
            user_growth_chart: {
                title: 'User Growth',
                icon: 'fa-chart-line',
                color: '#34C759',
                value: 'Chart',
                defaultSize: { w: 6, h: 4 }
            },
            role_distribution: {
                title: 'Role Distribution',
                icon: 'fa-chart-pie',
                color: '#007AFF',
                value: 'Chart',
                defaultSize: { w: 6, h: 4 }
            },
            department_chart: {
                title: 'Department Overview',
                icon: 'fa-chart-bar',
                color: '#5AC8FA',
                value: 'Chart',
                defaultSize: { w: 6, h: 4 }
            },
            system_health: {
                title: 'System Health',
                icon: 'fa-heartbeat',
                color: '#FF3B30',
                value: 'Healthy',
                defaultSize: { w: 3, h: 2 }
            },
            storage_usage: {
                title: 'Storage Usage',
                icon: 'fa-hdd',
                color: '#FF9500',
                value: '45%',
                defaultSize: { w: 3, h: 2 }
            },
            last_backup: {
                title: 'Last Backup',
                icon: 'fa-database',
                color: '#5AC8FA',
                value: 'Today',
                defaultSize: { w: 3, h: 2 }
            }
        },
        
        init: function() {
            var self = this;
            
            // Check if we're on the dashboard builder page
            if (!$('#dashboardGrid').length) {
                console.log('[DashboardBuilder] Not on dashboard page, skipping');
                return;
            }

            // Check if already initialized
            if (this.grid) {
                console.log('[DashboardBuilder] Already initialized, skipping');
                return;
            }
            
            console.log('[DashboardBuilder] Initializing...');
            
            // Wait for GridStack to be available
            if (typeof GridStack === 'undefined') {
                console.error('[DashboardBuilder] ERROR: GridStack library not loaded!');
                Swal.fire('Error', 'GridStack library failed to load. Please refresh the page.', 'error');
                return;
            }
            
            try {
                // Initialize GridStack
                this.grid = GridStack.init({
                    column: 12,
                    cellHeight: 80,
                    margin: 10,
                    float: true,
                    resizable: { handles: 'e, se, s, sw, w' },
                    draggable: { handle: '.widget-header' }
                }, '#dashboardGrid');
                
                console.log('[DashboardBuilder] GridStack initialized successfully');
                
                // Load saved layout or default
                this.loadDashboardLayout();
                
                // Setup drag & drop from widget library
                this.setupWidgetLibrary();
                
                // Setup template buttons
                this.setupTemplates();
                
                // Setup control buttons
                this.setupControls();
                
                // Auto-save on change
                this.grid.on('change', function() {
                    self.updateWidgetCount();
                    self.saveDashboardLayout();
                });
                
                console.log('[DashboardBuilder] Initialization complete ✓');
                } catch (error) {
                    console.error('[DashboardBuilder] Initialization failed:', error);
                    Swal.fire('Error', 'Dashboard Builder initialization failed. Check console for details.', 'error');
                }
        },
        
        setupWidgetLibrary: function() {
            var self = this;
            var $libraryItems = $('.widget-library-item');
            
            console.log('[DashboardBuilder] Setting up', $libraryItems.length, 'widget library items');
            
            if ($libraryItems.length === 0) {
                console.warn('[DashboardBuilder] No widget library items found!');
                return;
            }
            
            // Setup each library item for dragging
            $libraryItems.each(function() {
                var $item = $(this);
                var widgetType = $item.data('widget-type');
                
                // Make draggable using HTML5 drag API
                this.draggable = true;
                
                $item.on('dragstart', function(e) {
                    console.log('[DashboardBuilder] Drag started:', widgetType);
                    e.originalEvent.dataTransfer.setData('widgetType', widgetType);
                    e.originalEvent.dataTransfer.effectAllowed = 'copy';
                    $(this).addClass('dragging');
                });
                
                $item.on('dragend', function(e) {
                    $(this).removeClass('dragging');
                });
            });
            
            // Setup drop zone on grid
            var $gridElement = $('#dashboardGrid');
            
            $gridElement.on('dragover', function(e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'copy';
            });
            
            $gridElement.on('drop', function(e) {
                e.preventDefault();
                var widgetType = e.originalEvent.dataTransfer.getData('widgetType');
                console.log('[DashboardBuilder] Widget dropped:', widgetType);
                
                if (widgetType) {
                    self.addWidget(widgetType);
                }
            });
            
            console.log('[DashboardBuilder] Widget library setup complete');
        },
        
        addWidget: function(widgetType) {
            var template = this.widgetTemplates[widgetType];
            if (!template) {
                console.error('[DashboardBuilder] Unknown widget type:', widgetType);
                return;
            }
            
            var widgetId = 'widget_' + Date.now() + '_' + (++this.widgetCounter);
            var widgetHTML = this.createWidgetHTML(widgetId, widgetType, template);
            
            this.grid.addWidget(widgetHTML, {
                w: template.defaultSize.w,
                h: template.defaultSize.h
            });
            
            console.log('[DashboardBuilder] Widget added:', widgetType, 'ID:', widgetId);
            this.updateWidgetCount();
        },
        
        createWidgetHTML: function(widgetId, widgetType, template) {
            return `
                <div class="grid-stack-item" data-widget-type="${widgetType}" data-widget-id="${widgetId}">
                    <div class="grid-stack-item-content">
                        <div class="widget-card">
                            <div class="widget-header">
                                <span class="widget-title">
                                    <i class="fas ${template.icon}"></i> ${template.title}
                                </span>
                                <div class="widget-actions">
                                    <button class="widget-action-btn" onclick="dashboardBuilder.removeWidget('${widgetId}')" title="Remove Widget">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="widget-content">
                                <div class="widget-value" style="color: ${template.color}">
                                    ${template.value}
                                </div>
                                <div class="widget-label">${template.title}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },
        
        removeWidget: function(widgetId) {
            var self = this;
            console.log('[DashboardBuilder] Removing widget:', widgetId);
            
            var elements = this.grid.engine.nodes.filter(function(n) {
                return n.el.dataset.widgetId === widgetId;
            });
            
            if (elements.length > 0) {
                this.grid.removeWidget(elements[0].el);
                this.updateWidgetCount();
                console.log('[DashboardBuilder] Widget removed successfully');
            }
        },
        
        setupTemplates: function() {
            var self = this;
            
            $('.template-btn').on('click', function() {
                var template = $(this).data('template');
                console.log('[DashboardBuilder] Applying template:', template);
                self.applyTemplate(template);
            });
        },
        
        applyTemplate: function(templateName) {
            var self = this;
            
            console.log('[DashboardBuilder] Clearing current layout');
            this.grid.removeAll();
            
            console.log('[DashboardBuilder] Applying template:', templateName);
            
            switch(templateName) {
                case 'default':
                    self.addWidget('total_users');
                    self.addWidget('active_users');
                    self.addWidget('total_roles');
                    self.addWidget('total_departments');
                    break;
                case 'single':
                    self.addWidget('total_users');
                    self.addWidget('recent_logins');
                    self.addWidget('activity_log');
                    break;
                case 'dual':
                    self.addWidget('total_users');
                    self.addWidget('active_users');
                    self.addWidget('total_roles');
                    self.addWidget('total_departments');
                    self.addWidget('recent_logins');
                    self.addWidget('activity_log');
                    break;
                case 'triple':
                    self.addWidget('total_users');
                    self.addWidget('active_users');
                    self.addWidget('total_roles');
                    self.addWidget('total_departments');
                    self.addWidget('total_permissions');
                    self.addWidget('pending_requests');
                    self.addWidget('system_health');
                    self.addWidget('storage_usage');
                    break;
            }
            
            $('#layoutType').text(templateName.charAt(0).toUpperCase() + templateName.slice(1));
        },
        
        setupControls: function() {
            var self = this;
            
            // Grid toggle
            $('#enableGridLines').on('change', function() {
                $('#dashboardGrid').toggleClass('no-grid', !this.checked);
                console.log('[DashboardBuilder] Grid lines:', this.checked ? 'ON' : 'OFF');
            });
            
            // Save button
            $('#saveLayoutBtn, #dashboardSaveBtn').on('click', function() {
                console.log('[DashboardBuilder] Manual save triggered');
                self.saveDashboardLayout(true);
            });
            
            // Reset button
            $('#dashboardResetBtn').on('click', function() {
                Swal.fire({
                    title: 'Reset Dashboard?',
                    text: 'This will remove all widgets and restore default layout.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reset',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        console.log('[DashboardBuilder] Resetting to default');
                        self.grid.removeAll();
                        self.applyTemplate('default');
                    }
                });
            });
        },
        
        saveDashboardLayout: function(manual) {
            var self = this;
            var layout = [];
            
            this.grid.engine.nodes.forEach(function(node) {
                layout.push({
                    widgetId: node.el.dataset.widgetId,
                    widgetType: node.el.dataset.widgetType,
                    x: node.x,
                    y: node.y,
                    w: node.w,
                    h: node.h
                });
            });
            
            console.log('[DashboardBuilder] Saving layout with', layout.length, 'widgets');
            
            // Get CSRF token
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            if (!csrfToken) {
                console.error('[DashboardBuilder] CSRF token not found in page!');
                return;
            }
            
            $.ajax({
                url: '/dashboard/layout/save',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                contentType: 'application/json',
                data: JSON.stringify({ 
                    layout: layout,
                    manual: manual || false
                }),
                success: function(data) {
                    console.log('[DashboardBuilder] Layout saved successfully');
                    if (manual) {
                        Swal.fire('Success', 'Dashboard layout saved successfully!', 'success');
                    }
                    $('#lastSaved').text(new Date().toLocaleTimeString());
                },
                error: function(xhr, status, error) {
                    console.error('[DashboardBuilder] Save error:', xhr.status, error);
                    if (manual) {
                        Swal.fire('Error', 'Error saving dashboard layout: ' + (xhr.responseJSON?.message || error), 'error');
                    }
                }
            });
        },
        
        loadDashboardLayout: function() {
            var self = this;
            
            console.log('[DashboardBuilder] Loading saved layout...');
            
            $.ajax({
                url: '/dashboard/layout/get',
                method: 'GET',
                success: function(data) {
                    console.log('[DashboardBuilder] Layout loaded:', data);
                    
                    if (data.layout && data.layout.length > 0) {
                        console.log('[DashboardBuilder] Restoring', data.layout.length, 'widgets');
                        // Load saved layout
                        data.layout.forEach(function(item) {
                            var template = self.widgetTemplates[item.widgetType];
                            if (template) {
                                var widgetHTML = self.createWidgetHTML(item.widgetId, item.widgetType, template);
                                self.grid.addWidget(widgetHTML, {
                                    x: item.x,
                                    y: item.y,
                                    w: item.w,
                                    h: item.h
                                });
                            }
                        });
                    } else {
                        console.log('[DashboardBuilder] No saved layout, applying default template');
                        self.applyTemplate('default');
                    }
                    
                    self.updateWidgetCount();
                },
                error: function(xhr, status, error) {
                    console.error('[DashboardBuilder] Load error:', xhr.status, error);
                    console.log('[DashboardBuilder] Applying default template');
                    self.applyTemplate('default');
                }
            });
        },
        
        updateWidgetCount: function() {
            var count = this.grid.engine.nodes.length;
            $('#widgetCount').text(count);
            console.log('[DashboardBuilder] Widget count updated:', count);
        }
    };
    
    // Make dashboardBuilder globally accessible
    window.dashboardBuilder = dashboardBuilder;
    
    // Initialize dashboard builder
    $(document).ready(function() {
        console.log('[DashboardBuilder] DOM ready');
        // Small delay to ensure GridStack is loaded
        setTimeout(function() {
            dashboardBuilder.init();
        }, 100);
    });
    
    // Re-initialize after SPA navigation
    $(document).on('spa:loaded', function() {
        console.log('[DashboardBuilder] SPA loaded, re-initializing');
        setTimeout(function() {
            dashboardBuilder.init();
        }, 100);
    });


        // Home Dashboard Widget - Read-Only Display Component
        // Compatible with GridStack.js

        (function() {
            'use strict';
            
            let grid = null;
            let refreshInterval = null;
            let savedLayout = [];
            
            // Widget Templates Configuration
            const widgetTemplates = {
                total_users: {
                    title: 'Total Users',
                    icon: 'fa-users',
                    color: '#007AFF',
                    dataKey: 'total_users',
                    defaultValue: 0
                },
                active_users: {
                    title: 'Active Users',
                    icon: 'fa-user-check',
                    color: '#34C759',
                    dataKey: 'active_users',
                    defaultValue: 0
                },
                total_roles: {
                    title: 'Total Roles',
                    icon: 'fa-shield-alt',
                    color: '#5AC8FA',
                    dataKey: 'total_roles',
                    defaultValue: 0
                },
                total_departments: {
                    title: 'Departments',
                    icon: 'fa-sitemap',
                    color: '#FF9500',
                    dataKey: 'total_departments',
                    defaultValue: 0
                },
                total_permissions: {
                    title: 'Permissions',
                    icon: 'fa-lock',
                    color: '#FF3B30',
                    dataKey: 'total_permissions',
                    defaultValue: 0
                },
                pending_requests: {
                    title: 'Pending Requests',
                    icon: 'fa-bell',
                    color: '#FF9500',
                    dataKey: 'pending_requests',
                    defaultValue: 0
                },
                system_health: {
                    title: 'System Health',
                    icon: 'fa-heartbeat',
                    color: '#34C759',
                    dataKey: 'system_health',
                    defaultValue: 'N/A'
                },
                storage_usage: {
                    title: 'Storage Usage',
                    icon: 'fa-hdd',
                    color: '#FF9500',
                    dataKey: 'storage_usage',
                    defaultValue: 'N/A'
                },
                last_backup: {
                    title: 'Last Backup',
                    icon: 'fa-database',
                    color: '#5AC8FA',
                    dataKey: 'last_backup',
                    defaultValue: 'Never'
                },
                recent_logins: {
                    title: 'Recent Activity',
                    icon: 'fa-sign-in-alt',
                    color: '#5856D6',
                    dataKey: null,
                    defaultValue: 'View Logs'
                },
                activity_log: {
                    title: 'Activity Timeline',
                    icon: 'fa-history',
                    color: '#007AFF',
                    dataKey: null,
                    defaultValue: 'Recent Events'
                },
                user_growth_chart: {
                    title: 'User Growth',
                    icon: 'fa-chart-line',
                    color: '#34C759',
                    dataKey: null,
                    defaultValue: 'Trending'
                },
                role_distribution: {
                    title: 'Role Distribution',
                    icon: 'fa-chart-pie',
                    color: '#007AFF',
                    dataKey: null,
                    defaultValue: 'Stats'
                },
                department_chart: {
                    title: 'Department Overview',
                    icon: 'fa-chart-bar',
                    color: '#5AC8FA',
                    dataKey: null,
                    defaultValue: 'Overview'
                }
            };
            
            // Configuration
            const config = {
                gridSelector: '#homeDashboardGrid',
                emptySelector: '#emptyDashboard',
                countSelector: '#widgetCountDisplay',
                refreshSelector: '#lastRefresh',
                refreshUrl: '/dashboard/stats',
                refreshInterval: 60000, // 60 seconds
                column: 12,
                cellHeight: 80,
                margin: 10
            };
            
            /**
             * Initialize the Home Dashboard
             * @param {Array} layout - Dashboard layout configuration from backend
             * @param {Object} initialStats - Initial statistics data
             * @param {Object} options - Optional configuration overrides
             */
            function init(layout, initialStats, options) {
                // Merge options
                if (options) {
                    Object.assign(config, options);
                }
                
                savedLayout = layout || [];
                
                console.log('[HomeDashboard] Loading dashboard with', savedLayout.length, 'widgets');
                
                if (typeof GridStack === 'undefined') {
                    console.error('[HomeDashboard] GridStack library not loaded');
                    showEmptyState();
                    return;
                }
                
                // Initialize GridStack in STATIC mode (read-only)
                grid = GridStack.init({
                    column: config.column,
                    cellHeight: config.cellHeight,
                    margin: config.margin,
                    float: false,
                    disableResize: true,
                    disableDrag: true,
                    staticGrid: true
                }, config.gridSelector);
                
                if (savedLayout.length === 0) {
                    showEmptyState();
                } else {
                    loadDashboard(initialStats);
                    startAutoRefresh();
                }
                
                console.log('[HomeDashboard] Dashboard initialized');
            }
            
            /**
             * Load Dashboard Layout
             * @param {Object} stats - Initial statistics data
             */
            function loadDashboard(stats) {
                if (!grid) return;
                
                const $grid = $(config.gridSelector);
                const $empty = $(config.emptySelector);
                
                $empty.hide();
                $grid.show();
                
                savedLayout.forEach(function(widget) {
                    const template = widgetTemplates[widget.widgetType];
                    if (template) {
                        const value = getWidgetValue(template, stats);
                        const widgetHTML = createWidgetHTML(widget.widgetId, widget.widgetType, template, value);
                        grid.addWidget(widgetHTML, {
                            x: widget.x,
                            y: widget.y,
                            w: widget.w,
                            h: widget.h
                        });
                    }
                });
                
                updateWidgetCount();
                console.log('[HomeDashboard] Dashboard loaded successfully');
            }
            
            /**
             * Get widget display value from stats or default
             * @param {Object} template - Widget template
             * @param {Object} stats - Statistics data
             * @returns {string|number} Display value
             */
            function getWidgetValue(template, stats) {
                if (!stats || !template.dataKey) {
                    return template.defaultValue;
                }
                return stats[template.dataKey] !== undefined ? stats[template.dataKey] : template.defaultValue;
            }
            
            /**
             * Create Widget HTML
             * @param {string} widgetId - Widget identifier
             * @param {string} widgetType - Widget type key
             * @param {Object} template - Widget template configuration
             * @param {string|number} value - Display value
             * @returns {string} HTML string
             */
            function createWidgetHTML(widgetId, widgetType, template, value) {
                return `
                    <div class="grid-stack-item" data-widget-type="${widgetType}" data-widget-id="${widgetId}">
                        <div class="grid-stack-item-content">
                            <div class="widget-card">
                                <div class="widget-header">
                                    <span class="widget-title">
                                        <i class="fas ${template.icon}"></i>
                                        ${template.title}
                                    </span>
                                </div>
                                <div class="widget-content">
                                    <div class="widget-value" style="color: ${template.color}">
                                        ${value}
                                    </div>
                                    <div class="widget-label">${template.title}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            /**
             * Show Empty State when no widgets configured
             */
            function showEmptyState() {
                $(config.gridSelector).hide();
                $(config.emptySelector).show();
                $(config.countSelector).text('0');
                console.log('[HomeDashboard] No widgets configured - showing empty state');
            }
            
            /**
             * Update Widget Count Display
             */
            function updateWidgetCount() {
                const count = grid ? grid.engine.nodes.length : 0;
                $(config.countSelector).text(count);
            }
            
            /**
             * Manual Refresh Trigger
             */
            function refreshDashboard() {
                console.log('[HomeDashboard] Manual refresh triggered');
                refreshWidgetData(true);
            }
            
            /**
             * Refresh Widget Data from Backend
             * @param {boolean} showIndicator - Show loading indicator
             */
            function refreshWidgetData(showIndicator = false) {
                if (savedLayout.length === 0) return;
                
                console.log('[HomeDashboard] Refreshing widget data...');
                
                if (showIndicator) {
                    $('.widget-card').addClass('widget-refreshing');
                }
                
                $.ajax({
                    url: config.refreshUrl,
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.stats) {
                            updateWidgetValues(response.stats);
                            $(config.refreshSelector).text(new Date().toLocaleTimeString());
                            console.log('[HomeDashboard] Data refreshed successfully');
                            
                            if (showIndicator) {
                                setTimeout(function() {
                                    $('.widget-card').removeClass('widget-refreshing');
                                }, 500);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('[HomeDashboard] Refresh failed:', error);
                        if (showIndicator) {
                            $('.widget-card').removeClass('widget-refreshing');
                        }
                    }
                });
            }
            
            /**
             * Update Widget Values with Fresh Data
             * @param {Object} stats - Statistics data from backend
             */
            function updateWidgetValues(stats) {
                $('.grid-stack-item').each(function() {
                    const $widget = $(this);
                    const widgetType = $widget.data('widget-type');
                    const template = widgetTemplates[widgetType];
                    
                    if (!template) return;
                    
                    const $valueEl = $widget.find('.widget-value');
                    const newValue = getWidgetValue(template, stats);
                    
                    // Animate value change
                    $valueEl.fadeOut(200, function() {
                        $(this).text(newValue).fadeIn(200);
                    });
                });
            }
            
            /**
             * Start Auto-Refresh Interval
             */
            function startAutoRefresh() {
                refreshInterval = setInterval(function() {
                    refreshWidgetData(false);
                }, config.refreshInterval);
                
                console.log('[HomeDashboard] Auto-refresh enabled (' + (config.refreshInterval / 1000) + 's interval)');
            }
            
            /**
             * Stop Auto-Refresh
             */
            function stopAutoRefresh() {
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                    refreshInterval = null;
                    console.log('[HomeDashboard] Auto-refresh stopped');
                }
            }
            
            // Stop Auto-Refresh on Page Unload
            $(window).on('beforeunload', function() {
                stopAutoRefresh();
            });
            
            // Public API
            window.HomeDashboard = {
                init: init,
                refresh: refreshDashboard,
                stopAutoRefresh: stopAutoRefresh,
                startAutoRefresh: startAutoRefresh,
                destroy: function() {
                    stopAutoRefresh();
                    if (grid) {
                        grid.destroy();
                        grid = null;
                    }
                }
            };
            
        })();
    //

/* ============================================================================
   MACOS MENU BAR FUNCTIONALITY
   ============================================================================ */
// Live Clock Functionality
function updateClock() {
    var clockEl = document.getElementById('liveClock');
    var dateEl = document.getElementById('liveDate');
    if (!clockEl || !dateEl) return;
    
    var now = new Date();
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    clockEl.textContent = hours + ':' + minutes;
    
    var options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    dateEl.textContent = now.toLocaleDateString('en-US', options);
}

// Initialize clock
updateClock();
setInterval(updateClock, 1000);

// Message click handler (works after SPA navigation)
$(document).on('click', '.message-item', function(e) {
    var unreadBadge = document.getElementById('unreadBadge');
    if (!unreadBadge) return;
    
    var currentCount = parseInt(unreadBadge.textContent) || 0;
    var isUnread = this.querySelector('.font-weight-bold') !== null;
    
    if (isUnread && currentCount > 0) {
        currentCount--;
        if (currentCount === 0) {
            unreadBadge.style.display = 'none';
        } else {
            unreadBadge.textContent = currentCount > 99 ? '99+' : currentCount;
            unreadBadge.classList.add('badge-pulse');
            setTimeout(function() {
                unreadBadge.classList.remove('badge-pulse');
            }, 300);
        }
    }
});

// Auto-refresh unread count every 30 seconds
setInterval(function() {
    var unreadBadge = document.getElementById('unreadBadge');
    if (!unreadBadge) return;
    
    fetch('/messages/unread-count', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        var unreadBadge = document.getElementById('unreadBadge');
        if (!unreadBadge) return;
        if (data.count > 0) {
            unreadBadge.textContent = data.count > 99 ? '99+' : data.count;
            unreadBadge.style.display = '';
        } else {
            unreadBadge.style.display = 'none';
        }
    })
    .catch(function(error) { console.error('Error fetching unread count:', error); });
}, 30000);

// Haptic feedback for header elements
$(document).on('click', '.btn-glass, .nav-link, .dropdown-item', function() {
    if (window.navigator.vibrate) {
        window.navigator.vibrate(10);
    }
});

/* ============================================================================
   PAGE-SPECIFIC HANDLERS (Modules, Roles, Users, etc.)
   Works after SPA navigation via event delegation
   ============================================================================ */

// Modules: Confirm Uninstall
$(document).on('submit', 'form[data-confirm-uninstall]', function(e) {
    e.preventDefault();
    var form = this;
    
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to uninstall this module?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, uninstall it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: function() {
            return new Promise(function(resolve) {
                form.submit();
                resolve();
            });
        }
    });
});

// Roles: View Toggle
$(document).on('click', '[data-view-toggle]', function(e) {
    e.preventDefault();
    var view = this.dataset.view;
    console.log('[Roles] switchViewToggle called:', view);
    
    var grid = document.getElementById('rolesGrid');
    if (!grid) return;
    
    // Update buttons
    var buttons = document.querySelectorAll('#viewToggleButtons .view-toggle');
    buttons.forEach(function(btn) {
        if (btn.dataset.view === view) {
            btn.classList.add('fh-tab--active');
        } else {
            btn.classList.remove('fh-tab--active');
        }
    });
    
    // Update views
    var tiles = grid.querySelectorAll('.fh-role-block');
    tiles.forEach(function(tile) {
        if (view === 'grid') {
            tile.classList.add('tile-view');
            tile.classList.remove('list-view');
        } else {
            tile.classList.add('list-view');
            tile.classList.remove('tile-view');
        }
    });
    
    // Save preference
    localStorage.setItem('roles_view_mode', view);
});

// Initialize page-specific handlers on SPA load
$(document).on('spa:loaded', function() {
    // Roles: Restore view preference
    var savedView = localStorage.getItem('roles_view_mode');
    if (savedView) {
        var toggleBtn = document.querySelector('[data-view-toggle="' + savedView + '"]');
        if (toggleBtn) {
            toggleBtn.click();
        }
    }
});

})(jQuery);