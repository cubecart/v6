{if $CRUMBS}
<ul class="breadcrumbs">
   <li><a href="{$STORE_URL}"><span class="show-for-small-only"><i class="fa fa-home"></i></span><span class="show-for-medium-up">{$LANG.common.home}</a></li>
   {foreach from=$CRUMBS item=crumb}
   <li><a href="{$crumb.url}">{$crumb.title}</a></li>
   {/foreach}
</ul>
{else}
<div class="thickpad-top"></div>
{/if}
