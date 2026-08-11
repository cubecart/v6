{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_download().
 *
 * ⚠ Use {if !empty($IS_USER)} — see content.orders.php.
 *
 * $PAGINATION here uses page var `p` (not `page`) with 50 per page, and is
 * FALSE when there is a single page. Print raw, guard first.
 *
 * $download.file_info.stream marks a streamable file: ?_a=download&accesskey=…
 * with &s=1 renders main.stream.php as a page, without it the same URL
 * delivers the bytes.
 *
 * Redeem form field: accesskey.
 *}
<div class="mx-auto max-w-3xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.your_downloads}</h1>

   <form action="{$VAL_SELF}" method="post" class="cc-card mt-6 p-5">
      <label for="accesskey" class="cc-label">{$LANG.catalogue.redeem_download_code}</label>
      <div class="flex gap-2">
         <input type="text" name="accesskey" id="accesskey" class="min-w-0 flex-1" placeholder="{$LANG.catalogue.download_access_key}">
         <button type="submit" class="cc-btn cc-btn-primary shrink-0">{$LANG.common.submit}</button>
      </div>
   </form>

   {if !empty($IS_USER)}
      {if $DOWNLOADS}
      {if $PAGINATION}<div class="mt-6">{$PAGINATION}</div>{/if}

      <ul role="list" class="mt-6 space-y-4">
         {foreach from=$DOWNLOADS item=download}
         <li class="cc-card p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
               <div class="min-w-0">
                  <p class="text-sm font-semibold text-ink-900">{$download.name}</p>
                  {if !empty($download.file_info.filename)}
                  <p class="mt-1 truncate text-xs text-ink-500">{$LANG.account.download_filename}: {$download.file_info.filename}</p>
                  {/if}
                  <p class="mt-1 text-xs text-ink-500">
                     {$LANG.account.download_count}: <span class="tabular">{$download.downloads}</span>{if $MAX_DOWNLOADS} / <span class="tabular">{$MAX_DOWNLOADS}</span>{/if}
                     {if !empty($download.expires)} &middot; {$LANG.account.download_expires}: {$download.expires}{/if}
                  </p>
               </div>
               <div class="flex shrink-0 flex-wrap gap-2">
                  {if $download.deleted}
                  <span class="cc-btn cc-btn-secondary pointer-events-none opacity-60">{$LANG.account.download_deleted}</span>
                  {elseif !$download.active}
                  <span class="cc-btn cc-btn-secondary pointer-events-none opacity-60">{$LANG.account.download_expired}</span>
                  {else}
                     {if $download.file_info.stream}
                     <a href="{$STORE_URL}/index.php?_a=download&accesskey={$download.accesskey}&s=1" class="cc-btn cc-btn-secondary">{$LANG.common.view_details}</a>
                     {/if}
                  <a href="{$STORE_URL}/index.php?_a=download&accesskey={$download.accesskey}" class="cc-btn cc-btn-primary">{$LANG.common.download|default:$LANG.common.submit}</a>
                  {/if}
                  {if !empty($download.cart_order_id)}
                  <a href="{$STORE_URL}/index.php?_a=vieworder&cart_order_id={$download.cart_order_id}" class="cc-btn cc-btn-ghost">{$LANG.common.view_details}</a>
                  {/if}
               </div>
            </div>
         </li>
         {/foreach}
      </ul>

      {if $PAGINATION}<div class="mt-6">{$PAGINATION}</div>{/if}
      {else}
      <p class="mt-8 text-center text-ink-600">{$LANG.notification.no_downloads_available}</p>
      {/if}
   {/if}
</div>
