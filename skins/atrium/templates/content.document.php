{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by core. CMS pages: terms, privacy, about.
 *
 * $DOCUMENT.doc_content is merchant-authored HTML, so it needs .prose-cc —
 * Tailwind's preflight zeroes heading and list styling, and this page is almost
 * entirely such content.
 *}
{if $DOCUMENT.hide_title==0}
<h1 class="text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">{$DOCUMENT.doc_name}</h1>
{/if}

<div class="prose-cc mt-6">{$DOCUMENT.doc_content}</div>

{if $SHARE}
<div class="mt-10 flex flex-wrap gap-2 border-t border-ink-200 pt-6">
   {foreach from=$SHARE item=html}{$html}{/foreach}
</div>
{/if}

{foreach from=$COMMENTS item=html}{$html}{/foreach}
