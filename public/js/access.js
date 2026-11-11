/* =========================================================================
   Display & accessibility preferences
   Loaded in <head> without defer on purpose: the stored choices have to be on
   <html> before the first paint, or a person who picked the terminal theme
   would watch the dark blue one flash up first every single navigation.
   The UI wiring waits for the DOM; the applying does not.
   ========================================================================= */
(function () {
    'use strict';

    var KEY = 'brgy:a11y';

    var DEFAULTS = {
        theme: 'default',
        text: 'normal',
        contrast: 'normal',
        motion: 'auto',
        links: 'off',
        targets: 'normal'
    };

    /* Which attribute value each choice writes. Anything listed as null is
       "the default", and the attribute is removed rather than set, so the
       stylesheet's normal rules apply with no !important fights. */
    var MAP = {
        theme: { attr: 'data-theme', off: 'default' },
        text: { attr: 'data-text', off: 'normal' },
        contrast: { attr: 'data-contrast', off: 'normal' },
        motion: { attr: 'data-motion', off: 'auto' },
        links: { attr: 'data-links', off: 'off', write: { on: 'on' } },
        targets: { attr: 'data-targets', off: 'normal' }
    };

    function read() {
        var saved = {};
        try {
            saved = JSON.parse(localStorage.getItem(KEY)) || {};
        } catch (e) {
            saved = {};
        }
        var state = {};
        Object.keys(DEFAULTS).forEach(function (k) {
            state[k] = (typeof saved[k] === 'string' && saved[k] !== '') ? saved[k] : DEFAULTS[k];
        });
        return state;
    }

    function write(state) {
        try {
            localStorage.setItem(KEY, JSON.stringify(state));
        } catch (e) { /* private mode: preferences just won't persist */ }
    }

    function apply(state) {
        var root = document.documentElement;

        Object.keys(MAP).forEach(function (key) {
            var rule = MAP[key];
            var value = state[key];

            if (value === rule.off) {
                root.removeAttribute(rule.attr);
            } else {
                root.setAttribute(rule.attr, rule.write ? (rule.write[value] || value) : value);
            }
        });
    }

    var state = read();
    apply(state);

    /* ---------- UI ---------- */
    function syncPanel(panel) {
        Object.keys(MAP).forEach(function (key) {
            var inputs = panel.querySelectorAll('[data-access-key="' + key + '"]');
            Array.prototype.forEach.call(inputs, function (input) {
                if (input.type === 'checkbox') {
                    input.checked = state[key] === (MAP[key].write ? MAP[key].write.on : 'on');
                } else {
                    input.checked = input.value === state[key];
                }
            });
        });
    }

    function wire(panel) {
        var toggle = panel.querySelector('[data-access-toggle]');
        var box = panel.querySelector('[data-access-panel]');
        if (!toggle || !box) return;

        syncPanel(box);

        toggle.addEventListener('click', function () {
            var open = box.hasAttribute('hidden');
            if (open) {
                box.removeAttribute('hidden');
                toggle.setAttribute('aria-expanded', 'true');
                var first = box.querySelector('input:checked') || box.querySelector('input');
                if (first) first.focus();
            } else {
                box.setAttribute('hidden', '');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        function commit(e) {
            var input = e.target;
            var key = input.getAttribute('data-access-key');
            if (!key) return;

            if (input.type === 'checkbox') {
                state[key] = input.checked ? 'on' : DEFAULTS[key];
            } else {
                state[key] = input.value;
            }

            write(state);
            apply(state);
        }

        box.addEventListener('change', commit);

        var reset = box.querySelector('[data-access-reset]');
        if (reset) {
            reset.addEventListener('click', function () {
                state = Object.assign({}, DEFAULTS);
                write(state);
                apply(state);
                syncPanel(box);
                toggle.focus();
            });
        }

        /* Escape closes and hands focus back to the button, which is what
           keyboard users expect from a control that opened a panel. */
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' || box.hasAttribute('hidden')) return;
            box.setAttribute('hidden', '');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        });

        document.addEventListener('click', function (e) {
            if (box.hasAttribute('hidden') || panel.contains(e.target)) return;
            box.setAttribute('hidden', '');
            toggle.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('[data-access]'), wire);
    });
})();
