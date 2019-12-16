{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div class="box-basket-content">
   <h4 class="mini-basket-title nomarg pad-side">{$LANG.basket.your_basket}</h4>
   <div class="pad basket-detail">
      {if isset($CONTENTS) && count($CONTENTS) > 0}
      <ul class="no-bullet">
      {foreach from=$CONTENTS item=item name=items}
      {if $smarty.foreach.items.index == 10}
         <li class="clearfix"><div class="left">&hellip;</div></li>
         {break}
      {/if}
         <li class="clearfix">
            <div class="left"><a href="{$item.link}" title="{$item.name}">{$item.quantity} &times; {$item.name|truncate:25:"&hellip;"}</a></div>
            <div class="right">{$item.total}</div>
         </li>
      {/foreach}
         <li class="clearfix">
            <hr>
            <div class="left">{$LANG.common.item_plural}:</div>
            <div class="right">{$CART_ITEMS}</div>
         </li>
         <li class="clearfix">
            <div class="left total">{$LANG.basket.total}:</div>
            <div class="right total">{$CART_TOTAL}</div>
         </li>
      </ul>
      {if !$HIDE_CHECKOUT_BUTTON || $IS_USER}
      <div><a href="{$STORE_URL}/index.php?_a=checkout" class="button success expand nomarg">{if $CONFIG.ssl == 1}<svg class="icon"><use xlink:href="#icon-lock"></use></svg>{$LANG.basket.basket_secure_checkout}{else}{$LANG.basket.basket_checkout}{/if}</a></div>
      {/if}
      {if !$IS_USER}
      <div class="thinpad-top"><a href="{$STORE_URL}/index.php?_a=basket" class="button expand nomarg">{$LANG.basket.view_basket}</a></div>
      {/if}
      {else}
      <p class="pad-top text-center">{$LANG.basket.basket_is_empty}</p>
      {/if}
   </div>
</div>