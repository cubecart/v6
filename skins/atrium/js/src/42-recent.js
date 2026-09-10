/**
 * Atrium — recently viewed products. No template literals (00-boot.js).
 *
 * localStorage only, never sent to the server: no core change, no table, no
 * session, nothing to disclose.
 *
 * ⚠ Read back as UNTRUSTED — anything on the origin can write that key. URLs are
 * scheme-checked and nodes are built with textContent, never innerHTML.
 */
(function () {
    'use strict';

    var KEY = 'cc_recent';
    var removeLabel = 'Remove';   // replaced from the template's data attribute
    var MAX_STORED = 12;
    // One row at the widest breakpoint; CSS (.cc-recent-row) trims the rest.
    var MAX_SHOWN = 4;

    function read() {
        try {
            var raw = window.localStorage.getItem(KEY);
            var list = raw ? JSON.parse(raw) : [];
            return Object.prototype.toString.call(list) === '[object Array]' ? list : [];
        } catch (e) {
            return [];   // blocked storage, or junk in the key
        }
    }

    function write(list) {
        try {
            window.localStorage.setItem(KEY, JSON.stringify(list));
        } catch (e) { /* private mode, or quota */ }
    }

    /** Drop one product from the stored list. */
    function forget(id) {
        var list = read();
        for (var i = list.length - 1; i >= 0; i--) {
            if (String(list[i].id) === String(id)) list.splice(i, 1);
        }
        write(list);
    }

    // http(s) and root-relative only: blocks a smuggled javascript: URL.
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
        // Seen before: lift to the front rather than store it twice.
        for (var i = list.length - 1; i >= 0; i--) {
            if (String(list[i].id) === String(item.id)) list.splice(i, 1);
        }
        list.unshift(item);
        if (list.length > MAX_STORED) list.length = MAX_STORED;
        write(list);
        return String(item.id);
    }

    /* Always visible rather than hover-revealed: on a touch device there is no
       hover, and a control the customer cannot find is not a control. */
    function removeButton(item, li) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'absolute end-1.5 top-1.5 z-10 grid size-7 place-items-center rounded-full ' +
                           'bg-ink-50/90 text-ink-600 shadow hover:bg-ink-200 hover:text-ink-900';
        var label = (removeLabel + ' ' + (item.name || '')).trim();
        button.title = label;
        button.setAttribute('aria-label', label);
        button.innerHTML = '<svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
                           'stroke-width="2" aria-hidden="true"><path d="M6 18 18 6M6 6l12 12" ' +
                           'stroke-linecap="round"/></svg>';
        button.addEventListener('click', function () {
            forget(item.id);
            li.remove();
            /* nth-child does the trimming, so removing a node promotes a hidden
               one into the row on its own.

               Hidden only at ZERO, not at one. The "a row of one is not
               recently viewed" rule below applies to the FIRST render; once a
               customer has removed something deliberately, the one they kept is
               the one they want to see, and taking the section away with it
               looks like the X cleared everything. */
            var list = document.getElementById('cc-recent-list');
            var section = document.getElementById('cc-recent');
            if (list && section && !list.children.length) section.classList.add('hidden');
        });
        return button;
    }

    function card(item) {
        var li = document.createElement('li');
        li.className = 'group flex flex-col';

        // The button is a SIBLING of the link, never inside it: a <button> in an
        // <a> is invalid and browsers reparent it.
        var frame = document.createElement('div');
        frame.className = 'relative';

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
        frame.appendChild(link);
        frame.appendChild(removeButton(item, li));
        li.appendChild(frame);

        var heading = document.createElement('h3');
        heading.className = 'mt-3 text-sm font-medium';
        var nameLink = document.createElement('a');
        nameLink.href = link.href;
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

        removeLabel = section.getAttribute('data-remove-label') || removeLabel;

        var items = read();
        var shown = 0;
        for (var i = 0; i < items.length && shown < MAX_SHOWN; i++) {
            var item = items[i];
            // Never the page you are already on.
            if (currentId && String(item.id) === currentId) continue;
            if (!item.url || !safeUrl(item.url)) continue;
            list.appendChild(card(item));
            shown++;
        }
        // A row of one is not "recently viewed".
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
