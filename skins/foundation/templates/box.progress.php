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
{if isset($BLOCKS)}
<ul class="row no-bullet collapse checkout-progress-wrapper">
  {foreach from=$BLOCKS item=block}
  <li class="small-4 columns text-center checkout-progress {$block.class}"><a href="{$block.url}">{$block.step}. {$block.title}</a></li>
  {/foreach}
</ul>
 {/if}