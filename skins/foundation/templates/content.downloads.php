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
{if $IS_USER}
<h2>{$LANG.account.your_downloads}</h2>
{$PAGINATION}
<table class="show-for-medium-up">
   <thead>
      <tr>
         <td>{$LANG.account.download_filename}</td>
         <td>{$LANG.account.download_expires}</td>
         <td>{$LANG.account.download_count}</td>
         <td></td>
      </tr>
   </thead>
   <tbody>
      {foreach from=$DOWNLOADS item=download}
      <tr>
         {if $download.deleted}
         <td colspan="4">{$LANG.account.download_deleted}</td>
         {else}
         <td>
            <h5>{$download.name}</h5>
            {if $download.active}
            <a href="{$STORE_URL}/index.php?_a=download&accesskey={$download.accesskey}" title="{$LANG.common.download}"><svg class="icon"><use xlink:href="#icon-download"></use></svg> {$download.file_info.filename}</a>
            {else}
            <svg class="icon"><use xlink:href="#icon-download"></use></svg> <del>{$download.file_info.filename}</del>
            {/if}
         </td>
         <td> {if $download.active}{$download.expires}{else}{$LANG.account.download_expired}{/if}</td>
         <td class="text-center">{$download.downloads}/{$MAX_DOWNLOADS}</td>
         <td width="120"><a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$download.cart_order_id}" class="button tiny expand thinmarg-bottom" title="{$LANG.common.view_details}">{$LANG.common.view_details}</a>
            {if $download.active}<a href="{$STORE_URL}/index.php?_a=download&accesskey={$download.accesskey}" class="button tiny expand" title="{$LANG.common.view_details}">{$LANG.common.download}</a>{/if}
         </td>
         {/if}
      </tr>
      {foreachelse}
      <div>{$LANG.notification.no_downloads_available}</div>
      {/foreach}
   </tbody>
</table>
<div class="show-for-small-only">
<hr>
{foreach from=$DOWNLOADS item=download}
      <div>
         {if $download.deleted}
         {$LANG.account.download_deleted}
         {else}
         
            <h5>{$download.name}</h5>
            {if $download.active}
            <a href="{$STORE_URL}/index.php?_a=download&accesskey={$download.accesskey}" title="{$LANG.common.download}"><svg class="icon"><use xlink:href="#icon-download"></use></svg> {$download.file_info.filename}</a>
            {else}
            <svg class="icon"><use xlink:href="#icon-download"></use></svg> <del>{$download.file_info.filename}</del>
            {/if}
         <br>{$LANG.account.download_expires}: {if $download.active}{$download.expires}{else}{$LANG.account.download_expired}{/if}<br>{$LANG.account.download_count}: {$download.downloads}/{$MAX_DOWNLOADS}
         <div class="row">
         <div class="small-6 columns"><a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$download.cart_order_id}" class="button tiny expand thinmarg-bottom" title="{$LANG.common.view_details}">{$LANG.common.view_details}</a></div>
         <div class="small-6 columns">
            {if $download.active}<a href="{$STORE_URL}/index.php?_a=download&accesskey={$download.accesskey}" class="button tiny expand" title="{$LANG.common.view_details}">{$LANG.common.download}</a>{/if}</div></div>
         {/if}
         </div>
         <hr>
      {foreachelse}
      <div>{$LANG.notification.no_downloads_available}</div>
      {/foreach}
</div>
{$PAGINATION}
{else}
<form action="{$VAL_SELF}" method="post">
   <h2>{$LANG.catalogue.redeem_download_code}</h2>
   <fieldset>
      <div><label for="download-code">{$LANG.catalogue.download_access_key}</label><span><input type="text" name="accesskey" id="download-code" value=""></span></div>
   </fieldset>
   <div><input type="submit" value="{$LANG.common.submit}" class="button_submit"></div>
</form>
{/if}