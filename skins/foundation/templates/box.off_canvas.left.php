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
<aside class="left-off-canvas-menu">
   <ul class="off-canvas-list">
      {if $IS_USER}
      <li><label>{$CUSTOMER.first_name|capitalize} {$CUSTOMER.last_name|capitalize}</label></li>
      <li><a href="{$STORE_URL}/index.php?_a=profile" title="{$LANG.account.your_details}">{$LANG.account.your_details}</a></li>
      <li><a href="{$STORE_URL}/index.php?_a=vieworder" title="{$LANG.account.your_orders}">{$LANG.account.your_orders}</a></li>
      <li><a href="{$STORE_URL}/index.php?_a=addressbook" title="{$LANG.account.your_addressbook}">{$LANG.account.your_addressbook}</a></li>
      <li><a href="{$STORE_URL}/index.php?_a=downloads" title="{$LANG.account.your_downloads}">{$LANG.account.your_downloads}</a></li>
      <li><a href="{$STORE_URL}/index.php?_a=newsletter" title="{$LANG.account.your_subscription}">{$LANG.account.your_subscription}</a></li>
      {foreach from=$SESSION_LIST_HOOKS item=list_item}
      <li><a href="{$list_item.href}" title="{$list_item.title}">{$list_item.title}</a></li>
      {/foreach}
      <li><a href="{$STORE_URL}/index.php?_a=account" title="{$LANG.account.your_account}">{$LANG.common.more}&hellip;</a></li>
      <li><a href="{$STORE_URL}/index.php?_a=logout" title="{$LANG.account.logout}">{$LANG.account.logout}</a></li>
      {else}
      <li><label>{$LANG.account.your_account}</label></li>
      <li><a href="{$STORE_URL}/login.html">{$LANG.account.login} / {$LANG.account.register}</a></li>
      {/if}
   </ul>
   <ul class="off-canvas-list">
      <li><label>{$LANG.common.change_currency}</label></li>
      {foreach from=$CURRENCIES item=currency}
      {if $currency.code!==$CURRENT_CURRENCY.code}
      <li><a href="{$currency.url}">{$currency.symbol_left} {$currency.code} {$currency.symbol_right} ({$currency.name})</a></li>
      {/if}
      {/foreach}
   </ul>
   {if $LANGUAGES}
   <ul class="off-canvas-list">
      <li><label>{$LANG.common.change_language}</label></li>
      {foreach from=$LANGUAGES item=language}
      {if $current_language.code!==$language.code}
      <li><a href="{$language.url}"><span class="flag flag-{$language.code|substr:3:2}"></span> {$language.title}</a></li>
      {/if}
      {/foreach}  
   </ul>
   {/if}
</aside>
