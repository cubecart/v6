{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Developer skin switcher. Only rendered when the store has skin_change on.
 *
 * ⚠ ?select_skin= is NOT admin-gated — GUI::__construct() honours it from GET
 * as well as POST, so any visitor or crawler hitting such a URL switches skin
 * for their whole session. Turn skin_change OFF in production.
 *
 * Contract: <select name="select_skin"> with values "<skin>|<style>".
 *}
{if $SKINS}
<form action="{$VAL_SELF}" method="post" id="box-skin"
      class="skin_selector fixed bottom-3 end-3 z-50 rounded-cc-lg border border-ink-300 bg-ink-100 p-2 shadow-lg"
      x-data="ccDisclosure(true)" x-show="open" x-cloak>
   <div class="flex items-center gap-2">
      <label for="select_skin" class="text-xs font-medium text-ink-600">{$LANG.common.change_skin}</label>
      <select name="select_skin" id="select_skin" class="w-auto py-1 text-xs" @change="$el.form.submit()">
         {foreach from=$SKINS item=skin}
         {if isset($skin.styles)}
         {foreach from=$skin.styles item=style}
         <option value="{$skin.name}|{$style.directory}" {$style.selected}>{$skin.display} - {$style.name}</option>
         {/foreach}
         {else}
         <option value="{$skin.name}" {$skin.selected}>{$skin.display}</option>
         {/if}
         {/foreach}
      </select>
      <button type="button" @click="close()" title="{$LANG.common.close}" class="-m-1 p-1 text-ink-500 hover:text-ink-900">
         <span class="cc-sr-only">{$LANG.common.close}</span>
         <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"/>
         </svg>
      </button>
   </div>
   <noscript><button type="submit" class="cc-btn cc-btn-secondary mt-2 w-full">{$LANG.common.go|default:'Go'}</button></noscript>
</form>
{/if}
