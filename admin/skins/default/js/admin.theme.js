/**
 * The admin in light, dark, or whichever the device is in (#4765).
 *
 * The dark theme is written once, in layout.css and wherever else a stylesheet says
 * `@media (prefers-color-scheme: dark)`, so it follows the device by itself. A choice of
 * Light or Dark overrides the device by rewriting the condition of those rules in place:
 * always true or always false. Auto puts each one back as it was written. Nothing is
 * copied, and an extension's own dark rules follow the choice as well.
 *
 * This file is loaded in the head, after the stylesheets and before anything is drawn, so
 * a page never shows in one theme and then changes to the other.
 *
 *   ccTheme.mode         'light', 'dark' or 'auto' (the choice, kept in this browser)
 *   ccTheme.dark()       whether the page is dark now
 *   ccTheme.set(mode)    make a choice
 *   ccTheme.query        stands in for matchMedia('(prefers-color-scheme: dark)') where a
 *                        script decides how to draw: .matches, and 'change' when it changes
 *
 * `cc:theme` is sent to the document when the theme changes, and <html> carries
 * data-theme ("light" or "dark") and data-theme-choice ("light", "dark" or "auto").
 */
(function () {
    var KEY = 'cc_admin_theme';
    var FEATURE = /\(\s*prefers-color-scheme\s*:\s*(dark|light)\s*\)/gi;
    var system = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    var found = [];       // each: the rule or stylesheet, and its condition as written
    var listeners = [];

    function stored() {
        try {
            var value = window.localStorage.getItem(KEY);
            return (value === 'light' || value === 'dark') ? value : 'auto';
        } catch (e) {
            return 'auto';
        }
    }
    var mode = stored();

    function isDark() {
        return mode === 'dark' || (mode === 'auto' && !!(system && system.matches));
    }

    function known(owner) {
        for (var i = 0; i < found.length; i++) {
            if (found[i].owner === owner) return true;
        }
        return false;
    }
    function note(owner) {
        var text;
        try {
            text = owner.media ? owner.media.mediaText : '';
        } catch (e) {
            return;
        }
        FEATURE.lastIndex = 0;
        if (text && FEATURE.test(text) && !known(owner)) found.push({ owner: owner, text: text });
    }
    function walk(rules) {
        for (var i = 0; rules && i < rules.length; i++) {
            if (rules[i].media) note(rules[i]);
            if (rules[i].cssRules) walk(rules[i].cssRules);
            // A stylesheet brought in with @import
            if (rules[i].styleSheet) {
                try {
                    walk(rules[i].styleSheet.cssRules);
                } catch (e) {}
            }
        }
    }
    // Every rule that depends on the device's colour scheme, in every stylesheet that can be read
    function scan() {
        for (var i = 0; i < document.styleSheets.length; i++) {
            var sheet = document.styleSheets[i];
            note(sheet);
            try {
                walk(sheet.cssRules);
            } catch (e) {
                // A stylesheet from another site cannot be read, and is none of ours
            }
        }
    }

    function apply() {
        scan();
        for (var i = 0; i < found.length; i++) {
            var text = found[i].text;
            if (mode !== 'auto') {
                text = text.replace(FEATURE, function (all, scheme) {
                    return (scheme.toLowerCase() === mode) ? '(min-width: 0px)' : '(max-width: 0px)';
                });
            }
            try {
                if (found[i].owner.media.mediaText !== text) found[i].owner.media.mediaText = text;
            } catch (e) {}
        }
        var root = document.documentElement;
        root.setAttribute('data-theme', isDark() ? 'dark' : 'light');
        root.setAttribute('data-theme-choice', mode);
        var buttons = document.querySelectorAll('[data-cc-theme]');
        for (var b = 0; b < buttons.length; b++) {
            var on = buttons[b].getAttribute('data-cc-theme') === mode;
            buttons[b].setAttribute('aria-pressed', on ? 'true' : 'false');
        }
    }

    function tell() {
        var event = { matches: isDark(), media: '(prefers-color-scheme: dark)' };
        for (var i = 0; i < listeners.length; i++) {
            try {
                listeners[i](event);
            } catch (e) {}
        }
        try {
            document.dispatchEvent(new CustomEvent('cc:theme', { detail: { mode: mode, dark: isDark() } }));
        } catch (e) {}
    }

    function listen(fn) {
        if (typeof fn === 'function' && listeners.indexOf(fn) === -1) listeners.push(fn);
    }
    function unlisten(fn) {
        var at = listeners.indexOf(fn);
        if (at !== -1) listeners.splice(at, 1);
    }

    var query = {
        media: '(prefers-color-scheme: dark)',
        addEventListener: function (type, fn) { if (type === 'change') listen(fn); },
        removeEventListener: function (type, fn) { if (type === 'change') unlisten(fn); },
        addListener: listen,
        removeListener: unlisten
    };
    Object.defineProperty(query, 'matches', { get: isDark });

    var api = {
        dark: isDark,
        query: query,
        apply: apply,
        set: function (next) {
            var was = isDark();
            mode = (next === 'light' || next === 'dark') ? next : 'auto';
            try {
                if (mode === 'auto') {
                    window.localStorage.removeItem(KEY);
                } else {
                    window.localStorage.setItem(KEY, mode);
                }
            } catch (e) {}
            apply();
            if (was !== isDark()) tell();
        }
    };
    Object.defineProperty(api, 'mode', { get: function () { return mode; } });
    window.ccTheme = api;

    apply();

    // The device changes its mind (sunset, or its owner), which matters while the choice is Auto
    var deviceChanged = function () {
        if (mode === 'auto') {
            apply();
            tell();
        }
    };
    if (system) {
        if (system.addEventListener) {
            system.addEventListener('change', deviceChanged);
        } else if (system.addListener) {
            system.addListener(deviceChanged);
        }
    }
    // The choice is made in another tab
    window.addEventListener('storage', function (e) {
        if (e.key === KEY || e.key === null) {
            var was = isDark();
            mode = stored();
            apply();
            if (was !== isDark()) tell();
        }
    });
    // Stylesheets that arrive after this has run: in the body of a page, or added by a script
    document.addEventListener('load', function (e) {
        if (e.target && e.target.tagName === 'LINK') apply();
    }, true);
    document.addEventListener('DOMContentLoaded', apply);
    window.addEventListener('load', apply);
    // The buttons that make the choice
    document.addEventListener('click', function (e) {
        var button = e.target && e.target.closest ? e.target.closest('[data-cc-theme]') : null;
        if (button) {
            e.preventDefault();
            api.set(button.getAttribute('data-cc-theme'));
        }
    });
})();
