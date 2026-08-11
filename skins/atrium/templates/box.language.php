{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ THIS TEMPLATE MUST EXIST even on a single-language store.
 * GUI::_displayLanguageSwitch() only assigns $LANGUAGES when it can render this
 * file, and element.meta.php builds the hreflang alternates from $LANGUAGES.
 * Delete this and the hreflang tags silently disappear from every page.
 *
 * $current_language is LOWER CASE — a local assigned by core, not a
 * conventional upper-case skin variable.
 *}
{if $LANGUAGES}
<div id="box-language" class="hidden md:block">
   <div x-data="ccDisclosure()" @click.outside="close()" @keydown.escape.window="close()" class="relative">
      <button type="button" @click="toggle()" :aria-expanded="open ? 'true' : 'false'"
              class="cc-btn cc-btn-ghost gap-1.5" title="{$current_language.title}" rel="nofollow">
         <img src="{$STORE_URL}/language/flags/{$current_language.code}.png" alt="" class="h-4 w-auto rounded-xs" aria-hidden="true">
         <span class="cc-sr-only">{$current_language.title}</span>
         <svg class="size-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m19.5 8.25-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
         </svg>
      </button>
      <ul x-show="open" x-cloak x-transition.opacity.duration.150ms
          class="absolute end-0 z-40 mt-2 w-52 rounded-cc-lg border border-ink-200 bg-ink-100 py-1.5 shadow-lg">
         {foreach from=$LANGUAGES item=language}
         {if $current_language.code!==$language.code}
         <li><a href="{$language.url}" title="{$language.title}" rel="nofollow"
                class="flex items-center gap-2 px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">
            <img src="{$STORE_URL}/language/flags/{$language.code}.png" alt="" class="h-4 w-auto rounded-xs" aria-hidden="true">
            {$language.title}
         </a></li>
         {/if}
         {/foreach}
      </ul>
   </div>
</div>
{/if}
