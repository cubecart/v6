{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * $CRUMBS is assigned by GUI::displayCommon() as an array of
 * ['url' => ..., 'title' => ...]. It is false/empty on the homepage.
 *}
{if $CRUMBS}
<nav aria-label="{$LANG.common.breadcrumb|default:'Breadcrumb'}" class="mb-6">
   <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-500">
      {* Icon, not the word: the crumb trail is chrome, and "Home" is the one
         label in it that carries no information the icon does not. The text
         stays as the accessible name and the tooltip, so nothing is lost to a
         screen reader or on hover. *}
      <li class="breadcrumb-item">
         <a href="{$STORE_URL}" class="inline-flex items-center hover:text-ink-800" title="{$LANG.common.home}">
            <span class="cc-sr-only">{$LANG.common.home}</span>
            <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
               <path d="M2.25 12 12 2.25 21.75 12M4.5 9.75v10.5h5.25V15h4.5v5.25h5.25V9.75" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
         </a>
      </li>
      {foreach from=$CRUMBS item=crumb name=crumbposition}
      <li class="breadcrumb-item{if $smarty.foreach.crumbposition.last} active{/if}">
         <svg class="mr-2 inline size-3.5 shrink-0 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m9 5 7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
         </svg>
         {if $smarty.foreach.crumbposition.last}
         <span aria-current="page">{$crumb.title}</span>
         {else}
         <a href="{$crumb.url}" class="hover:text-ink-800">{$crumb.title}</a>
         {/if}
      </li>
      {/foreach}
   </ol>
</nav>
{/if}
