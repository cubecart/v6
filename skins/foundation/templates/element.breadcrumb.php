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
