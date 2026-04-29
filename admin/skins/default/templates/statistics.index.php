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
    if (window.google && window.google.visualization && window.google.visualization.arrayToDataTable) {
        fn();
    } else {
        setTimeout(function() { window.whenChartsReady(fn); }, 50);
    }
};

window.drawChart = function(id, chart_data) {
    var container = document.getElementById('chart' + id);
    if (container == null) return false;
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
    var chart = new google.visualization.ColumnChart(container);
    chart.draw(data, options);
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
    div.innerHTML = '<p style="padding:1em;">Loading…</p>';
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
        })
        .catch(function() {
            div.removeAttribute('data-loading');
            div.innerHTML = '<p style="padding:1em;">Failed to load.</p>';
        });
}

function statsLoadTab(tabId) {
    var div = document.getElementById(tabId);
    if (!div || !div.hasAttribute('data-needs-load')) return;
    statsFetchInto(div, '?_g=statistics&format=fragment&tab=' + encodeURIComponent(tabId));
}

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
    var a = (t && t.closest) ? t.closest('.tab_content .pagination a') : null;
    if (!a) return;
    var href = a.getAttribute('href');
    if (!href || href.indexOf('_g=statistics') === -1) return;
    var tabDiv = a.closest('.tab_content');
    if (!tabDiv || !tabDiv.id) return;
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
