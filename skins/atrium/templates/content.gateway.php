{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core. This is the payment hand-off; a
 * mistake here loses revenue and fails silently.
 *
 * Three transfer modes ($TRANSFER.mode):
 *   automatic  post to the gateway immediately, no customer action
 *   manual     show the gateway's own form and let the customer submit
 *   iframe     embed the gateway
 *
 * ⚠ THE AUTOMATIC REDIRECT MUST FIRE. It is the x-init on #gateway-transfer
 * that submits, with the <noscript> button as the only fallback. Remove either
 * and the customer is stranded before payment with no error. The .icon-submit
 * class on the spinner is legacy: no Atrium JS binds to it.
 *
 * ⚠ $FORM_VARS are the gateway's signed fields. Print them exactly; do not
 * reorder, re-encode or add to them, or signatures fail.
 *
 * ⚠ $FORM_TEMPLATE and $IFRAME_FORM are gateway-supplied HTML rendered from the
 * module's own template. It will not carry Atrium classes — that is expected.
 *}
{if !isset($TRANSFER)}
<h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.gateway.select}</h1>
<form id="gateway-select" action="{$VAL_SELF}" method="post" class="mt-6 max-w-lg">
   {if $GATEWAYS}
   <ul class="space-y-2">
      {foreach from=$GATEWAYS item=gateway}
      <li class="flex items-center gap-2 rounded-cc border border-ink-200 p-3">
         <input name="gateway" type="radio" value="{$gateway.folder}" id="{$gateway.folder}" {$gateway.checked}>
         <label for="{$gateway.folder}" class="flex-1 text-sm text-ink-800">{$gateway.description}</label>
         {if !empty($gateway.help)}
         <a href="{$gateway.help}" class="info text-ink-500" title="{$LANG.common.information}" target="_blank" rel="noopener">?</a>
         {/if}
      </li>
      {/foreach}
   </ul>
   <button type="submit" class="cc-btn cc-btn-primary mt-6 w-full">{$LANG.common.continue}</button>
   {else}
   <p class="text-ink-600">{$LANG.gateway.none_defined}</p>
   {/if}
</form>
{/if}

{if isset($TRANSFER)}
{if $TRANSFER.mode == 'iframe'}
<div class="gateway_wrapper">
   <iframe src="{$IFRAME_SRC}" frameborder="0" scrolling="auto" width="100%" height="500" title="{$LANG.gateway.select}"></iframe>
   {$IFRAME_FORM}
</div>
{else}
<div class="gateway_wrapper">
<form id="gateway-transfer" action="{$TRANSFER.action}" method="{$TRANSFER.method}" target="{$TRANSFER.target}"
      {if $TRANSFER.mode == 'automatic'}x-data x-init="$nextTick(() => $el.submit())"{/if}>
   {foreach from=$FORM_VARS key=name item=value}<input type="hidden" name="{$name}" value="{$value}">
   {/foreach}

   {if $TRANSFER.mode == 'automatic'}
   <div class="py-16 text-center">
      <p class="text-ink-700">{$LANG.gateway.transferring}</p>
      <svg class="icon-submit mx-auto mt-4 size-8 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
         <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/>
         <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
      </svg>
      {* Only path to payment with JS off. *}
      <noscript>
         <button type="submit" class="cc-btn cc-btn-primary mt-6">{$BTN_PROCEED}</button>
      </noscript>
   </div>

   {elseif $TRANSFER.mode == 'manual'}
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.gateway.amount_due}</h1>
   <p class="price mt-2 text-2xl font-semibold text-ink-900">{$LANG_AMOUNT_DUE}</p>
   <div class="mt-6">{$FORM_TEMPLATE}</div>
   {/if}

   {if !$DISPLAY_3DS && $TRANSFER.mode != 'automatic'}
   <div class="mt-6 text-center">
      <button type="submit" class="cc-btn cc-btn-primary min-w-48">{$BTN_PROCEED}</button>
   </div>
   {/if}

   {foreach from=$AFFILIATES item=affiliate}{$affiliate}{/foreach}
</form>
</div>
{/if}
{/if}
