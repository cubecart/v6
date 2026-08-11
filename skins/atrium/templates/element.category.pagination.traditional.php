{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Per-page selector plus the rendered paginator.
 *
 * $PAGINATION is already-rendered HTML from element.paginate.php — print it,
 * do not {include} the paginator again.
 * $PAGE_SPLITS comes from the <layout><products><perpage> entries in config.xml,
 * read by GUI::listSkins() and served by GUI::perPageSplits(); change the
 * amounts there, not here.
 *}
{if isset($PAGINATION) && !empty($PAGINATION)}
<div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-ink-200 pt-6">
   {if isset($PAGE_SPLITS) && $PAGE_SPLITS}
   <label class="flex items-center gap-2 text-sm text-ink-600">
      <span class="shrink-0">{$LANG.common.per_page}</span>
      {* Alpine rather than an inline onchange= — inline handlers are the first
         thing a merchant's Content-Security-Policy blocks. *}
      <select class="url_select w-auto py-1.5 text-sm" x-data @change="if ($el.value) window.location = $el.value">
         {foreach from=$PAGE_SPLITS item=page_split}
         <option value="{$page_split.url}"{if $page_split.selected} selected{/if}>{$page_split.amount}</option>
         {/foreach}
      </select>
   </label>
   {/if}
   <div class="ms-auto">{$PAGINATION}</div>
</div>
{/if}
