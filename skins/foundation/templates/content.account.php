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
<h2>{$LANG.account.your_account}</h2>
<p>{sprintf($LANG.account.credit_on_account, $CREDIT)}
   <a href="#" data-reveal-id="credit_modal">
      <svg class="icon"><use xlink:href="#icon-info-circle"></use></svg>
   </a>
</p>
<ul class="no-bullet small-block-grid-1 medium-block-grid-3 large-block-grid-3">
   <li><a href="{$STORE_URL}/index.php?_a=profile" title="{$LANG.account.your_details}" class="button secondary expand nomarg"><svg class="icon"><use xlink:href="#icon-user"></use></svg> {$LANG.account.your_details}</a></li>
   <li><a href="{$STORE_URL}/index.php?_a=vieworder" title="{$LANG.account.your_orders}" class="button secondary expand nomarg"><svg class="icon"><use xlink:href="#icon-truck"></use></svg> {$LANG.account.your_orders}</a></li>
   <li><a href="{$STORE_URL}/index.php?_a=addressbook" title="{$LANG.account.your_addressbook}" class="button secondary expand nomarg"><svg class="icon"><use xlink:href="#icon-book"></use></svg> {$LANG.account.your_addressbook}</a></li>
   <li><a href="{$STORE_URL}/index.php?_a=downloads" title="{$LANG.account.your_downloads}" class="button secondary expand nomarg"><svg class="icon"><use xlink:href="#icon-download"></use></svg> {$LANG.account.your_downloads}</a></li>
   <li><a href="{$STORE_URL}/index.php?_a=newsletter" title="{$LANG.account.your_subscription}" class="button secondary expand nomarg"><svg class="icon"><use xlink:href="#icon-envelope"></use></svg> {$LANG.account.your_subscription}</a></li>
   {foreach from=$ACCOUNT_LIST_HOOKS item=list_item}
   <li><a href="{$list_item.href}" title="{$list_item.title}" class="button secondary expand nomarg">{if !empty($list_item.fa)}<svg class="icon"><use xlink:href="#icon-{$list_item.fa}"></use></svg> {/if}{$list_item.title}</a></li>
   {/foreach}
   <li><a href="{$STORE_URL}/index.php?_a=logout" title="{$LANG.account.logout}" class="button secondary expand nomarg"><svg class="icon"><use xlink:href="#icon-sign-out"></use></svg> {$LANG.account.logout}</a></li>
</ul>
<div id="credit_modal" class="reveal-modal" data-reveal aria-labelledby="credit_modal" aria-hidden="true" role="dialog">
  <h2>{$LANG.account.account_credit}</h2>
  <p>{$LANG.account.credit_desc}</p>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>