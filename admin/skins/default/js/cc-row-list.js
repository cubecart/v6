/**
 * CubeCart admin: shared "list of editable rows with +/- buttons" component.
 *
 * Layer 1 — declarative row list:
 *   <ANY data-cc-row-list
 *        data-cc-add-button=".add"        (default: ".add")
 *        data-cc-remove-button=".remove"  (default: ".remove")
 *        data-cc-row-container="tbody"    (default: first <tbody> if list is a <table>, else the list element itself)>
 *      <thead>...</thead>
 *      <tbody>
 *         <ANY data-cc-row>...</ANY>
 *      </tbody>
 *      <template data-cc-row-template>
 *         <ANY data-cc-row>
 *            <input name="items[{INDEX}][qty]">
 *         </ANY>
 *      </template>
 *   </ANY>
 *
 *   - Click `.add` (anywhere inside the list) clones the <template> body, replaces
 *     `{INDEX}` placeholders with the next free index, appends to the row container,
 *     and fires a `cc:row-added` event on the row.
 *   - Click `.remove` (anywhere inside a row) removes its closest `[data-cc-row]`
 *     and fires `cc:row-removed` on the list element.
 *   - The list element itself fires `cc:rows-changed` after either operation.
 *
 * Layer 2 — declarative recalculator:
 *   <table data-cc-row-list data-cc-recalc="orderBuilder">
 *      <tbody>
 *         <tr data-cc-row>
 *            <td><input data-cc-field="qty" type="number"></td>
 *            <td><input data-cc-field="price" type="number"></td>
 *            <td><output data-cc-field="line"></output></td>
 *         </tr>
 *      </tbody>
 *   </table>
 *
 *   Register a function on `window.ccRecalc.<name>` which receives:
 *     { list, rows: [[{name, $el, value}, ...], ...] }
 *   and returns:
 *     { rows: [{name: value, ...}, ...], totals?: {selector: value, ...} }
 *
 *   The framework writes returned `rows[i][name]` back into the row's matching
 *   `[data-cc-field=name]` element (input → .val(), output/span/td → .text()),
 *   and writes returned `totals[selector]` back via the same rule.
 *
 *   Recalc runs on:
 *     - input/change on any `[data-cc-field]` inside the list
 *     - the list's own `cc:rows-changed` event
 *     - manual trigger: `$(list).trigger('cc:recalc')`
 */
(function ($) {
    'use strict';
    if (!$ || $.fn.ccRowList) { return; }

    window.ccRecalc = window.ccRecalc || {};

    // Read the recalc value from a [data-cc-field] element.
    function readField($el) {
        if ($el.is('input, select, textarea')) {
            var v = $el.val();
            if ($el.attr('type') === 'number' || $el.is('[data-cc-field-numeric]')) {
                var n = parseFloat(v);
                return isNaN(n) ? 0 : n;
            }
            return v;
        }
        return $el.text();
    }

    // Write a recalc result back into a [data-cc-field] element. Skip the
    // write when the value is unchanged so we don't disturb the cursor while
    // the user is typing in the field.
    function writeField($el, value) {
        var str = (value === null || value === undefined) ? '' : String(value);
        if ($el.is('input, select, textarea')) {
            if ($el.val() !== str) { $el.val(str); }
        } else {
            if ($el.text() !== str) { $el.text(str); }
        }
    }

    function getRowContainer($list) {
        var sel = $list.attr('data-cc-row-container');
        if (sel) { return $list.find(sel).first(); }
        if ($list.is('table')) {
            var $tbody = $list.children('tbody').first();
            if ($tbody.length) { return $tbody; }
        }
        return $list;
    }

    function getTemplate($list) {
        var $tpl = $list.find('template[data-cc-row-template]').first();
        if (!$tpl.length) { return null; }
        // <template>.content is a DocumentFragment; fall back to .innerHTML for
        // older browsers (we only support evergreen, but be defensive).
        var html = $tpl.prop('innerHTML') || '';
        return html;
    }

    function nextIndex($list) {
        var current = +$list.data('ccRowListIndex');
        if (!current) {
            // Initial seed: max existing [data-cc-row-index] + 1, or count of rows.
            var max = -1;
            $list.find('[data-cc-row]').each(function () {
                var i = parseInt($(this).attr('data-cc-row-index'), 10);
                if (!isNaN(i) && i > max) { max = i; }
            });
            current = (max >= 0) ? (max + 1) : $list.find('[data-cc-row]').length;
        }
        $list.data('ccRowListIndex', current + 1);
        return current;
    }

    function addRow($list) {
        var html = getTemplate($list);
        if (html === null) { return null; }
        var idx = nextIndex($list);
        html = html.replace(/\{INDEX\}/g, idx);
        var $row = $($.parseHTML(html.trim()));
        // The template may include whitespace text nodes; keep only the row element(s).
        $row = $row.filter(function () { return this.nodeType === 1; });
        $row.attr('data-cc-row-index', idx);
        getRowContainer($list).append($row);
        $row.trigger('cc:row-added');
        $list.trigger('cc:rows-changed', { reason: 'add', $row: $row, index: idx });
        return $row;
    }

    function removeRow($row) {
        var $list = $row.closest('[data-cc-row-list]');
        var idx = parseInt($row.attr('data-cc-row-index'), 10);
        $row.remove();
        $list.trigger('cc:row-removed', { index: isNaN(idx) ? null : idx });
        $list.trigger('cc:rows-changed', { reason: 'remove', index: isNaN(idx) ? null : idx });
    }

    function collectRows($list) {
        // Exclude .inline-source (legacy cubecart pattern: hidden template rows).
        return $list.find('[data-cc-row]').not('.inline-source').map(function () {
            var $row = $(this);
            var fields = { $row: $row, kind: $row.attr('data-cc-row-kind') || '' };
            $row.find('[data-cc-field]').each(function () {
                var $el = $(this);
                fields[$el.attr('data-cc-field')] = readField($el);
            });
            return fields;
        }).get();
    }

    function collectSummary($list) {
        var summary = {};
        $list.find('[data-cc-field]').each(function () {
            var $el = $(this);
            if ($el.closest('[data-cc-row]').length) { return; }
            summary[$el.attr('data-cc-field')] = readField($el);
        });
        return summary;
    }

    function applyRecalcResult($list, result) {
        if (!result) { return; }
        if (Array.isArray(result.rows)) {
            var $rows = $list.find('[data-cc-row]').not('.inline-source');
            result.rows.forEach(function (rowResult, i) {
                if (!rowResult) { return; }
                var $row = $rows.eq(i);
                Object.keys(rowResult).forEach(function (name) {
                    if (name === '$row' || name === 'kind') { return; }
                    var $field = $row.find('[data-cc-field="' + name + '"]').first();
                    if ($field.length) { writeField($field, rowResult[name]); }
                });
            });
        }
        if (result.summary && typeof result.summary === 'object') {
            Object.keys(result.summary).forEach(function (name) {
                $list.find('[data-cc-field="' + name + '"]').each(function () {
                    var $el = $(this);
                    if ($el.closest('[data-cc-row]').length) { return; }
                    writeField($el, result.summary[name]);
                });
            });
        }
        if (result.totals && typeof result.totals === 'object') {
            Object.keys(result.totals).forEach(function (sel) {
                var $el = $(sel);
                if ($el.length) { writeField($el, result.totals[sel]); }
            });
        }
    }

    function runRecalc($list) {
        var name = $list.attr('data-cc-recalc');
        if (!name) { return; }
        var fn = window.ccRecalc[name];
        if (typeof fn !== 'function') { return; }
        var rows = collectRows($list);
        var summary = collectSummary($list);
        var result = fn({ list: $list, rows: rows, summary: summary });
        applyRecalcResult($list, result);
    }

    // Public jQuery method (mostly for tests / explicit init).
    $.fn.ccRowList = function (action) {
        if (action === 'add')    { return this.each(function () { addRow($(this)); }); }
        if (action === 'recalc') { return this.each(function () { runRecalc($(this)); }); }
        return this;
    };

    // ---------- Delegated event handlers ----------

    $(document).on('click', '[data-cc-row-list] [data-cc-add-trigger], [data-cc-row-list] .cc-row-list__add', function (e) {
        var $list = $(this).closest('[data-cc-row-list]');
        if (!$list.length) { return; }
        e.preventDefault();
        addRow($list);
    });

    // Generic add-button selector (defaults to ".add"). Honour data-cc-add-button
    // override on the list element.
    $(document).on('click', '[data-cc-row-list]', function (e) {
        var $list = $(this);
        var addSel = $list.attr('data-cc-add-button') || '.add';
        var $target = $(e.target).closest(addSel);
        if (!$target.length || !$.contains(this, $target[0])) { return; }
        // Ignore if the list defers add via cc-row-list__add / data-cc-add-trigger
        // — those are handled above. Otherwise, only handle adds that come from
        // the list's *own* add button (not from inside an existing row's UI).
        if ($target.closest('[data-cc-row]').length) { return; }
        e.preventDefault();
        addRow($list);
    });

    $(document).on('click', '[data-cc-row-list] [data-cc-row]', function (e) {
        var $list = $(this).closest('[data-cc-row-list]');
        var rmSel = $list.attr('data-cc-remove-button') || '.remove';
        var $target = $(e.target).closest(rmSel);
        if (!$target.length || !$.contains(this, $target[0])) { return; }
        e.preventDefault();
        var $row = $target.closest('[data-cc-row]');
        if ($row.length) { removeRow($row); }
    });

    // Layer 2: run recalc on field input/change and on row changes.
    $(document).on('input change', '[data-cc-row-list][data-cc-recalc] [data-cc-field]', function () {
        var $list = $(this).closest('[data-cc-row-list]');
        runRecalc($list);
    });
    $(document).on('cc:rows-changed cc:recalc', '[data-cc-row-list][data-cc-recalc]', function () {
        runRecalc($(this));
    });

    // Initial recalc on DOM ready — opt-in via [data-cc-recalc-on-load], so
    // server-rendered totals (e.g. order summary) aren't overwritten before
    // any user interaction.
    $(function () {
        $('[data-cc-row-list][data-cc-recalc][data-cc-recalc-on-load]').each(function () { runRecalc($(this)); });
    });

    // Expose a tiny imperative API for callers that need it (e.g. order builder
    // wiring needs to push remove markers into a hidden field).
    window.ccRowList = {
        add:    function (list) { return addRow($(list)); },
        remove: function (row)  { return removeRow($(row)); },
        recalc: function (list) { return runRecalc($(list)); },
        rows:   function (list) { return collectRows($(list)); }
    };
})(window.jQuery);
