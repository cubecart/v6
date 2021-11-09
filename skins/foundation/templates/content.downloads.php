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
            <a href="{$STORE_URL}/index.php?_a=download&s={$download.file_info.stream}&accesskey={$download.accesskey}" title="{$download.action}"><svg class="icon"><use xlink:href="#icon-download"></use></svg> {$download.file_info.filename}</a>
            {else}
            <svg class="icon"><use xlink:href="#icon-download"></use></svg> <del>{$download.file_info.filename}</del>
            {/if}
         </td>
         <td> {if $download.active}{$download.expires}{else}{$LANG.account.download_expired}{/if}</td>
         <td class="text-center">{$download.downloads}/{$MAX_DOWNLOADS}</td>
         <td width="120"><a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$download.cart_order_id}" class="button tiny expand thinmarg-bottom" title="{$LANG.common.view_details}">{$LANG.common.view_details}</a>
            {if $download.active}<a href="{$STORE_URL}/index.php?_a=download&s={$download.file_info.stream}&accesskey={$download.accesskey}" class="button tiny expand" title="{$download.action}">{$download.action}</a>{/if}
         </td>
         {/if}
      </tr>
      {foreachelse}
      <div>{$LANG.notification.no_downloads_available}</div>
      {/foreach}
   </tbody>
</table>
<div class="show-for-small-only">
{foreach from=$DOWNLOADS item=download}
         {if $download.deleted}
         <p>{$LANG.account.download_deleted}</p>
         {else}
         <table class="expand">
            <thead>
               <tr>
                  <th colspan="2">{$download.name}</th>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td colspan="2">
                  {if $download.active}
                     <a href="{$STORE_URL}/index.php?_a=download&s={$download.file_info.stream}&accesskey={$download.accesskey}" title="{$download.action}"><svg class="icon"><use xlink:href="#icon-download"></use></svg> {$download.file_info.filename}</a>
                  {else}
                     <svg class="icon"><use xlink:href="#icon-download"></use></svg> <del>{$download.file_info.filename}</del>
                  {/if}
                  </td>
               </tr>
               <tr>
                  <th width="50%">{$LANG.account.download_expires}</th>
                  <th width="50%">{$LANG.account.download_count}</th>
               </tr>
               <tr>
                  <td width="50%">{if $download.active}{$download.expires}{else}{$LANG.account.download_expired}{/if}</td>
                  <td width="50%">{$download.downloads}/{$MAX_DOWNLOADS}</td>
               </tr>
               <tr>
                  <td width="50%"><a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$download.cart_order_id}" class="button tiny expand nomarg" title="{$LANG.common.view_details}">{$LANG.common.view_details}</a></td>
                  <td width="50%">{if $download.active}<a href="{$STORE_URL}/index.php?_a=download&s={$download.file_info.stream}&accesskey={$download.accesskey}" class="button tiny expand nomarg" title="{$download.action}">{$download.action}</a>{/if}</td>
               </tr>
            </tbody>
         </table>
         {/if}
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