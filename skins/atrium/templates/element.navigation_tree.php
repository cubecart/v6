{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ ONE node of the category tree, rendered recursively BY PHP, not Smarty
 * (GUI::_makeTree()): children are fetched through this same template FIRST and
 * handed to the parent as a finished HTML string in {$BRANCH.children}. The
 * fetch is templateExists()-guarded, so this file is optional.
 *
 * $BRANCH = {name, cat_id, cat_level, product_count, url}
 *
 * Because children render before parents, a template cannot know its own depth
 * at render time. Emit identical markup at every level and do depth-dependent
 * styling with CSS descendant selectors (.cc-nav, css/src/components.css). Do
 * not branch on $BRANCH.cat_level — its base value is not contracted.
 *
 * This output is baked into the server-side $CATEGORIES cache (see
 * box.navigation.php), so nothing here may be per-visitor. x-data is safe:
 * Alpine initialises from the DOM.
 *}
{if isset($BRANCH.children)}
<li class="cc-nav-item cc-nav-parent" x-data="ccDisclosure()"
    @mouseenter="open = true" @mouseleave="close()"
    @keydown.escape.window="close()">
   <a href="{$BRANCH.url}" title="{$BRANCH.name}" class="cc-nav-link"
      :aria-expanded="open ? 'true' : 'false'">
      <span>{$BRANCH.name}</span>
      <svg class="cc-nav-caret" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
         <path d="m19.5 8.25-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
   </a>
   <ul class="cc-nav-menu" x-show="open" x-cloak x-transition.opacity.duration.150ms>
      {$BRANCH.children}
   </ul>
</li>
{else}
<li class="cc-nav-item">
   <a href="{$BRANCH.url}" title="{$BRANCH.name}" class="cc-nav-link"><span>{$BRANCH.name}</span></a>
</li>
{/if}
