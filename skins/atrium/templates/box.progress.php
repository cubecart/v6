{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_checkoutProcess(), which assigns
 * the rendered result to $CHECKOUT_PROGRESS — main.php prints that variable, it
 * does not include this file.
 *
 * $BLOCKS items: {step, title, url, class}. Core sets `class` (current /
 * previous / next / last) and drops the payment block when the skin's
 * <minVersion> is >= 6.0.0a, which Atrium satisfies — so expect three steps,
 * not four. The value is printed through unchanged so a plugin adding its own
 * state class still has something to hook.
 *}
{if isset($BLOCKS)}
<nav id="box-progress" class="mb-8" aria-label="{$LANG.checkout.checkout|default:'Checkout'}">
   <ol class="flex items-center gap-2 text-sm sm:gap-4">
      {foreach from=$BLOCKS item=block name=blocks}
      <li class="checkout-progress {$block.class} flex min-w-0 flex-1 items-center gap-2">
         <a href="{$block.url}" class="flex min-w-0 items-center gap-2">
            <span class="flex size-7 shrink-0 items-center justify-center rounded-full border text-xs font-semibold
                         {if $block.class == 'current'}border-brand-600 bg-brand-600 text-white{else}border-ink-300 text-ink-500{/if}">{$block.step}</span>
            <span class="truncate {if $block.class == 'current'}font-medium text-ink-900{else}text-ink-500{/if}">{$block.title}</span>
         </a>
         {if !$smarty.foreach.blocks.last}
         <span class="h-px flex-1 bg-ink-200" aria-hidden="true"></span>
         {/if}
      </li>
      {/foreach}
   </ol>
</nav>
{/if}
