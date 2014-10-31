<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<div id="stats_sales" class="tab_content">
  <h3>{$LANG.statistics.title_sales}</h3>
  {if $DISPLAY_SALES}
  <form action="{$VAL_SELF}" method="post">
	<div>
	  <fieldset><legend>{$LANG.common.filter}</legend>
	    <select name="select[year]">
	    {foreach from=$YEARS item=year}><option value="{$year.value}" {$year.selected}>{$year.value}</option>{/foreach}
	    </select>
	    <select name="select[month]">
	    {foreach from=$MONTHS item=month}<option value="{$month.value}"{$month.selected}>{$month.title}{/foreach}
	    </select>
	    <select name="select[day]">
	    {foreach from=$DAYS item=day}<option value="{$day.value}"{$day.selected}>{$day.value}</option>{/foreach}
	    </select>
	    <input type="submit" value="{$LANG.common.go}">
	  </fieldset>
	</div>
	<input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>
    
    <script type="text/javascript">
      {literal}
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([{/literal}{$GRAPH_DATA.yearly.data}{literal}]);
        var options = {
          title: '{/literal}{$GRAPH_DATA.yearly.title}{literal}',
          hAxis: {title: '{/literal}{$GRAPH_DATA.yearly.hAxis}{literal}'},
          vAxis: {title: '{/literal}{$GRAPH_DATA.yearly.vAxis}{literal}'}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('yearly_sales'));
        chart.draw(data, options);
      }
      {/literal}
    </script>
    <div id="yearly_sales" style="width: 600px; height: 500px;"></div>
    
    <script type="text/javascript">
      {literal}
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([{/literal}{$GRAPH_DATA.monthly.data}{literal}]);
        var options = {
          title: '{/literal}{$GRAPH_DATA.monthly.title}{literal}',
          hAxis: {title: '{/literal}{$GRAPH_DATA.monthly.hAxis}{literal}'},
          vAxis: {title: '{/literal}{$GRAPH_DATA.monthly.vAxis}{literal}'}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('monthly_sales'));
        chart.draw(data, options);
      }
      {/literal}
    </script>
    <div id="monthly_sales" style="width: 600px; height: 500px;"></div>
    
    <script type="text/javascript">
      {literal}
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([{/literal}{$GRAPH_DATA.daily.data}{literal}]);
        var options = {
          title: '{/literal}{$GRAPH_DATA.daily.title}{literal}',
          hAxis: {title: '{/literal}{$GRAPH_DATA.daily.hAxis}{literal}'},
          vAxis: {title: '{/literal}{$GRAPH_DATA.daily.vAxis}{literal}'}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('daily_sales'));
        chart.draw(data, options);
      }
      {/literal}
    </script>
    <div id="daily_sales" style="width: 600px; height: 500px;"></div>
  
	
	<script type="text/javascript">
      {literal}
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([{/literal}{$GRAPH_DATA.hourly.data}{literal}]);
        var options = {
          title: '{/literal}{$GRAPH_DATA.hourly.title}{literal}',
          hAxis: {title: '{/literal}{$GRAPH_DATA.hourly.hAxis}{literal}'},
          vAxis: {title: '{/literal}{$GRAPH_DATA.hourly.vAxis}{literal}'}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('hourly_sales'));
        chart.draw(data, options);
      }
      {/literal}
    </script>
    <div id="hourly_sales" style="width: 600px; height: 500px;"></div>
  {else}
  <p>{$LANG.statistics.notify_sales_none}</p>
  {/if}
</div>

{if isset($PRODUCT_SALES)}
<div id="stats_prod_sales" class="tab_content">
  <h3>{$LANG.statistics.title_popular}</h3>
  
  <script type="text/javascript">
      {literal}
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([{/literal}{$GRAPH_DATA.prodsales.data}{literal}]);
        var options = {
          title: '{/literal}{$GRAPH_DATA.prodsales.title}{literal}',
          hAxis: {title: '{/literal}{$GRAPH_DATA.prodsales.hAxis}{literal}'},
          vAxis: {title: '{/literal}{$GRAPH_DATA.prodsales.vAxis}{literal}'}
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('prodsales'));
        chart.draw(data, options);
      }
      {/literal}
    </script>
    <div id="prodsales" style="width: 600px; height: 500px;"></div>
  
  <div>{$PAGINATION_SALES}</div>
 <table>
	<thead>
	  <tr>
		<td></td>
		<td>{$LANG.catalogue.product_name}</td>
		<td width="130">{$LANG.statistics.quantity_sold}</td>
		<td width="150">{$LANG.statistics.percentage_of_total}</td>
	  </tr>
	</thead>
	<tbody>
	  {foreach from=$PRODUCT_SALES item=sale}
	  <tr>
		<td>{$sale.key}</td>
		<td>{$sale.name}</td>
		<td>{$sale.quan}</td>
		<td>{$sale.percent}</td>
	  </tr>
	  {/foreach}
	</tbody>
  </table>
</div>
{/if}

{if isset($PRODUCT_VIEWS)}
<div id="stats_prod_views" class="tab_content">
  <h3>{$LANG.statistics.title_viewed}</h3>
  
  <script type="text/javascript">
      {literal}
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([{/literal}{$GRAPH_DATA.prodviews.data}{literal}]);
        var options = {
          title: '{/literal}{$GRAPH_DATA.prodviews.title}{literal}',
          hAxis: {title: '{/literal}{$GRAPH_DATA.prodviews.hAxis}{literal}'},
          vAxis: {title: '{/literal}{$GRAPH_DATA.prodviews.vAxis}{literal}'}
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('prodviews'));
        chart.draw(data, options);
      }
      {/literal}
    </script>
    <div id="prodviews" style="width: 600px; height: 500px;"></div>
  <div>{$PAGINATION_VIEWS}</div>
  <table>
	<thead>
	  <tr>
		<td width="20">&nbsp;</td>
		<td>{$LANG.catalogue.product_name}</td>
		<td width="130">{$LANG.statistics.product_views}</td>
		<td width="150">{$LANG.statistics.percentage_of_views}</td>
	  </tr>
	</thead>
	<tbody>
	  {foreach from=$PRODUCT_VIEWS item=view}
	  <tr>
		<td align="center">{$view.key}</td>
		<td>{$view.name}</td>
		<td>{$view.popularity}</td>
		<td>{$view.percent}</td>
	  </tr>
	  {/foreach}
	<tbody>
  </table>
</div>
{/if}

{if isset($SEARCH_TERMS)}
<div id="stats_search" class="tab_content">
  <h3>{$LANG.statistics.title_search}</h3>
  
  <script type="text/javascript">
      {literal}
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([{/literal}{$GRAPH_DATA.search.data}{literal}]);
        var options = {
          title: '{/literal}{$GRAPH_DATA.search.title}{literal}',
          hAxis: {title: '{/literal}{$GRAPH_DATA.search.hAxis}{literal}'},
          vAxis: {title: '{/literal}{$GRAPH_DATA.search.vAxis}{literal}'}
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('search'));
        chart.draw(data, options);
      }
      {/literal}
    </script>
    <div id="search" style="width: 600px; height: 500px;"></div>
  <div>{$PAGINATION_SEARCH}</div>
  <table>
	<thead>
	  <tr>
		<td width="20">&nbsp;</td>
		<td>{$LANG.statistics.search_term}</td>
		<td width="130">{$LANG.statistics.product_hits}</td>
		<td width="150">{$LANG.statistics.percentage_of_search}</td>
	  </tr>
	</thead>
	<tbody>
	  {foreach from=$SEARCH_TERMS item=term}
	  <tr>
		<td align="center">{$term.key}</td>
		<td>{$term.searchstr}</td>
		<td>{$term.hits}</td>
		<td>{$term.percent}</td>
	  </tr>
	  {/foreach}
	<tbody>
  </table>
</div>
{/if}

{if isset($BEST_CUSTOMERS)}
  <div id="stats_best_customers" class="tab_content">
  <h3>{$LANG.statistics.title_customers_best}</h3>
  <script type="text/javascript">
      {literal}
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([{/literal}{$GRAPH_DATA.best_customers.data}{literal}]);
        var options = {
          title: '{/literal}{$GRAPH_DATA.best_customers.title}{literal}',
          hAxis: {title: '{/literal}{$GRAPH_DATA.best_customers.hAxis}{literal}'},
          vAxis: {title: '{/literal}{$GRAPH_DATA.best_customers.vAxis}{literal}'}
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('best_customers'));
        chart.draw(data, options);
      }
      {/literal}
    </script>
    <div id="best_customers" style="width: 600px; height: 500px;"></div>
  <div>{$PAGINATION_BEST}</div>
  <table>
	<thead>
	  <tr>
		<td width="20">&nbsp;</td>
		<td>{$LANG.common.name}</td>
		<td width="130">{$LANG.statistics.total_expenditure}</td>
		<td width="150">{$LANG.statistics.percentage_of_total}</td>
	  </tr>
	</thead>
	<tbody>
	  {foreach from=$BEST_CUSTOMERS item=customer}
	  <tr>
		<td align="center">{$customer.key}</td>
		<td><a href="?_g=customers&node=index&action=edit&customer_id={$customer.customer_id}">{$customer.last_name}, {$customer.first_name}</a></td>
		<td>{$customer.expenditure}</td>
		<td>{$customer.percent}</td>
	  </tr>
	  {/foreach}
	<tbody>
  </table>
  </div>
  {/if}

  {if isset($USERS_ONLINE)}
  <div id="stats_online" class="tab_content">
  <h3>{$LANG.statistics.title_customers_active}</h3>
  <p>
  {if $BOTS==true}
  	<a href="?_g=statistics&bots=false#stats_online">{$LANG.statistics.display_customers_only}</a>
  {else}
  	<a href="?_g=statistics&bots=true#stats_online">{$LANG.statistics.display_bots_and_customers}</a>
  {/if}
  </p>
  <table>
	<thead>
	  <tr>
		<td>{$LANG.statistics.session_admin}</td>
		<td>{$LANG.statistics.session_user}</td>
		<td>{$LANG.statistics.session_location}</td>
		<td>{$LANG.statistics.session_started}</td>
		<td>{$LANG.statistics.session_last}</td>
		<td>{$LANG.statistics.session_length}</td>
	  </tr>
	</thead>
	<tbody>
	{foreach from=$USERS_ONLINE item=user}
	  <tr>
		<td align="center"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/{$user.is_admin}.png"></td>
		<td>
		  <strong>
		  {if !empty($user.customer_id)}
		  	<a href="{$CONFIG.adminFile}?_g=customers&action=edit&customer_id={$user.customer_id}">{$user.name}</a>
		  {else}
		  	{$user.name}
		  {/if}
		  </strong>
		  {if !empty($user.ip_address)}
		  <br>
		  [<a href="http://api.hostip.info/get_html.php?ip={$user.ip_address}&position=true" class="colorbox hostip">{$user.ip_address}</a>]
		  {/if}
		</td>
		<td>{$user.location} <a href="{$user.location}" target="_blank">&raquo;</a></td>
		<td align="center">{$user.session_start}</td>
		<td align="center"	>{$user.session_last}</td>
		<td>{$user.session_length}</td>
	  </tr>
	{/foreach}
	</tbody>
  </table>
  </div>
 {/if}