{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

{* Chart helpers must exist before the included tab body's inline script runs. *}
<script type="text/javascript">
{literal}
google.charts.load('current', {packages: ['corechart']});

window.chart_data = window.chart_data || [];
window.chart_options = window.chart_options || [];

window.whenChartsReady = function(fn) {
    // Use the proper async hook from the new gstatic loader so callbacks
    // don't fire before all packages (corechart -> ColumnChart) are ready.
    if (window.google && window.google.charts && typeof window.google.charts.setOnLoadCallback === 'function') {
        google.charts.setOnLoadCallback(fn);
    } else {
        setTimeout(function() { window.whenChartsReady(fn); }, 50);
    }
};

window.drawChart = function(id, chart_data) {
    var container = document.getElementById('chart' + id);
    if (container == null) return false;
    // gstatic loader's setOnLoadCallback can fire before admin.js shows the
    // active tab (it removes display:none on doc-ready). If we draw while the
    // container has no layout, Google Charts measures 0 width and falls back
    // to its ~400px default — locked in until a window resize. Defer the draw
    // until the container actually gets a box.
    if (!container.clientWidth) {
        if (typeof ResizeObserver === 'function') {
            var ro = new ResizeObserver(function() {
                if (container.clientWidth) {
                    ro.disconnect();
                    window.drawChart(id, chart_data);
                }
            });
            ro.observe(container);
        } else {
            setTimeout(function() { window.drawChart(id, chart_data); }, 100);
        }
        return false;
    }
    var chart_title = document.getElementById('chart' + id + '-title');
    var chart_hAxis = document.getElementById('chart' + id + '-hAxis');
    var chart_vAxis = document.getElementById('chart' + id + '-vAxis');

    var data = google.visualization.arrayToDataTable(chart_data[id]);
    var yMax = 0, gNOR = data.getNumberOfRows(), gNOC = data.getNumberOfColumns();
    for (var x = 1; x < gNOC; x++) {
        if (data.getColumnType(x) !== 'number') continue; // skip style/role columns
        for (var y = 0; y < gNOR; y++) {
            yMax = Math.max(data.getValue(y, x), yMax);
        }
    }

    var log10yMax = Math.log10(yMax);
    var floorexp = Math.floor(log10yMax);
    var normyMax = yMax / Math.pow(10, floorexp);
    var ceilnormyMax = Math.ceil(normyMax);
    yMax = ceilnormyMax * Math.pow(10, floorexp);

    if (yMax < 20 || isNaN(yMax)) { yMax = 20; }
    const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = isDark ? {
        backgroundColor: '#171b1f',
        chartAreaBg: '#171b1f',
        text: '#e6e6e6',
        gridline: '#2c343b'
    } : {
        backgroundColor: '#ffffff',
        chartAreaBg: '#ffffff',
        text: '#555555',
        gridline: '#ddd'
    };
    var options = {
        titleTextStyle: { color: theme.text },
        backgroundColor: theme.backgroundColor,
        chartArea: { backgroundColor: theme.chartAreaBg },
        legend: { textStyle: { color: theme.text } },
        title: chart_title ? chart_title.innerHTML : '',
        hAxis: {
            title: chart_hAxis ? chart_hAxis.innerHTML : '',
            textStyle: { color: theme.text },
            gridlines: { color: theme.gridline }
        },
        vAxis: {
            title: chart_vAxis ? chart_vAxis.innerHTML : '',
            textStyle: { color: theme.text },
            gridlines: { color: theme.gridline },
            viewWindowMode: 'explicit',
            viewWindow: { min: 0, max: yMax }
        }
    };
    var custom = (window.chart_options && window.chart_options[id]) || {};
    if (custom.colors) options.colors = custom.colors;
    if (custom.legend === 'none') options.legend = 'none';
    if (custom.yFormat) options.vAxis.format = custom.yFormat;
    var chart = new google.visualization.ColumnChart(container);
    chart.draw(data, options);

    if (custom.drill && typeof window.statsHandleDrill === 'function') {
        google.visualization.events.addListener(chart, 'select', function() {
            var sel = chart.getSelection();
            if (!sel.length || sel[0].row === null) return;
            window.statsHandleDrill(custom.drill, sel[0].row, sel[0].column);
        });
    }
};

// Drill-down: translate a chart bar click into URL param updates and refetch.
window.statsHandleDrill = function(drill, rowIdx, colIdx) {
    var pad = function(n) { return String(n).padStart(2, '0'); };
    var updates = {};
    var scrollTo = null;
    if (drill.type === 'year') {
        updates.m_year = drill.years[rowIdx];
        scrollTo = 'chart2';
    } else if (drill.type === 'month') {
        // YoY chart: col 1 = current year, col 2 = prior year.
        var year = (colIdx === 1) ? drill.year : drill.year - 1;
        updates.d_year  = year;
        updates.d_month = pad(rowIdx + 1);
        scrollTo = 'chart3';
    } else if (drill.type === 'day') {
        updates.h_year  = drill.year;
        updates.h_month = drill.month;
        updates.h_day   = pad(rowIdx + 1);
        scrollTo = 'chart4';
    } else if (drill.type === 'product') {
        var pid = drill.product_ids && drill.product_ids[rowIdx];
        if (pid) window.location.href = '?_g=statistics&node=product&product_id=' + pid;
        return; // full navigation, no AJAX refetch needed
    } else if (drill.type === 'customer') {
        var cid = drill.customer_ids && drill.customer_ids[rowIdx];
        if (cid) window.location.href = '?_g=customers&node=index&action=edit&customer_id=' + cid;
        return;
    } else if (drill.type === 'search') {
        var term = drill.terms && drill.terms[rowIdx];
        if (term && drill.storeURL) {
            window.open(drill.storeURL + '/index.php?_a=search&search[keywords]=' + encodeURIComponent(term), '_blank', 'noopener');
        }
        return;
    }
    window.statsDrillTo(updates, scrollTo);
};

window.statsDrillTo = function(updates, scrollTo) {
    var tabDiv = document.getElementById('stats_sales');
    if (!tabDiv) return;
    var params = new URLSearchParams(window.location.search);
    for (var k in updates) params.set(k, updates[k]);
    params.set('tab', 'stats_sales');
    var pushParams = new URLSearchParams(params.toString());
    pushParams.delete('format');
    var pushUrl = '?' + pushParams.toString() + '#stats_sales';
    try { history.pushState({ tabId: 'stats_sales' }, '', pushUrl); } catch(_) {}
    var ajaxParams = new URLSearchParams(params.toString());
    ajaxParams.set('format', 'fragment');
    statsFetchInto(tabDiv, '?' + ajaxParams.toString()).then(function() {
        if (scrollTo) {
            var target = document.getElementById(scrollTo);
            if (target) {
                // Scroll the next chart card (the .stat-chart wrapper) into view.
                var card = target.closest('.stat-chart') || target;
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
};

// Mirror ?tab= into the hash so admin.js's tab strip activates the right tab.
(function() {
    try {
        var t = new URLSearchParams(window.location.search).get('tab');
        if (t && !window.location.hash) {
            window.location.hash = t;
        }
    } catch(e) {}
})();
{/literal}
</script>

<div id="stats_sales" class="tab_content"{if $ACTIVE_TAB != 'stats_sales'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_sales'}{include file='templates/statistics.tabs.php'}{/if}
</div>
<div id="stats_funnel" class="tab_content"{if $ACTIVE_TAB != 'stats_funnel'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_funnel'}{include file='templates/statistics.tabs.php'}{/if}
</div>
<div id="stats_abandoned" class="tab_content"{if $ACTIVE_TAB != 'stats_abandoned'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_abandoned'}{include file='templates/statistics.tabs.php'}{/if}
</div>
<div id="stats_country" class="tab_content"{if $ACTIVE_TAB != 'stats_country'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_country'}{include file='templates/statistics.tabs.php'}{/if}
</div>
<div id="stats_prod_sales" class="tab_content"{if $ACTIVE_TAB != 'stats_prod_sales'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_prod_sales'}{include file='templates/statistics.tabs.php'}{/if}
</div>
<div id="stats_prod_views" class="tab_content"{if $ACTIVE_TAB != 'stats_prod_views'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_prod_views'}{include file='templates/statistics.tabs.php'}{/if}
</div>
<div id="stats_search" class="tab_content"{if $ACTIVE_TAB != 'stats_search'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_search'}{include file='templates/statistics.tabs.php'}{/if}
</div>
<div id="stats_best_customers" class="tab_content"{if $ACTIVE_TAB != 'stats_best_customers'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_best_customers'}{include file='templates/statistics.tabs.php'}{/if}
</div>

{if isset($PLUGIN_TABS) && $PLUGIN_TABS}
   {foreach from=$PLUGIN_TABS item=tab}
      {$tab}
   {/foreach}
{/if}

<div id="stats_online" class="tab_content"{if $ACTIVE_TAB != 'stats_online'} data-needs-load="1"{/if}>
   {if $ACTIVE_TAB == 'stats_online'}{include file='templates/statistics.tabs.php'}{/if}
</div>

<script type="text/javascript">
{literal}
// Redraw any visible charts on resize so they fit their container.
window.addEventListener('resize', function() {
    for (var id in window.chart_data) {
        if (document.getElementById('chart' + id)) {
            window.drawChart(id, window.chart_data);
        }
    }
});

// Vanilla JS — this script runs before jQuery is loaded by the admin layout.
function statsFetchInto(div, url) {
    div.setAttribute('data-loading', '1');
    div.innerHTML = '<p class="stat-loading">Loading…</p>';
    return fetch(url, { credentials: 'same-origin' })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        })
        .then(function(html) {
            div.removeAttribute('data-needs-load');
            div.removeAttribute('data-loading');
            div.innerHTML = html;
            // innerHTML doesn't execute embedded <script> tags; clone-replace each
            // so the chart-init scripts inside the fragment actually run.
            var scripts = div.querySelectorAll('script');
            for (var i = 0; i < scripts.length; i++) {
                var oldS = scripts[i];
                var newS = document.createElement('script');
                if (oldS.src) {
                    newS.src = oldS.src;
                } else {
                    newS.textContent = oldS.textContent;
                }
                oldS.parentNode.replaceChild(newS, oldS);
            }
            // admin.js wraps native checkboxes in .custom-checkbox at document.ready;
            // it doesn't see ones we inject post-load, so wrap them here.
            var cbs = div.querySelectorAll('input[type="checkbox"]');
            for (var j = 0; j < cbs.length; j++) {
                var cb = cbs[j];
                if (cb.parentNode && cb.parentNode.classList && cb.parentNode.classList.contains('custom-checkbox')) continue;
                var wrap = document.createElement('div');
                wrap.className = 'custom-checkbox';
                if (cb.checked) wrap.classList.add('selected');
                cb.parentNode.insertBefore(wrap, cb);
                wrap.appendChild(cb);
            }
        })
        .catch(function() {
            div.removeAttribute('data-loading');
            div.innerHTML = '<p class="stat-loading">Failed to load.</p>';
        });
}

function statsLoadTab(tabId) {
    var div = document.getElementById(tabId);
    if (!div || !div.hasAttribute('data-needs-load')) return;
    statsFetchInto(div, '?_g=statistics&format=fragment&tab=' + encodeURIComponent(tabId));
}

// Auto-submit a .stats-filter form when its select changes — so the user
// doesn't have to click Go after every dropdown tweak. Go stays as the
// explicit/keyboard fallback.
document.addEventListener('change', function(e) {
    var sel = e.target;
    if (!sel || sel.tagName !== 'SELECT') return;
    var form = sel.closest && sel.closest('.stats-filter');
    if (!form) return;
    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    }
});

// AJAX-ify any in-tab filter form (.stats-filter) so submitting refreshes
// just the tab and pushes a clean URL into history for sharing.
document.addEventListener('submit', function(e) {
    var form = e.target;
    if (!form || !form.classList || !form.classList.contains('stats-filter')) return;
    var tabDiv = form.closest('.tab_content');
    if (!tabDiv || !tabDiv.id) return;
    e.preventDefault();

    var params = new URLSearchParams(new FormData(form));
    params.set('format', 'fragment');
    params.set('tab', tabDiv.id);

    var pushParams = new URLSearchParams(params);
    pushParams.delete('format');
    var pushUrl = '?' + pushParams.toString() + '#' + tabDiv.id;
    try { history.pushState({ tabId: tabDiv.id }, '', pushUrl); } catch(_) {}

    statsFetchInto(tabDiv, '?' + params.toString());
});

// AJAX-ify pagination links inside any tab body so paging doesn't full-reload.
document.addEventListener('click', function(e) {
    var t = e.target;
    if (!t || !t.closest) return;
    var a = t.closest('.tab_content .pagination a');
    if (!a) return;
    var tabDiv = a.closest('.tab_content');
    if (!tabDiv || !tabDiv.id) return;
    var href = a.getAttribute('href');
    if (!href || href.indexOf('_g=statistics') === -1) return;
    e.preventDefault();

    var hashIdx  = href.indexOf('#');
    var hrefBase = (hashIdx === -1) ? href : href.substring(0, hashIdx);
    var hrefHash = (hashIdx === -1) ? ('#' + tabDiv.id) : href.substring(hashIdx);
    var sep      = (hrefBase.indexOf('?') === -1) ? '?' : '&';
    var ajaxUrl  = hrefBase + sep + 'format=fragment&tab=' + encodeURIComponent(tabDiv.id);
    var pushUrl  = hrefBase + (hrefBase.indexOf('tab=') === -1 ? sep + 'tab=' + encodeURIComponent(tabDiv.id) : '') + hrefHash;
    try { history.pushState({ tabId: tabDiv.id }, '', pushUrl); } catch(_) {}

    statsFetchInto(tabDiv, ajaxUrl);
});

// Auto-refresh the Online tab. Fires on a 15s tick (when visible) and also
// the moment the user switches focus back to the tab.
function statsRefreshOnline() {
    var div = document.getElementById('stats_online');
    if (!div || div.offsetParent === null) return;     // tab not currently shown
    if (div.hasAttribute('data-loading')) return;       // mid-fetch already
    if (div.hasAttribute('data-needs-load')) return;    // not yet loaded once
    var url = '?_g=statistics&format=fragment&tab=stats_online';
    var qs  = new URLSearchParams(window.location.search);
    if (qs.get('bots') !== null) url += '&bots=' + encodeURIComponent(qs.get('bots'));
    fetch(url, { credentials: 'same-origin', cache: 'no-store' })
        .then(function(r) { return r.ok ? r.text() : null; })
        .then(function(html) {
            if (html === null) return;
            div.innerHTML = html;
            var scripts = div.querySelectorAll('script');
            for (var i = 0; i < scripts.length; i++) {
                var n = document.createElement('script');
                if (scripts[i].src) n.src = scripts[i].src;
                else                n.textContent = scripts[i].textContent;
                scripts[i].parentNode.replaceChild(n, scripts[i]);
            }
        })
        .catch(function() { /* swallow — try again next tick */ });
}
setInterval(function() {
    if (document.visibilityState !== 'visible') return;
    statsRefreshOnline();
}, 15000);
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') statsRefreshOnline();
});
window.addEventListener('focus', statsRefreshOnline);
// Manual refresh trigger via .stats-refresh button in the card header.
document.addEventListener('click', function(e) {
    var btn = e.target && e.target.closest && e.target.closest('.stats-refresh');
    if (!btn) return;
    e.preventDefault();
    var div = document.getElementById('stats_online');
    if (div) div.setAttribute('data-needs-load', '1');
    statsLoadTab('stats_online');
});

// Back/forward through paginated states: refetch the current URL as a fragment.
window.addEventListener('popstate', function(e) {
    var state = e.state || {};
    if (!state.tabId) return; // not one of our pushStates
    var div = document.getElementById(state.tabId);
    if (!div) return;
    var search = window.location.search.replace(/^\?/, '');
    var parts = search ? search.split('&').filter(function(p) { return p && p.indexOf('format=') !== 0; }) : [];
    parts.push('format=fragment');
    if (!parts.some(function(p) { return p.indexOf('tab=') === 0; })) {
        parts.push('tab=' + encodeURIComponent(state.tabId));
    }
    statsFetchInto(div, '?' + parts.join('&'));
});

function statsRedrawTabCharts(tabId) {
    var div = document.getElementById(tabId);
    if (!div || div.offsetParent === null) return;
    var charts = div.querySelectorAll('.google_chart');
    for (var i = 0; i < charts.length; i++) {
        var chartId = charts[i].id.replace('chart', '');
        if (window.chart_data && window.chart_data[chartId]) {
            (function(id) {
                window.whenChartsReady(function() { window.drawChart(id, window.chart_data); });
            })(chartId);
        }
    }
}

function statsSyncFromHash() {
    var h = window.location.hash.replace(/^#/, '');
    if (h && document.getElementById(h)) {
        // Online tab is live data — always refetch on activation so the user
        // doesn't see a stale snapshot waiting for the next 30s auto-tick.
        if (h === 'stats_online') {
            var div = document.getElementById('stats_online');
            if (div) div.setAttribute('data-needs-load', '1');
        }
        statsLoadTab(h);
        setTimeout(function() { statsRedrawTabCharts(h); }, 100);
    }
}

window.addEventListener('hashchange', statsSyncFromHash);
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', statsSyncFromHash);
} else {
    statsSyncFromHash();
}
{/literal}
</script>
