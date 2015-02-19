<div id="breadcrumbs">
   <div class="inner">
      <ul class="quick_links">
      {if $QUICK_TOUR}
      <li><a href="#" id="quickTour">{$LANG.dashboard.quick_tour}</a></li>
      {/if}
      <li><a href="{$HELP_URL}" id="wikihelp" class="colorbox wiki">{$LANG.common.help}</a></li>
      <li><a href="index.php" target="_blank">{$LANG.settings.store_status} - {if ($STORE_STATUS)}<span class="store_open">{$LANG.common.open}</span>{else}<span class="store_closed">{$LANG.common.closed}</span>{/if}</a></li>
      </ul>
      <ul class="location">
      <li><i class="fa fa-home"></i> <a href="?">{$LANG.dashboard.title_dashboard}</a></li>
      {if isset($CRUMBS)}{foreach from=$CRUMBS item=crumb}<li><i class="fa fa-chevron-right"></i> <a href="{$crumb.url}">{$crumb.title}</a></li>{/foreach}{/if}
      </ul>
   </div>
</div>