{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * $GUI_MESSAGE.{error,notice,info} are arrays keyed by md5 of the message.
 *
 * ⚠ Messages are printed UNESCAPED by design. Core sanitises them and they
 * legitimately contain markup — several core messages embed an <a> link
 * ("click here to log in"). Adding |escape here silently turns those into
 * visible tag soup.
 *
 * The .cc-alert-error class is a contract, not decoration: the checkout uses it
 * to detect an error state and re-open the guest form.
 *}
{if isset($GUI_MESSAGE.error)}
<div class="cc-alert cc-alert-error mb-4" role="alert" x-data="ccAlert" x-show="shown" x-cloak>
   <div class="min-w-0 flex-1">
      <p class="font-semibold">{$LANG.gui_message.errors_detected}</p>
      <ul class="mt-1 list-disc space-y-1 ps-5">
         {foreach from=$GUI_MESSAGE.error item=error}
         <li>{$error}</li>
         {/foreach}
      </ul>
   </div>
   <button type="button" class="-m-1 shrink-0 self-start p-1" @click="dismiss()">
      <span class="cc-sr-only">{$LANG.common.close|default:'Close'}</span>
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
         <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"/>
      </svg>
   </button>
</div>
{/if}
{if isset($GUI_MESSAGE.notice)}
<div class="cc-alert cc-alert-notice mb-4" role="status" x-data="ccAlert" x-show="shown" x-cloak>
   <ul class="min-w-0 flex-1 space-y-1">
      {foreach from=$GUI_MESSAGE.notice item=notice}
      <li>{$notice}</li>
      {/foreach}
   </ul>
   <button type="button" class="-m-1 shrink-0 self-start p-1" @click="dismiss()">
      <span class="cc-sr-only">{$LANG.common.close|default:'Close'}</span>
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
         <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"/>
      </svg>
   </button>
</div>
{/if}
{if isset($GUI_MESSAGE.info)}
<div class="cc-alert cc-alert-info mb-4" role="status" x-data="ccAlert" x-show="shown" x-cloak>
   <ul class="min-w-0 flex-1 space-y-1">
      {foreach from=$GUI_MESSAGE.info item=info}
      <li>{$info}</li>
      {/foreach}
   </ul>
   <button type="button" class="-m-1 shrink-0 self-start p-1" @click="dismiss()">
      <span class="cc-sr-only">{$LANG.common.close|default:'Close'}</span>
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
         <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"/>
      </svg>
   </button>
</div>
{/if}
<noscript>
   <div class="cc-alert cc-alert-warn mb-4" role="alert">
      <p>{$LANG.catalogue.error_js_required}</p>
   </div>
</noscript>
