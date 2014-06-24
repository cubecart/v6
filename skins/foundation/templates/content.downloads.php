{if $IS_USER}
<h2>{$LANG.account.your_downloads}</h2>
{$PAGINATION}
<table>
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
            <h4>{$download.name}</h4>
            {if $download.active}
            <a href="{$STORE_URL}/index.php?_a=download&accesskey={$download.accesskey}" title="{$LANG.common.download}"><i class="fa fa-download"> {$download.file_info.filename}</i></a>
            {else}
            <i class="fa fa-download"> {$download.file_info.filename}</i>
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