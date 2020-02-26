<div id="breadcrumbs">
   <div class="inner">
      <ul class="quick_links">
      {if !isset($CONFIG.cid)}
      <li><i class="fa fa-server" aria-hidden="true"></i> <a href="https://hosted.cubecart.com/offer" target="_blank">Official CubeCart Hosting Offer</a></li>
      {/if}
      {if $QUICK_TOUR}
      <li><a href="#" id="quickTour">{$LANG.dashboard.quick_tour}</a></li>
      {/if}
      {if $CONFIG.cache}<li id="clear_cache_master"{if $CLEAR_CACHE} class="clear"{/if}><a href="{$SKIN_VARS.clear_cache_link}">{$LANG.maintain.cache_clear}</a></li>{/if}
      <li id="help_menu"><i class="fa fa-life-ring" aria-hidden="true"></i> <a href="#">{$LANG.common.help}</a>
         <ul>
            <li><i class="fa fa-book" aria-hidden="true"></i> <a href="{$HELP_URL}" id="wikihelp" class="colorbox wiki">{$LANG.common.this_page}</a></li>
            <li><i class="fa fa-comments-o" aria-hidden="true"></i> <a href="https://www.cubecart.com/technical-support" target="_blank">{$LANG.common.tech_support}</a></li>
            <li><i class="fa fa-users" aria-hidden="true"></i> <a href="https://forums.cubecart.com" target="_blank">CubeCart Forums</a></li>
         </ul>
      </li>
      <li><a href="index.php" target="_blank">{$LANG.settings.store_status} - {if ($STORE_STATUS)}<span class="store_open">{$LANG.common.open}</span>{else}<span class="store_closed">{$LANG.common.closed}</span>{/if}</a></li>
      </ul>
      <ul class="location">
      <li><i class="fa fa-home"></i> <a href="?">{$LANG.dashboard.title_dashboard}</a></li>
      {foreach from=$CRUMBS item=crumb}<li><i class="fa fa-chevron-right"></i> <a href="{$crumb.url}">{$crumb.title}</a></li>{/foreach}
      </ul>
   </div>
</div>