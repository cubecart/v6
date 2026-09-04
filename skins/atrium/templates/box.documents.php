{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * From GUI::_displayDocuments(): $DOCUMENTS is a DATA array of
 * {doc_url, doc_name, doc_url_openin}, $DOCUMENTS_LIST_HOOKS lets plugins
 * append {href, title} rows. The contact page is a document now, so it
 * arrives in $DOCUMENTS like any other (#4243).
 *}
<div id="box-documents">
   <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.common.information}</h3>
   <nav class="mt-4">
      <ul class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-3">
         {foreach from=$DOCUMENTS item=document}
         <li><a href="{$document.doc_url}" title="{$document.doc_name}"{if $document.doc_url_openin} target="_blank" rel="noopener"{/if} class="text-ink-600 hover:text-ink-900">{$document.doc_name}</a></li>
         {/foreach}
         {foreach from=$DOCUMENTS_LIST_HOOKS item=list_item}
         <li><a href="{$list_item.href}" title="{$list_item.title}" class="text-ink-600 hover:text-ink-900">{$list_item.title}</a></li>
         {/foreach}
      </ul>
   </nav>
</div>
