{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Recently viewed products. ENTIRELY CLIENT-SIDE: the list lives in the
 * visitor's own localStorage and is never posted anywhere, so it needs no core
 * change, no database table, no session and no cookie banner. A shared computer
 * keeps it to that browser profile, which is where it belongs.
 *
 * Two jobs, both optional and independent (js/src/42-recent.js):
 *   record  — #cc-recent-record carries THIS product as JSON. Product page only.
 *   render  — #cc-recent is filled from storage, or stays empty and hidden.
 *
 * Include with:  {include file='templates/element.recently_viewed.php'}
 * and on the product page also pass  record=true.
 *
 * The heading is rendered here rather than in JS so it stays translated; the
 * script reveals the section only once it has something to put in it.
 *}
{if !isset($SKIN_SETTINGS.show_recently_viewed) || $SKIN_SETTINGS.show_recently_viewed}
{if isset($record) && $record && !empty($PRODUCT.product_id)}
{* Server-rendered JSON, so the stored name and price are already translated and
   currency-formatted. json_encode via Smarty would need a modifier; these are
   four scalars, so |escape:'html' on each is simpler and safer. *}
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
   {* Same column counts as the category and homepage grids, so this reads as
      one more product row rather than a different component. How many are
      actually visible is CSS's job, not the script's — see .cc-recent-row. *}
   <ul role="list" id="cc-recent-list" class="cc-recent-row grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-3 xl:grid-cols-4"></ul>
</section>
{/if}
