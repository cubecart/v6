<div id="breadcrumbs">
   <div class="inner">
      <ul class="quick_links">

      {if $QUICK_TOUR}
      <li><a href="#" id="quickTour">{$LANG.dashboard.quick_tour}</a></li>
      {/if}
      {if isset($PERFORMANCE)}
      <li id="performance_tile" class="perf-score-{$PERFORMANCE.enabled_count}"><a href="?_g=performance"><i class="fa fa-tachometer" aria-hidden="true"></i> Performance <span class="perf-badge">{$PERFORMANCE.enabled_count}/3</span></a></li>
      {/if}
      <li><a href="index.php" target="_blank">{$LANG.settings.store_status} - {if ($STORE_STATUS)}<span class="store_open">{$LANG.common.open}</span>{else}<span class="store_closed">{$LANG.common.closed}</span>{/if}</a></li>
      <li id="help_menu"><a href="#"><i class="fa fa-life-ring" aria-hidden="true"></i> {$LANG.common.help}</a>
         <ul>
            {if $HELP_URL}<li><a href="{$HELP_URL}" id="wikihelp" class="help-panel-trigger"><i class="fa fa-book" aria-hidden="true"></i>{$LANG.common.this_page}</a></li>{/if}
            <li><a href="https://www.cubecart.com/technical-support" target="_blank"><i class="fa fa-comments-o" aria-hidden="true"></i>{$LANG.common.tech_support}</a></li>
            <li><a href="https://community.cubecart.com" target="_blank"><i class="fa fa-users" aria-hidden="true"></i>{$LANG.common.community}</a></li>
         </ul>
      </li>
      </ul>
      <ul class="location">
      <li><i class="fa fa-home"></i> <a href="?">{$LANG.dashboard.title_dashboard}</a></li>
      {foreach from=$CRUMBS item=crumb}<li><i class="fa fa-chevron-right"></i> <a href="{$crumb.url}">{$crumb.title}</a></li>{/foreach}
      </ul>
   </div>
</div>