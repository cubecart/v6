{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Quick-view fragment. Rendered by the `quickview` case in Cubecart::loadPage(),
 * which calls Catalogue::displayProduct() with this file instead of
 * content.product.php, so every $PRODUCT / $GALLERY / options variable is
 * exactly what the product page gets.
 *
 * ⚠ NOT a page. No <html>, no breadcrumb, no tabs, no reviews. It is injected
 * into the modal in main.php and Alpine initialises the components inside it.
 *
 * ⚠ It carries the SAME ids as the product page (#ptp, #img-preview,
 * #cc-main-buy and so on) because it includes the same element templates. That
 * is safe only while one fragment exists at a time, which is why the modal
 * clears its html on close rather than hiding it. Two copies and every
 * getElementById in 20-product.js finds the wrong one.
 *
 * Deliberately omitted: the sticky buy bar (product page only) and the tabs.
 *}
{* The action MUST be the product URL, not index.php. ccAddToBasket strips the
   query and appends _g=ajaxadd, and core needs the product context from the URL:
   posting to index.php returns "Redir:<product url>" and adds nothing. *}
<form action="{$PRODUCT.url}" method="post" class="add_to_basket"
      x-data="ccAddToBasket()" @submit="submit($event)">
   <div x-data="ccProduct()" class="sm:flex sm:gap-6">

      <div class="sm:w-1/2 sm:shrink-0">
         {include file='templates/element.product.gallery.php'}
      </div>

      <div class="mt-4 min-w-0 sm:mt-0 sm:flex-1">
         <h2 class="text-lg font-semibold tracking-tight text-ink-900">
            <a href="{$PRODUCT.url}" class="hover:underline">{$PRODUCT.name}</a>
         </h2>
         {if !empty($PRODUCT.product_code)}
         <p class="mt-1 text-sm text-ink-500">{$LANG.catalogue.product_code}: {$PRODUCT.product_code}</p>
         {/if}

         {include file='templates/element.product.call_to_action.php'}

         {include file='templates/element.product.options.php'}

         <p class="mt-6 text-sm">
            <a href="{$PRODUCT.url}" class="underline">{$LANG.common.view_details|default:$LANG.catalogue.product_details}</a>
         </p>
      </div>
   </div>
</form>
