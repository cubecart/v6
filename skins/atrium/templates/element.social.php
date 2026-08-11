{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * $SOCIAL_LINKS from GUI::_displaySocial() is {url, name, icon}. `icon` is a
 * bare name such as 'facebook' or 'x'; this skin has no icon set, so the name
 * is rendered as text and a newly-added network still gives a usable link.
 *}
{if $SOCIAL_LINKS}
<div class="element-social">
   <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.common.follow_us}</h3>
   <ul class="mt-4 flex flex-wrap gap-2 md:justify-end">
      {foreach from=$SOCIAL_LINKS item=link}
      <li>
         <a href="{$link.url}" title="{$link.name}" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center rounded-cc border border-ink-200 bg-ink-50 px-3 py-1.5 text-xs font-medium capitalize text-ink-700 hover:border-ink-300 hover:text-ink-900">
            {$link.name}
         </a>
      </li>
      {/foreach}
   </ul>
</div>
{/if}
