{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div id="stock_log" class="tab_content">
<h3>{$LANG.catalogue.stock_log_title}</h3>

<form action="{$VAL_SELF}" method="post" class="stocklog-filter ignore-dirty">
  <input type="hidden" name="filter_submit" value="1">
  {if $FILTER.product_id}
  <span class="stocklog-pinned">{$LANG.common.product}: <strong>{$FILTER.product_name|escape:'html'}</strong></span>
  {else}
  <input type="text" name="q" class="textbox q" placeholder="{$LANG.catalogue.stock_log_search}" value="{$FILTER.q|escape:'html'}">
  {/if}
  <select name="source" class="textbox">
    {foreach from=$SOURCE_OPTIONS item=opt}
    <option value="{$opt.value}"{if $opt.value eq $FILTER.source} selected="selected"{/if}>{$opt.label}</option>
    {/foreach}
  </select>
  <label for="stocklog_from">{$LANG.catalogue.stock_log_from}</label>
  <input type="date" name="date_from" id="stocklog_from" class="textbox" value="{$FILTER.date_from|escape:'html'}">
  <label for="stocklog_to">{$LANG.catalogue.stock_log_to}</label>
  <input type="date" name="date_to" id="stocklog_to" class="textbox" value="{$FILTER.date_to|escape:'html'}">
  <input type="submit" value="{$LANG.common.go}" class="tiny">
  {if $FILTER.active}<a href="?_g=products&amp;node=stocklog&amp;reset_filter=1">{$LANG.common.reset}</a>{/if}
</form>

{if isset($STOCK_LOG)}
<table class="stocklog">
  <thead>
    <tr>
      <td width="150">{$LANG.common.date}</td>
      <td>{$LANG.common.product}</td>
      <td width="70" class="text-center">{$LANG.catalogue.stock_log_change}</td>
      <td width="80" class="text-center">{$LANG.catalogue.stock_log_after}</td>
      <td width="110">{$LANG.catalogue.stock_log_source}</td>
      <td width="110">{$LANG.common.order_id}</td>
      <td>{$LANG.common.notes}</td>
    </tr>
  </thead>
  <tbody>
  {foreach from=$STOCK_LOG item=entry}
    <tr>
      <td class="stocklog-time">{$entry.time}</td>
      <td>
        {if $entry.product_gone}
        <span class="stocklog-gone">{$entry.product_name|escape:'html'}</span>
        {else}
        <a href="?_g=products&amp;node=index&amp;action=edit&amp;product_id={$entry.product_id}">{$entry.product_name|escape:'html'}</a>
        {if $entry.product_code} <small>({$entry.product_code|escape:'html'})</small>{/if}
        {/if}
        {if $entry.variant}<br><small class="stocklog-variant">{$entry.variant|escape:'html'}</small>{/if}
      </td>
      <td class="text-center"><span class="stocklog-change stocklog-change--{$entry.change_class}">{$entry.change}</span></td>
      <td class="text-center">{$entry.stock_after}</td>
      <td><span class="errorlog-badge errorlog-badge--gray">{$entry.source_label}</span></td>
      <td>{if $entry.cart_order_id}<a href="?_g=orders&amp;action=edit&amp;order_id={$entry.cart_order_id|escape:'url'}">{$entry.cart_order_id|escape:'html'}</a>{else}&mdash;{/if}</td>
      <td class="stocklog-note">
        {$entry.note|escape:'html'}
        {if $entry.admin}<br><small>{$entry.admin|escape:'html'}</small>{/if}
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
<div class="pagination">{$PAGINATION}</div>
{else}
<p>{$LANG.form.none}</p>
{/if}
</div>
