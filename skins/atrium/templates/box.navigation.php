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
      <li class="cc-nav-item">
         <a href="{$ROOT_PATH}" title="{$LANG.common.home}" class="cc-nav-link">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
               <path d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="cc-sr-only">{$LANG.common.home}</span>
         </a>
      </li>
      {$NAVIGATION_TREE}
      {if $CTRL_CERTIFICATES && !$CATALOGUE_MODE}
      <li class="cc-nav-item"><a href="{$URL.certificates}" title="{$LANG.navigation.giftcerts}" class="cc-nav-link"><span>{$LANG.navigation.giftcerts}</span></a></li>
      {/if}
      {if $CTRL_SALE}
      <li class="cc-nav-item"><a href="{$URL.saleitems}" title="{$LANG.navigation.saleitems}" class="cc-nav-link !text-danger-600"><span>{$LANG.navigation.saleitems}</span></a></li>
      {/if}
   </ul>
</nav>
{/if}
