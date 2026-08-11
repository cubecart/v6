{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY TEMPLATE. GUI::displaySideBasket() fetches it unguarded, and
 * displayCommon() calls that on nearly every page. Delete it and the whole
 * storefront throws SmartyException.
 *
 * This is also the AJAX payload: _g=ajaxadd (Cubecart::loadPage()) returns this
 * template's output verbatim from GUI::displaySideBasket(), and the skin's JS
 * swaps it into #mini-basket. Returning HTML rather than JSON is a documented
 * plugin API via the class.gui.display_side_basket hook — do not "improve" it.
 *
 * data-basket-* are read by Alpine's basket store (js/src/00-boot.js) so the
 * header count, the drawer and the mini-basket cannot desync when only one of
 * them is replaced.
 *}
{if !$CATALOGUE_MODE}
<div id="mini-basket"
     class="relative"
     data-basket-count="{$CART_ITEMS|default:0}"
     data-basket-total="{$CART_TOTAL}"
     x-data="ccDisclosure()"
     x-init="$store.basket.syncFrom($el)"
     @click.outside="close()"
     @keydown.escape.window="close()">

   <a href="{$ROOT_PATH}index.php?_a=basket"
      class="cc-btn cc-btn-secondary gap-2"
      @click.prevent="toggle()"
      :aria-expanded="open ? 'true' : 'false'"
      title="{sprintf($LANG.basket.basket_total, $CART_TOTAL)}">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
         <path d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span class="hidden sm:inline">{$CART_TOTAL}</span>
      <span class="cc-sr-only">{sprintf($LANG.basket.basket_total, $CART_TOTAL)}</span>
   </a>

   <div x-show="open" x-cloak x-transition.opacity.duration.150ms
        class="absolute end-0 z-40 mt-2 w-80 max-w-[90vw] rounded-cc-lg border border-ink-200 bg-ink-100 p-4 shadow-lg"
        role="dialog" aria-label="{$LANG.basket.shopping_basket|default:'Basket'}">
      {include file='templates/box.basket.content.php'}
   </div>

   {* GUI::display() injects the CSRF token before every </form>, but this box
      has no form. The skin's JS reads the token from here for AJAX. *}
   <input type="hidden" class="cc_session_token" name="token" value="{$SESSION_TOKEN}">
</div>
{/if}
