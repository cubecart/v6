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

   {* Below sm the link is left alone and the tap goes to the basket page. A
      dropdown there means a list, a subtotal and a checkout button competing
      for the short half of a phone screen, and the checkout button is the one
      that loses. Only sm and up gets the preview panel. *}
   <a href="{$ROOT_PATH}index.php?_a=basket"
      class="cc-btn cc-btn-secondary gap-2"
      @click="if (window.matchMedia('(min-width: 40rem)').matches) { $event.preventDefault(); toggle(); }"
      :aria-expanded="open ? 'true' : 'false'"
      title="{sprintf($LANG.basket.basket_total, $CART_TOTAL)}">
      {* Two icons, one shown. The initial state is Smarty's, so it is right with
         JS off and before Alpine boots; the :class bindings then keep it in step
         with the store after an AJAX add. Toggling `hidden` rather than x-show
         for the same reason: no cloak flash. *}
      <span class="relative inline-flex">
         <span class="{if !$CART_ITEMS}hidden{/if}" :class="{ 'hidden': !$store.basket.count }">
            {include file='templates/element.icon.basket.php' variant='solid'}
         </span>
         <span class="{if $CART_ITEMS}hidden{/if}" :class="{ 'hidden': $store.basket.count > 0 }">
            {include file='templates/element.icon.basket.php'}
         </span>
         {* The count badge is the only basket signal below sm, where the total
            beside it is hidden. Merchants can turn it off; the filled icon
            still says the basket is not empty. *}
         {if !isset($SKIN_SETTINGS.show_basket_count) || $SKIN_SETTINGS.show_basket_count}
         <span class="absolute -end-1.5 -top-1.5 min-w-4 rounded-full bg-brand-600 px-1 text-center text-[10px] font-semibold leading-4 cc-on-brand{if !$CART_ITEMS} hidden{/if}"
               :class="{ 'hidden': !$store.basket.count }"
               x-text="$store.basket.count || '{$CART_ITEMS|default:0}'"
               aria-hidden="true">{$CART_ITEMS|default:0}</span>
         {/if}
      </span>
      {if !isset($SKIN_SETTINGS.show_basket_total) || $SKIN_SETTINGS.show_basket_total}
      <span class="hidden sm:inline">{$CART_TOTAL}</span>
      {/if}
      <span class="cc-sr-only">{sprintf($LANG.basket.basket_total, $CART_TOTAL)}</span>
   </a>

   {* Column + max-height: the item list scrolls, the subtotal and the button
      never leave the viewport however full the basket is. *}
   {* hidden below sm so it cannot be reached at all there, whatever opens the
      store's `open` state. dvh, not vh: on mobile browsers vh is the LARGEST
      viewport, the one with the toolbars hidden, so a vh-capped panel still
      runs under the address bar. The 5rem covers the header plus a margin. *}
   <div x-show="open" x-cloak x-transition.opacity.duration.150ms
        class="absolute end-0 z-40 mt-2 hidden max-h-[calc(100dvh-5rem)] w-80 max-w-[90vw] flex-col rounded-cc-lg border border-ink-200 bg-ink-100 p-4 shadow-lg sm:flex"
        role="dialog" aria-label="{$LANG.basket.shopping_basket|default:'Basket'}">
      {include file='templates/box.basket.content.php'}
   </div>

   {* GUI::display() injects the CSRF token before every </form>, but this box
      has no form. The skin's JS reads the token from here for AJAX. *}
   <input type="hidden" class="cc_session_token" name="token" value="{$SESSION_TOKEN}">
</div>
{/if}
