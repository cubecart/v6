{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ Form contract — none of these may change:
 *   action  {$STORE_URL}/search{$CONFIG.seo_ext}, method GET
 *   name="search[keywords]"   the search term
 *   hidden _a=category        required by the search controller
 *   class="search_input"      hooked by plugins
 *   class="es"                added only when Elasticsearch is on; the SAYT
 *                             component treats it as the "live search enabled"
 *                             flag
 *   data-amount / data-image  SAYT result count and whether to show thumbnails
 *
 * Rendered once per page — a second copy would duplicate the input name.
 *}
<div class="w-full" x-data="ccSearch()" @click.outside="close()" @keydown.escape.window="close()">
   <form action="{$STORE_URL}/search{$CONFIG.seo_ext}" class="search_form relative" method="get" role="search">
      <label for="cc-search-input" class="cc-sr-only">{$LANG.common.search}</label>
      <input id="cc-search-input"
             name="search[keywords]"
             type="search"
             x-ref="input"
             x-model="term"
             @input.debounce.300ms="go()"
             @focus="go()"
             autocomplete="off"
             data-image="true"
             data-amount="15"
             class="search_input w-full ps-4 pe-11{if $CONFIG.elasticsearch=='1'} es{/if}"
             placeholder="{$LANG.search.input_default}"
             required>
      <button type="submit" title="{$LANG.common.search}"
              class="absolute inset-y-0 end-0 flex w-11 items-center justify-center rounded-e-cc text-ink-500 hover:text-ink-800">
         <span class="cc-sr-only">{$LANG.common.search}</span>
         <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" stroke-linecap="round" stroke-linejoin="round"/>
         </svg>
      </button>
      <input type="hidden" name="_a" value="category">

      {* Search-as-you-type results. Only ever populated when Elasticsearch is
         enabled — ccSearch() bails otherwise. *}
      <ul id="sayt_results" x-show="open && results.length" x-cloak
          x-transition.opacity.duration.150ms
          class="absolute inset-x-0 top-full z-40 mt-1 max-h-96 overflow-y-auto rounded-cc-lg border border-ink-200 bg-ink-100 py-1 shadow-lg"
          role="listbox">
         <template x-for="p in results" :key="p.product_id">
            <li>
               <a :href="p.url" class="flex items-center gap-3 px-3 py-2 text-sm text-ink-800 hover:bg-ink-200">
                  {* The slot is reserved whenever images are enabled, even when the product
                     has none: 308 indexed products carry no `thumbnail` field, and an x-if on
                     p.thumbnail alone rendered NOTHING for them — those rows lost the 40px
                     box and their text jumped left out of line with the rest. *}
                  <template x-if="showImages">
                     <span class="size-10 shrink-0 overflow-hidden rounded bg-ink-200">
                        <template x-if="p.thumbnail">
                           <img :src="p.thumbnail" :alt="p.name" class="size-10 object-cover" loading="lazy">
                        </template>
                     </span>
                  </template>
                  <span x-html="p.highlighted"></span>
               </a>
            </li>
         </template>
      </ul>
      <p x-show="open && searched && !results.length" x-cloak
         class="absolute inset-x-0 top-full z-40 mt-1 rounded-cc-lg border border-ink-200 bg-ink-100 px-3 py-2 text-sm text-ink-500 shadow-lg">
         {$LANG.search.no_results|default:'No results found'}
      </p>
   </form>
</div>
