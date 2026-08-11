{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ KEEP THE {if $CATEGORIES} GUARD.
 * $CATEGORIES is this whole block's rendered HTML, cached SERVER-SIDE per skin
 * + language + customer group (html.<skin>.menu.*). On a cache hit core hands
 * back the finished markup and the {else} branch never runs; without the guard
 * every page re-renders the entire category tree.
 *
 * So NOTHING inside the {else} branch may be per-visitor — no customer name, no
 * basket count, no CSRF token; it would be cached and served to everyone. The
 * one sanctioned exception is the rel="..." rewrite core applies after the
 * cache read (GUI::_displayNavigation()).
 *
 * Rendered once; the mobile drawer reuses this same markup via Alpine.
 *}
{if $CATEGORIES}
{$CATEGORIES}
{else}
<nav id="box-navigation" class="cc-nav" aria-label="{$LANG.navigation.title}">
   <ul>
      {$NAVIGATION_TREE}
      {if $CTRL_CERTIFICATES && !$CATALOGUE_MODE}
      <li class="cc-nav-item"><a href="{$URL.certificates}" title="{$LANG.navigation.giftcerts}" class="cc-nav-link"><span>{$LANG.navigation.giftcerts}</span></a></li>
      {/if}
      {if $CTRL_SALE}
      {* "Sale", not "Sale Items": common.sale is new in 6.8.0, so a translated
         pack that predates it has no key yet — hence the fallback to the
         long-standing navigation.saleitems string rather than an empty label.
         Uppercased in CSS, not with |upper, which mangles non-Latin scripts. *}
      <li class="cc-nav-item"><a href="{$URL.saleitems}" title="{$LANG.common.sale|default:$LANG.navigation.saleitems}" class="cc-nav-link cc-nav-sale font-bold uppercase tracking-wide"><span>{$LANG.common.sale|default:$LANG.navigation.saleitems}</span></a></li>
      {/if}
   </ul>
</nav>
{/if}
