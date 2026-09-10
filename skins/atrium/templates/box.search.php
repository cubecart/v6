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
 * Rendered TWICE below lg: the inline desktop box and the panel the header's
 * magnifier reveals. Pass uid to the second one ({include ... uid='mobile'})
 * and every id here gains that suffix, so the label/input pairing and the
 * listbox reference stay unambiguous. The input NAME is deliberately the same
 * in both: they are separate forms, and the name is part of the contract above.
 *}
{if isset($uid) && $uid}{assign var='cc_s_uid' value="-`$uid`"}{else}{assign var='cc_s_uid' value=''}{/if}
<div class="w-full" x-data="ccSearch('{$cc_s_uid}')" @click.outside="close()" @keydown.escape.window="close()">
   <form action="{$STORE_URL}/search{$CONFIG.seo_ext}" class="search_form relative" method="get" role="search">
      <label for="cc-search-input{$cc_s_uid}" class="cc-sr-only">{$LANG.common.search}</label>
      <input id="cc-search-input{$cc_s_uid}"
             name="search[keywords]"
             type="search"
             x-ref="input"
             x-model="term"
             @input.debounce.300ms="go()"
             @focus="go()"
             {* Combobox keyboard contract. .prevent on the arrows stops the
                caret jumping to the ends of the term while you pick a row;
                Enter only preventDefaults when a row IS selected, so Enter from
                the input still submits and runs a full search. *}
             role="combobox"
             aria-autocomplete="list"
             aria-controls="sayt_results{$cc_s_uid}"
             :aria-expanded="open && results.length ? 'true' : 'false'"
             :aria-activedescendant="active >= 0 ? optionId(active) : null"
             @keydown.arrow-down.prevent="move(1)"
             @keydown.arrow-up.prevent="move(-1)"
             @keydown.enter="if (choose()) $event.preventDefault()"
             @keydown.escape="close()"
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
      <ul id="sayt_results{$cc_s_uid}" x-show="open && results.length" x-cloak
          x-transition.opacity.duration.150ms
          {* .cc-pending dims the stale list while a newer query is in flight and
             blocks clicks on rows that are about to be replaced. *}
          :class="{ 'cc-pending': busy }" :aria-busy="busy ? 'true' : 'false'"
          class="absolute inset-x-0 top-full z-40 mt-1 max-h-96 overflow-y-auto rounded-cc-lg border border-ink-200 bg-ink-100 py-1 shadow-lg"
          role="listbox">
         <template x-for="(p, i) in results" :key="p.product_id">
            <li :id="optionId(i)" role="option" :aria-selected="i === active ? 'true' : 'false'">
               {* Pointer and keyboard share one highlight, so moving the mouse
                  over the list does not leave two rows looking selected. *}
               <a :href="p.url" @mouseenter="active = i"
                  class="flex items-center gap-3 px-3 py-2 text-sm text-ink-800 hover:bg-ink-200"
                  :class="i === active ? 'bg-ink-200' : ''">
                  {* The slot is reserved whenever images are enabled, even when the product
                     has none: 308 indexed products carry no `thumbnail` field, and an x-if on
                     p.thumbnail alone rendered NOTHING for them — those rows lost the 40px
                     box and their text jumped left out of line with the rest. *}
                  <template x-if="showImages">
                     <span class="size-10 shrink-0 overflow-hidden rounded bg-ink-200">
                        {* The indexer stores a path into images/cache, which is a derived
                           artefact hosts and merchants clear routinely — and live search is
                           the one display path that never re-resolves it through imagePath(),
                           because ?_e=es is dispatched before the bootstrap and so has no
                           Catalogue to call. A cleared cache therefore 404s until someone
                           rebuilds the index. Drop the src on error so that degrades to the
                           neutral slot below rather than a broken-image icon. *}
                        <template x-if="p.thumbnail">
                           <img :src="p.thumbnail" :alt="p.name" class="cc-media size-10 object-cover" loading="lazy"
                                @error="p.thumbnail = ''">
                        </template>
                     </span>
                  </template>
                  <span x-html="p.highlighted"></span>
               </a>
            </li>
         </template>
      </ul>
      <p x-show="open && searched && !busy && !results.length" x-cloak
         class="absolute inset-x-0 top-full z-40 mt-1 rounded-cc-lg border border-ink-200 bg-ink-100 px-3 py-2 text-sm text-ink-500 shadow-lg">
         {$LANG.search.no_results|default:'No results found'}
      </p>
   </form>
</div>
