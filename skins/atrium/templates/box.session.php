{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * $IS_USER, $CUSTOMER.{first_name,last_name} and $SESSION_LIST_HOOKS
 * (plugin-supplied {href,title} rows) come from GUI::_displaySessionBox().
 *}
<div id="box-session" x-data="ccDisclosure()" @click.outside="close()" @keydown.escape.window="close()" class="relative">
{if $IS_USER}
   <button type="button" @click="toggle()" :aria-expanded="open ? 'true' : 'false'"
           class="cc-btn cc-btn-ghost gap-1.5">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
         <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span class="hidden sm:inline">{$CUSTOMER.first_name|capitalize}</span>
      <svg class="size-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
         <path d="m19.5 8.25-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
   </button>
   <ul x-show="open" x-cloak x-transition.opacity.duration.150ms
       class="absolute end-0 z-40 mt-2 w-60 rounded-cc-lg border border-ink-200 bg-ink-100 py-1.5 shadow-lg">
      <li class="border-b border-ink-200 px-4 pb-2 pt-1 text-sm font-medium text-ink-900">
         {$CUSTOMER.first_name|capitalize} {$CUSTOMER.last_name|capitalize}
      </li>
      <li><a href="{$STORE_URL}/index.php?_a=profile" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">{$LANG.account.your_details}</a></li>
      <li><a href="{$STORE_URL}/index.php?_a=vieworder" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">{$LANG.account.your_orders}</a></li>
      <li><a href="{$STORE_URL}/index.php?_a=addressbook" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">{$LANG.account.your_addressbook}</a></li>
      <li><a href="{$STORE_URL}/index.php?_a=downloads" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">{$LANG.account.your_downloads}</a></li>
      {if !isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1'}
      <li><a href="{$STORE_URL}/index.php?_a=newsletter" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">{$LANG.account.your_subscription}</a></li>
      {/if}
      {foreach from=$SESSION_LIST_HOOKS item=list_item}
      <li><a href="{$list_item.href}" title="{$list_item.title}" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">{$list_item.title}</a></li>
      {/foreach}
      <li class="mt-1 border-t border-ink-200 pt-1">
         <a href="{$STORE_URL}/index.php?_a=logout" class="block px-4 py-2 text-sm text-ink-700 hover:bg-ink-200">{$LANG.account.logout}</a>
      </li>
   </ul>
{else}
   <div class="flex items-center gap-1">
      <a href="{$STORE_URL}/login{$CONFIG.seo_ext}" class="cc-btn cc-btn-ghost">{$LANG.account.login}</a>
      <a href="{$STORE_URL}/register{$CONFIG.seo_ext}" class="cc-btn cc-btn-ghost hidden sm:inline-flex">{$LANG.account.register}</a>
   </div>
{/if}
</div>
