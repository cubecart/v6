{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Recently viewed. Client-side only (js/src/42-recent.js): the list lives in the
 * visitor's localStorage and is never posted anywhere.
 *
 * Include with record=true on the product page — the only page that knows the
 * product — and plain anywhere it should render. The heading is server-rendered
 * so it stays translated; the script reveals the section once it has content.
 *}
{if !isset($SKIN_SETTINGS.show_recently_viewed) || $SKIN_SETTINGS.show_recently_viewed}
{if isset($record) && $record && !empty($PRODUCT.product_id)}
{* escape:'javascript' also turns </ into <\/, so a name cannot close this tag. *}
<script type="application/json" id="cc-recent-record">{ldelim}
   "id": "{$PRODUCT.product_id}",
   "name": "{$PRODUCT.name|escape:'javascript'}",
   "url": "{$PRODUCT.url|escape:'javascript'}",
   "img": "{$PRODUCT.medium|default:$PRODUCT.small|escape:'javascript'}",
   "price": "{if $CTRL_HIDE_PRICES}{else}{if $PRODUCT.ctrl_sale}{$PRODUCT.sale_price|escape:'javascript'}{else}{$PRODUCT.price|escape:'javascript'}{/if}{/if}"
{rdelim}</script>
{/if}

<section id="cc-recent" class="mt-16 hidden" aria-labelledby="cc-recent-heading">
   <h2 id="cc-recent-heading" class="mb-6 text-xl font-semibold tracking-tight text-ink-900">
      {$LANG.catalogue.recently_viewed|default:'Recently Viewed'}
   </h2>
   {* Same columns as the other product grids; .cc-recent-row trims to one row. *}
   <ul role="list" id="cc-recent-list" class="cc-recent-row grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-3 xl:grid-cols-4"></ul>
</section>
{/if}
