/**
 * Atrium — recently viewed products.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * The list is this browser's alone: localStorage, never sent to the server.
 * That is the feature, not an implementation detail — it means no core change,
 * no table, no session, and nothing to disclose in a privacy policy.
 *
 * ⚠ Everything is built with createElement and textContent, never innerHTML.
 * The values were written by this skin, but localStorage is writable by
 * anything else running on the origin, so it is treated as untrusted input on
 * the way back in: the URL is scheme-checked and the image is dropped unless it
 * looks like one.
 */
(function () {
    'use strict';

    var KEY = 'cc_recent';
    var MAX_STORED = 12;
    /* One full row at the widest breakpoint. Narrower viewports show fewer,
       and that is decided in CSS (.cc-recent-row) rather than here: a JS count
       would need a resize listener, would be wrong until it fired, and would
       have to re-render on every rotate. Rendering four and hiding two costs
       nothing — the images are loading="lazy", and a display:none image is
       never fetched. */
    var MAX_SHOWN = 4;

    function read() {
        try {
            var raw = window.localStorage.getItem(KEY);
            var list = raw ? JSON.parse(raw) : [];
            return Object.prototype.toString.call(list) === '[object Array]' ? list : [];
        } catch (e) {
            return [];   // blocked storage, or somebody left junk in the key
        }
    }

    function write(list) {
        try {
            window.localStorage.setItem(KEY, JSON.stringify(list));
        } catch (e) { /* private mode, or quota */ }
    }

    /* Only http(s) and root-relative paths. Blocks a javascript: or data: URL
       smuggled into storage from turning a thumbnail into a script. */
    function safeUrl(value) {
        var url = String(value || '');
        return (/^https?:\/\//i.test(url) || url.charAt(0) === '/') ? url : '';
    }

    function record() {
        var node = document.getElementById('cc-recent-record');
        if (!node) return null;

        var item;
        try {
            item = JSON.parse(node.textContent);
        } catch (e) {
            return null;
        }
        if (!item || !item.id) return null;

        var list = read();
        // Seen before: lift it to the front rather than storing it twice.
        for (var i = list.length - 1; i >= 0; i--) {
            if (String(list[i].id) === String(item.id)) list.splice(i, 1);
        }
        list.unshift(item);
        if (list.length > MAX_STORED) list.length = MAX_STORED;
        write(list);
        return String(item.id);
    }

    function card(item) {
        var li = document.createElement('li');
        li.className = 'group flex flex-col';

        var link = document.createElement('a');
        link.href = safeUrl(item.url);
        link.title = String(item.name || '');
        link.className = 'cc-media block overflow-hidden rounded-cc-lg border border-ink-200';

        var img = document.createElement('img');
        img.src = safeUrl(item.img);
        img.alt = String(item.name || '');
        img.loading = 'lazy';
        img.className = 'aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105';
        link.appendChild(img);
        li.appendChild(link);

        var heading = document.createElement('h3');
        heading.className = 'mt-3 text-sm font-medium';
        var nameLink = document.createElement('a');
        nameLink.href = link.href;
        // textContent, not innerHTML: the name came back out of storage.
        nameLink.textContent = String(item.name || '');
        nameLink.className = 'text-ink-900 hover:underline';
        heading.appendChild(nameLink);
        li.appendChild(heading);

        if (item.price) {
            var price = document.createElement('div');
            price.className = 'price mt-2 text-base font-semibold text-ink-900';
            price.textContent = String(item.price);
            li.appendChild(price);
        }
        return li;
    }

    function render(currentId) {
        var section = document.getElementById('cc-recent');
        var list = document.getElementById('cc-recent-list');
        if (!section || !list) return;

        var items = read();
        var shown = 0;
        for (var i = 0; i < items.length && shown < MAX_SHOWN; i++) {
            var item = items[i];
            // Never show the page you are already on.
            if (currentId && String(item.id) === currentId) continue;
            if (!item.url || !safeUrl(item.url)) continue;
            list.appendChild(card(item));
            shown++;
        }
        /* One product viewed and nothing else to show is not "recently viewed",
           it is a row of one. The section stays hidden until it earns its
           heading. */
        if (shown > 1) section.classList.remove('hidden');
    }

    function init() {
        render(record());
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
