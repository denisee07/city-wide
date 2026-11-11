/* =========================================================================
   Barangay Sigla — shared UI behaviour
   ========================================================================= */

(function () {
    'use strict';

    /* ---------- toast notifications (server flash + client) ---------- */
    function ensureToasts() {
        var el = document.getElementById('toasts');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toasts';
            document.body.appendChild(el);
        }
        return el;
    }

    window.toast = function (message, type) {
        type = type || 'info';
        var host = ensureToasts();
        var node = document.createElement('div');
        node.className = 'toast ' + type;
        node.innerHTML =
            '<span class="grow">' + escapeHtml(message) + '</span>' +
            '<button class="close" aria-label="Dismiss">&times;</button>';
        node.querySelector('.close').addEventListener('click', function () { node.remove(); });
        host.appendChild(node);
        setTimeout(function () {
            node.style.transition = 'opacity .3s, transform .3s';
            node.style.opacity = '0';
            node.style.transform = 'translateX(40px)';
            setTimeout(function () { node.remove(); }, 320);
        }, 4600);
    };

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* Render Laravel flash messages passed through the layout. */
    document.addEventListener('DOMContentLoaded', function () {
        var flash = document.querySelectorAll('[data-flash]');
        flash.forEach(function (node) {
            toast(node.getAttribute('data-flash'), node.getAttribute('data-flash-type') || 'info');
            node.remove();
        });

        initSidebar();
        applyGroups();
    });

    /* ---------- sidebar: one control, two shapes ----------
       Above 860px the panel is a column that can be folded away; below it, an
       off-canvas drawer with a backdrop. The same hamburger drives both, and
       the desktop choice is remembered between pages - which matters here
       because every link is a full navigation, so a preference that only
       lives in the DOM would reset on the next click. */
    var DESKTOP = window.matchMedia('(min-width: 861px)');
    var PREF_NAV = 'brgy:nav-collapsed';
    var PREF_GROUPS = 'brgy:nav-groups';

    function store(key, value) {
        try { localStorage.setItem(key, value); } catch (e) { /* private mode */ }
    }

    function read(key, fallback) {
        try {
            var v = localStorage.getItem(key);
            return v === null ? fallback : v;
        } catch (e) { return fallback; }
    }

    function sidebarEl() { return document.querySelector('.sidebar'); }
    function backdropEl() { return document.querySelector('.sidebar-backdrop'); }

    function sidebarOpen() {
        var side = sidebarEl();
        if (!side) return false;
        return DESKTOP.matches
            ? !document.body.classList.contains('nav-collapsed')
            : side.classList.contains('open');
    }

    function setSidebar(open, remember) {
        var side = sidebarEl();
        if (!side) return;

        if (DESKTOP.matches) {
            document.body.classList.toggle('nav-collapsed', !open);
            /* A folded panel must not linger open underneath the drawer once
               the window is dragged back down to phone width. */
            side.classList.remove('open');
            if (backdropEl()) backdropEl().classList.remove('show');
            if (remember !== false) store(PREF_NAV, open ? '0' : '1');
        } else {
            document.body.classList.remove('nav-collapsed');
            side.classList.toggle('open', open);
            if (backdropEl()) backdropEl().classList.toggle('show', open);
        }

        var burger = document.querySelector('[data-sidebar-toggle]');
        if (burger) burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function initSidebar() {
        var side = sidebarEl();
        if (!side) return;

        if (DESKTOP.matches) {
            setSidebar(read(PREF_NAV, '0') !== '1');
        } else {
            setSidebar(false);
        }
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-sidebar-toggle]')) {
            setSidebar(!sidebarOpen());
            return;
        }
        if (e.target.classList && e.target.classList.contains('sidebar-backdrop')) {
            setSidebar(false);
            return;
        }
        /* Picking a destination on a phone should land the user on the page,
           not leave them looking at a drawer covering it. */
        if (!DESKTOP.matches && e.target.closest('.sidebar a')) {
            setSidebar(false);
        }
    });

    /* Resizing across the breakpoint re-applies that shape rather than
       leaving the panel in a state that belongs to the other one. */
    var onShape = function () { initSidebar(); };
    if (DESKTOP.addEventListener) DESKTOP.addEventListener('change', onShape);
    else if (DESKTOP.addListener) DESKTOP.addListener(onShape);

    /* ---------- nav groups ---------- */
    function openGroups() {
        return Array.prototype.slice.call(document.querySelectorAll('.nav-group')).map(function (g) {
            return g.getAttribute('data-nav-group');
        });
    }

    function activeGroup() {
        var link = document.querySelector('.nav-group .nav-item.active');
        var group = link && link.closest('.nav-group');
        return group ? group.getAttribute('data-nav-group') : null;
    }

    function applyGroups() {
        var raw = read(PREF_GROUPS, null);
        var stored = null;

        if (raw !== null) {
            try { stored = JSON.parse(raw) || []; } catch (e) { stored = []; }
        }

        var current = activeGroup();

        document.querySelectorAll('.nav-group').forEach(function (g, i) {
            var id = g.getAttribute('data-nav-group');
            /* Two rules beat everything else: the section holding the page you
               are on is always open, and on a first visit - before anyone has
               clicked a heading - the first section opens too, so the panel
               reads as a menu rather than a row of closed folders. That is
               also what keeps Complaints and the map from looking removed.
               After that, the officer's own choices win. */
            var open = id === current || (stored === null ? i === 0 : stored.indexOf(id) !== -1);
            g.classList.toggle('open', open);
            var toggle = g.querySelector('[data-nav-toggle]');
            if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    function persistGroups() {
        var current = activeGroup();
        var open = Array.prototype.slice.call(document.querySelectorAll('.nav-group.open'))
            .map(function (g) { return g.getAttribute('data-nav-group'); })
            .filter(function (id) { return id !== current; });
        store(PREF_GROUPS, JSON.stringify(open));
    }

    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-nav-toggle]');
        if (!toggle) return;
        var group = toggle.closest('.nav-group');
        if (!group) return;
        var open = !group.classList.contains('open');
        group.classList.toggle('open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        persistGroups();
    });

    /* ---------- modals ---------- */
    document.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-modal-open]');
        if (opener) {
            e.preventDefault();
            var target = document.getElementById(opener.getAttribute('data-modal-open'));
            if (target) {
                target.classList.add('open');
                document.body.style.overflow = 'hidden';
                var first = target.querySelector('input, select, textarea, button');
                if (first) setTimeout(function () { first.focus(); }, 60);
            }
            return;
        }

        if (e.target.closest('[data-modal-close]') ||
            (e.target.classList && e.target.classList.contains('modal-backdrop'))) {
            var open = document.querySelector('.modal.open');
            if (open) {
                open.classList.remove('open');
                document.body.style.overflow = '';
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var open = document.querySelector('.modal.open');
            if (open) {
                open.classList.remove('open');
                document.body.style.overflow = '';
            } else if (!DESKTOP.matches && sidebarOpen()) {
                setSidebar(false);
            }
        }
    });

    /* ---------- destructive form confirmation ---------- */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.hasAttribute('data-confirm')) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        }
    });

    /* ---------- ajax action forms (no page reload) ----------
       Any form marked data-ajax is sent with fetch instead of navigating.
       The server answers with JSON describing the record's new state, and the
       matching [data-status-cell] is rewritten in place - so the official
       keeps their scroll position, active filters and open modal instead of
       being thrown back to the top of a freshly loaded page.

       Progressive enhancement: without the attribute, or without JS, the form
       posts and redirects exactly as it always did. */
    function firstError(errors) {
        for (var key in errors) {
            if (Object.prototype.hasOwnProperty.call(errors, key) && errors[key].length) {
                return errors[key][0];
            }
        }
        return '';
    }

    function applyPatch(patch) {
        if (!patch) return;
        var list = Array.isArray(patch) ? patch : (patch.patches || [patch]);
        list.forEach(function (p) {
            if (!p || !p.selector) return;

            var nodes = document.querySelectorAll(p.selector);

            /* Attribute patch: keeps data-* in step with the record, e.g. the
               button that prefills a modal must not offer yesterday's status. */
            if (p.attrs) {
                Object.keys(p.attrs).forEach(function (name) {
                    nodes.forEach(function (node) { node.setAttribute(name, p.attrs[name]); });
                });
                return;
            }

            if (p.html == null) return;

            /* querySelectorAll, not querySelector: a selector like
               [data-status-cell] often marks several badges on one page and
               every one of them must move together. */
            nodes.forEach(function (node) { node.innerHTML = p.html; });
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches('form[data-ajax]')) return;
        if (e.defaultPrevented) return;   /* a data-confirm the user declined */

        e.preventDefault();

        var button = form.querySelector('[type="submit"]');
        if (button) button.disabled = true;

        var fields = new FormData(form);

        /* POST, never the true verb. PHP only fills $_POST for a POST request,
           so a real PATCH/DELETE with a multipart body drops _token and _method
           on the floor and fails CSRF. Laravel reads the intended verb from
           _method in the body, exactly as a plain form submit would. */
        fetch(form.action, {
            method: 'POST',
            body: fields,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (res) {
                return res.json()
                    .catch(function () { return {}; })
                    .then(function (data) { return { ok: res.ok, data: data }; });
            })
            .then(function (r) {
                if (!r.ok) {
                    var message = (r.data && r.data.message)
                        || (r.data && r.data.errors && firstError(r.data.errors))
                        || 'Nothing was saved.';
                    window.toast(message, 'error');
                    return;
                }

                window.toast(r.data.message || 'Saved.', 'success');
                applyPatch(r.data.patches || r.data.patch);

                /* Close the modal the form lived in, exactly the way the
                   backdrop does, so the page scrolls again. */
                var modal = form.closest('.modal');
                if (modal && modal.classList.contains('open')) {
                    modal.classList.remove('open');
                    document.body.style.overflow = '';
                }
            })
            .catch(function () {
                window.toast('Could not reach the server - nothing was saved.', 'error');
            })
            .then(function () {
                if (button) button.disabled = false;
            });
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-confirm-click]');
        if (btn && !window.confirm(btn.getAttribute('data-confirm-click'))) {
            e.preventDefault();
        }
    });

    /* ---------- client-side table filtering ---------- */
    document.addEventListener('input', function (e) {
        var input = e.target.closest('[data-filter]');
        if (!input) return;

        var scope = document.querySelector(input.getAttribute('data-filter'));
        if (!scope) return;

        var term = input.value.toLowerCase().trim();
        var rows = scope.querySelectorAll('[data-search]');
        var shown = 0;

        rows.forEach(function (row) {
            var hit = row.getAttribute('data-search').toLowerCase().indexOf(term) !== -1;
            row.style.display = hit ? '' : 'none';
            if (hit) shown++;
        });

        var counter = document.querySelector('[data-filter-count]');
        if (counter) counter.textContent = shown;

        var none = scope.querySelector('[data-filter-empty]');
        if (none) none.style.display = shown === 0 ? '' : 'none';
    });

    /* ---------- chip filters (same table, class based) ---------- */
    document.addEventListener('click', function (e) {
        var chip = e.target.closest('[data-chip-filter]');
        if (!chip) return;

        var group = chip.closest('[data-chip-group]');
        if (group) {
            group.querySelectorAll('[data-chip-filter]').forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
        }

        var scope = document.querySelector(chip.getAttribute('data-chip-filter'));
        if (!scope) return;

        var value = chip.getAttribute('data-chip-value') || '';
        scope.querySelectorAll('[data-chip-item]').forEach(function (item) {
            var tags = (item.getAttribute('data-chip-item') || '').split(' ');
            item.style.display = (value === '' || tags.indexOf(value) !== -1) ? '' : 'none';
        });
    });

    /* ---------- copy-to-clipboard ---------- */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-copy]');
        if (!btn) return;
        e.preventDefault();
        var text = btn.getAttribute('data-copy');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () { toast('Copied: ' + text, 'success'); });
        } else {
            toast('Reference: ' + text, 'info');
        }
    });

    /* ---------- in-place list filtering (no reload) ----------
       The list pages are plain GET forms, so pressing Apply used to navigate
       and repaint the whole page - and with every dropdown still on "All...",
       that repaint was identical to what was already on screen, so the button
       looked broken. Forms marked data-filter-ajax are fetched instead and
       only <main class="content"> is swapped: scroll position, any open legend
       and the sidebar all survive.

       The tabs, chips, Reset and pagination links beside the form are
       intercepted too, so every way of narrowing the same list behaves the
       same way. The address bar still updates (pushState), so back and
       forward step through filters.

       Progressive enhancement: without the attribute, or if the fetch fails or
       lands somewhere unexpected - a session that expired mid-filter, say -
       the browser navigates exactly as it always did. */

    function listRegion() {
        return document.querySelector('main.content');
    }

    function renderFlash(scope) {
        scope.querySelectorAll('[data-flash]').forEach(function (node) {
            toast(node.getAttribute('data-flash'), node.getAttribute('data-flash-type') || 'info');
            node.remove();
        });
    }

    /* Read the counter if the page shows one, so the toast can say how much
       matched instead of just announcing that something happened. */
    function resultLabel(doc) {
        var el = doc.querySelector('[data-result-count]');
        if (!el) return '';
        var digits = (el.textContent.match(/[\d,]+/) || [])[0];
        if (!digits) return '';
        var n = parseInt(digits.replace(/,/g, ''), 10);
        if (isNaN(n)) return '';
        return ' \u00b7 ' + n + (n === 1 ? ' result' : ' results');
    }

    /* Only send fields that mean something, so filtering to "Resolved" gives
       ?status=resolved rather than ?q=&status=resolved&priority=&category=. */
    function filterQuery(form) {
        var params = new URLSearchParams();
        Array.prototype.forEach.call(form.elements, function (el) {
            if (!el.name || el.disabled) return;
            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
            if (el.value === '' || el.value === null) return;
            params.append(el.name, el.value);
        });
        return params.toString();
    }

    function loadList(url, options) {
        options = options || {};
        var region = listRegion();
        var button = options.button || null;

        if (button) button.disabled = true;
        if (region) region.classList.add('is-loading');

        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');

                /* The login page has no main.content - that means we were
                   bounced, so let the browser show it properly. */
                var next = doc.querySelector('main.content');
                if (!region || !next) throw new Error('no content region');

                region.innerHTML = next.innerHTML;
                renderFlash(region);

                if (options.push) {
                    /* Mark the entry we are leaving as well, or going back to
                       it would change the URL without changing the list. */
                    if (!history.state || !history.state.listFilter) {
                        history.replaceState({ listFilter: true }, '', location.href);
                    }
                    history.pushState({ listFilter: true }, '', url);
                }

                if (!options.quiet) toast('Filters applied' + resultLabel(doc), 'info');
                return true;
            })
            .catch(function () {
                window.location.href = url;
                return false;
            })
            .then(function (done) {
                if (region) region.classList.remove('is-loading');
                if (button) button.disabled = false;
                return done;
            });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches('form[data-filter-ajax]')) return;
        if (e.defaultPrevented) return;
        e.preventDefault();

        var qs = filterQuery(form);
        loadList(location.pathname + (qs ? '?' + qs : ''), {
            push: true,
            button: form.querySelector('[type="submit"]')
        });
    });

    /* The same list, a different URL: status tabs, chips, Reset, pagination. */
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;  /* keep "open in new tab" */

        var link = e.target.closest('main.content a[href]');
        if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

        var href = link.getAttribute('href');
        if (!href || href.charAt(0) === '#') return;

        var url;
        try { url = new URL(href, location.href); } catch (err) { return; }
        if (url.origin !== location.origin) return;
        if (url.pathname !== location.pathname) return;   /* a different page: leave it alone */

        e.preventDefault();
        loadList(url.pathname + url.search, { push: true });
    });

    /* Back/forward through the filter states we pushed ourselves. */
    window.addEventListener('popstate', function (e) {
        if (!e.state || !e.state.listFilter) return;
        loadList(location.pathname + location.search, { push: false, quiet: true });
    });

    /* ---------- live clock on queue board ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var clocks = document.querySelectorAll('[data-clock]');
        if (!clocks.length) return;
        var tick = function () {
            var now = new Date();
            var txt = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            clocks.forEach(function (c) { c.textContent = txt; });
        };
        tick();
        setInterval(tick, 1000);
    });

    /* ---------- auto-refresh a block from a JSON endpoint ---------- */
    window.pollUrl = function (url, handler, interval) {
        var run = function () {
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) { if (data) handler(data); })
                .catch(function () {});
        };
        run();
        setInterval(run, interval || 5000);
    };

    /* ---------- password strength meter (registration + profile) ----------
       Markup lives in resources/views/partials/password-strength.blade.php and
       carries data-pw / data-confirm / data-match / data-common, so this stays
       in one place instead of being duplicated per form. */
    function initPasswordMeter(meter) {
        var pw = document.getElementById(meter.getAttribute('data-pw'));
        var confirmEl = document.getElementById(meter.getAttribute('data-confirm'));
        var matchBox = document.getElementById(meter.getAttribute('data-match'));
        if (!pw || !pw.form || !confirmEl) return;

        var track = meter.querySelector('.pw-track');
        var label = meter.querySelector('.pw-label');
        var hint = meter.querySelector('.pw-hint');
        var segments = Array.prototype.slice.call(meter.querySelectorAll('.pw-seg'));
        var items = Array.prototype.slice.call(meter.querySelectorAll('.pw-rules li'));
        var optional = meter.getAttribute('data-optional') === '1';

        var common = [];
        try { common = JSON.parse(meter.getAttribute('data-common') || '[]'); } catch (err) { common = []; }

        /* Mirrors PasswordPolicy: same run regex, same five-or-more repeat. */
        var RUN_RE = /(?:0123456789|abcdefghij|qwertyuiop|asdfghjkl|zxcvbnm|abcdefghijkl)/i;
        var RE_LOWER = /\p{Ll}/u;
        var RE_UPPER = /\p{Lu}/u;
        var RE_DIGIT = /\p{N}/u;
        var RE_SYMBOL = /[^\p{L}\p{N}]/u;

        var LABELS = ['', 'Too weak', 'Weak', 'Fair', 'Good', 'Strong'];
        var HINTS = {
            len: 'Use at least 8 characters',
            case: 'Mix upper and lower case letters',
            num: 'Add at least one number',
            sym: 'Add a symbol like #, ! or .',
            orig: 'Avoid common, repeated or keyboard patterns'
        };

        var nameInput = document.querySelector('input[name="name"]');
        var emailInput = document.querySelector('input[name="email"]');
        var staticEmail = meter.getAttribute('data-email') || '';

        function identity() {
            var email = emailInput ? emailInput.value : staticEmail;
            var at = email.indexOf('@');
            return {
                local: at > 0 ? email.slice(0, at).trim().toLowerCase() : '',
                name: nameInput ? nameInput.value.replace(/\s+/g, '').trim().toLowerCase() : ''
            };
        }

        function isOriginal(value, id) {
            if (common.indexOf(value.toLowerCase()) !== -1) return false;
            if (/(.)\1{4,}/.test(value)) return false;
            if (RUN_RE.test(value)) return false;
            if (id.local.length >= 3 && value.toLowerCase().indexOf(id.local) !== -1) return false;
            if (id.name.length >= 4 && value.toLowerCase().indexOf(id.name) !== -1) return false;
            return true;
        }

        function score(value, checks, unmet) {
            var pts = 0;
            if (value.length >= 8) pts += 1;
            if (value.length >= 10) pts += 1;
            if (value.length >= 12) pts += 1;
            if (value.length >= 14) pts += 1;
            if (value.length >= 16) pts += 1;

            var classes = 0;
            if (RE_LOWER.test(value)) { pts++; classes++; }
            if (RE_UPPER.test(value)) { pts++; classes++; }
            if (RE_DIGIT.test(value)) { pts++; classes++; }
            if (RE_SYMBOL.test(value)) { pts++; classes++; }
            if (classes >= 3) pts++;

            var unique = new Set(value).size;
            if (unique >= 8) pts += 1;
            if (value.length >= 8 && unique / value.length <= 0.5) pts -= 1;

            if (/(.)\1{4,}/.test(value)) pts -= 2;
            if (RUN_RE.test(value)) pts -= 2;
            if (!checks.orig) pts -= 3;

            var level = pts <= 2 ? 1 : pts <= 4 ? 2 : pts <= 6 ? 3 : pts <= 8 ? 4 : 5;

            /* Never rate above "Weak" until every requirement is ticked - otherwise
               the bar contradicts the red crosses right underneath it. */
            if (unmet >= 2) return 1;
            if (unmet === 1) return Math.min(level, 2);
            return level;
        }

        function render(level, checks) {
            var i;
            for (i = 0; i <= 5; i++) meter.classList.remove('lv' + i);
            meter.classList.add('lv' + level);

            for (i = 0; i < segments.length; i++) segments[i].classList.toggle('on', i < level);

            if (label) label.textContent = level === 0 ? 'Password strength' : LABELS[level];
            if (track) {
                track.setAttribute('aria-valuenow', level);
                track.setAttribute('aria-valuetext', level === 0 ? 'Empty' : LABELS[level]);
            }
            if (!hint) return;

            if (!pw.value) {
                hint.textContent = meter.getAttribute('data-blank-hint') || 'Start typing to see how strong it is';
                return;
            }

            var firstUnmet = items.filter(function (li) {
                return !checks[li.getAttribute('data-rule')];
            })[0];

            if (firstUnmet) hint.textContent = HINTS[firstUnmet.getAttribute('data-rule')];
            else if (level < 5) hint.textContent = 'All requirements met - longer is still stronger';
            else hint.textContent = 'Excellent - safe to use';
        }

        function evaluate() {
            var value = pw.value;
            var id = identity();

            var checks = {
                len: value.length >= 8 && value.length <= 64,
                case: RE_LOWER.test(value) && RE_UPPER.test(value),
                num: RE_DIGIT.test(value),
                sym: RE_SYMBOL.test(value),
                orig: value.length > 0 && isOriginal(value, id)
            };

            var unmet = 0;
            items.forEach(function (li) {
                var ok = !!checks[li.getAttribute('data-rule')];
                li.classList.toggle('ok', ok);
                if (!ok) unmet++;
            });

            render(value ? score(value, checks, unmet) : 0, checks);
        }

        function renderMatch() {
            if (!matchBox) return;
            if (!confirmEl.value) {
                matchBox.hidden = true;
                matchBox.classList.remove('yes', 'no');
                matchBox.textContent = '';
                return;
            }
            var ok = confirmEl.value === pw.value;
            matchBox.hidden = false;
            matchBox.classList.toggle('yes', ok);
            matchBox.classList.toggle('no', !ok);
            matchBox.textContent = ok ? 'Passwords match.' : 'Passwords do not match yet.';
        }

        pw.addEventListener('input', function () { evaluate(); renderMatch(); });
        confirmEl.addEventListener('input', renderMatch);

        /* Re-check when the resident edits the details a password must not contain. */
        if (nameInput) nameInput.addEventListener('input', evaluate);
        if (emailInput) emailInput.addEventListener('input', evaluate);

        /* Only block what the server would reject anyway. */
        pw.form.addEventListener('submit', function (e) {
            if (optional && !pw.value && !confirmEl.value) return;

            var failed = items.filter(function (li) { return !li.classList.contains('ok'); })[0];
            if (failed) {
                e.preventDefault();
                pw.focus();
                evaluate();
                return;
            }
            if (confirmEl.value !== pw.value) {
                e.preventDefault();
                (confirmEl.value ? confirmEl : pw).focus();
                renderMatch();
            }
        });

        evaluate();
        renderMatch();
    }

    function bootPasswordMeters() {
        document.querySelectorAll('.js-pw-meter').forEach(initPasswordMeter);
    }

    /* ---------- password show/hide toggle ----------
       Markup: resources/views/partials/password-toggle.blade.php
       Every [data-pw-toggle="<input id>"] flips that input between
       type=password and type=text. The caret is preserved across the switch,
       and the input is re-masked on submit so plaintext never lingers in the
       DOM once the form has been sent. */
    function initPasswordToggle(btn) {
        var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
        if (!input) return;

        var what = btn.getAttribute('data-what') || 'password';
        var showLabel = 'Show ' + what;
        var hideLabel = 'Hide ' + what;

        /* Both eye icons ship in the partial; CSS swaps them off the is-on class. */
        function paint(shown) {
            btn.setAttribute('aria-pressed', shown ? 'true' : 'false');
            btn.setAttribute('aria-label', shown ? hideLabel : showLabel);
            btn.setAttribute('title', shown ? hideLabel : showLabel);
            btn.classList.toggle('is-on', shown);
        }

        btn.addEventListener('click', function () {
            var shown = input.type === 'text';
            var start = input.selectionStart;
            var end = input.selectionEnd;
            var hadFocus = document.activeElement === input;

            input.type = shown ? 'password' : 'text';
            paint(!shown);

            if (hadFocus && start !== null && start !== undefined) {
                try { input.setSelectionRange(start, end); } catch (e) { /* type=text only */ }
            }
        });

        /* Never leave the value visible after submit. */
        var form = input.form;
        if (form) {
            form.addEventListener('submit', function () {
                if (input.type === 'text') {
                    input.type = 'password';
                    paint(false);
                }
            });
        }
    }

    function bootPasswordToggles() {
        document.querySelectorAll('[data-pw-toggle]').forEach(initPasswordToggle);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            bootPasswordMeters();
            bootPasswordToggles();
        });
    } else {
        bootPasswordMeters();
        bootPasswordToggles();
    }
})();
