{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if isset($BLOCKS)}
<ul class="row no-bullet collapse checkout-progress-wrapper" id="box-progress">
  {foreach from=$BLOCKS item=block}
  <li class="small-4 columns text-center checkout-progress {$block.class}"><a href="{$block.url}">{$block.step}. {$block.title}</a></li>
  {/foreach}
</ul>
{/if}