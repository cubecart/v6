{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core. This is the SHARED paginator — the
 * category listing, search results, order history and downloads all render
 * through it, so keep it generic.
 *
 * Core assigns these before fetching, in Database::pagination():
 *   $page      current page number
 *   $total     total number of pages
 *   $show      how many numbered links to show around the current page
 *   $params    existing query-string params to preserve
 *   $var_name  the page parameter's name (varies by context)
 *   $current   base URL including "?"
 *   $anchor    optional #fragment
 *
 * $TOTAL_RESULTS is assigned by the same pagination() call — it is NOT
 * undefined here, despite looking that way in isolation.
 *}
{if $total > 1}
<nav class="pagination mt-8 flex items-center justify-center gap-1" aria-label="{$LANG.common.pagination|default:'Pagination'}" id="element-paginate">

   {if ($page > 1)}
   {$params[$var_name] = $page-1}
   <a href="{$current}{http_build_query($params)}{$anchor}" rel="prev"
      class="inline-flex items-center gap-1 rounded-cc px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-200">
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
         <path d="M15.75 19.5 8.25 12l7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span class="hidden sm:inline">{$LANG.common.previous}</span>
   </a>
   {/if}

   {if ($page >= $show-1)}
   {$params[$var_name] = 1}
   <a href="{$current}{http_build_query($params)}{$anchor}" class="rounded-cc px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-200">1</a>
   <span class="px-1 text-ink-400" aria-hidden="true">&hellip;</span>
   {/if}

   {for $i = 1; $i <= $total; $i++}
   {if ($i < $page - floor((float)$show / 2))}{continue}{/if}
   {if ($i > $page + floor((float)$show / 2))}{break}{/if}
   {$params[$var_name] = $i}
   {if ($i == $page)}
   <span class="current rounded-cc bg-brand-600 px-3 py-2 text-sm font-semibold text-white" aria-current="page">{$i}</span>
   {else}
   <a href="{$current}{http_build_query($params)}{$anchor}" class="rounded-cc px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-200">{$i}</a>
   {/if}
   {/for}

   {if ($i <= $total)}
   {$params[$var_name] = $total}
   <span class="px-1 text-ink-400" aria-hidden="true">&hellip;</span>
   <a href="{$current}{http_build_query($params)}{$anchor}" class="rounded-cc px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-200">{$total}</a>
   {/if}

   {if ($page < $total)}
   {$params[$var_name] = $page + 1}
   <a href="{$current}{http_build_query($params)}{$anchor}" rel="next"
      class="inline-flex items-center gap-1 rounded-cc px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-200">
      <span class="hidden sm:inline">{$LANG.common.next}</span>
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
         <path d="m8.25 4.5 7.5 7.5-7.5 7.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
   </a>
   {/if}
</nav>
{/if}
