{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 *}
{if $IS_USER}
<a href="#" data-dropdown="session" class="button white small">{$CUSTOMER.first_name} {$CUSTOMER.last_name}</a><br>
<ul id="session" data-dropdown-content class="f-dropdown">
   <li class="text-left"><a href="{$STORE_URL}/index.php?_a=profile" title="{$LANG.account.your_details}">{$LANG.account.your_details}</a></li>
   <li class="text-left"><a href="{$STORE_URL}/index.php?_a=vieworder" title="{$LANG.account.your_orders}">{$LANG.account.your_orders}</a></li>
   <li class="text-left"><a href="{$STORE_URL}/index.php?_a=addressbook" title="{$LANG.account.your_addressbook}">{$LANG.account.your_addressbook}</a></li>
   <li class="text-left"><a href="{$STORE_URL}/index.php?_a=downloads" title="{$LANG.account.your_downloads}">{$LANG.account.your_downloads}</a></li>
   <li class="text-left"><a href="{$STORE_URL}/index.php?_a=newsletter" title="{$LANG.account.your_subscription}">{$LANG.account.your_subscription}</a></li>
   {foreach from=$ACCOUNT_LIST_HOOKS item=list_item}
   <li class="text-left"><a href="{$list_item.href}" title="{$list_item.title}">{$list_item.title}</a></li>
   {/foreach}
   <li class="text-left"><a href="{$STORE_URL}/index.php?_a=account" title="{$LANG.account.your_account}">{$LANG.common.more}&hellip;</a></li>
   <li class="text-left"><a href="{$STORE_URL}/index.php?_a=logout" title="{$LANG.account.logout}">{$LANG.account.logout}</a></li>
</ul>
{else}
<a href="{$STORE_URL}/index.php?_a=login" class="button white small">{$LANG.account.login} / {$LANG.account.register}</a>
{/if}