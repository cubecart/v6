{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_account(). No forms.
 *
 * Plugin items in $ACCOUNT_LIST_HOOKS carry href, title and `fa` (an icon
 * name); `fa` is deliberately ignored here.
 *}
<div class="mx-auto max-w-3xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.your_account}</h1>

   {if $CREDIT}
   <div class="cc-card mt-6 p-5">
      <p class="text-sm text-ink-600">{$LANG.account.account_credit}</p>
      <p class="price mt-1 text-2xl font-semibold text-ink-900">{$CREDIT}</p>
      <p class="mt-1 text-xs text-ink-500">{$LANG.account.credit_desc}</p>
   </div>
   {/if}

   <nav class="mt-6">
      <ul role="list" class="grid gap-3 sm:grid-cols-2">
         <li><a href="{$STORE_URL}/index.php?_a=profile" class="cc-card block p-4 text-sm font-medium text-ink-900 hover:border-brand-600">{$LANG.account.your_details}</a></li>
         <li><a href="{$STORE_URL}/index.php?_a=vieworder" class="cc-card block p-4 text-sm font-medium text-ink-900 hover:border-brand-600">{$LANG.account.your_orders}</a></li>
         <li><a href="{$STORE_URL}/index.php?_a=addressbook" class="cc-card block p-4 text-sm font-medium text-ink-900 hover:border-brand-600">{$LANG.account.your_addressbook}</a></li>
         <li><a href="{$STORE_URL}/index.php?_a=downloads" class="cc-card block p-4 text-sm font-medium text-ink-900 hover:border-brand-600">{$LANG.account.your_downloads}</a></li>
         {if !isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1'}
         <li><a href="{$STORE_URL}/index.php?_a=newsletter" class="cc-card block p-4 text-sm font-medium text-ink-900 hover:border-brand-600">{$LANG.account.your_subscription}</a></li>
         {/if}
         {foreach from=$ACCOUNT_LIST_HOOKS item=list_item}
         <li><a href="{$list_item.href}" class="cc-card block p-4 text-sm font-medium text-ink-900 hover:border-brand-600">{$list_item.title}</a></li>
         {/foreach}
         <li><a href="{$STORE_URL}/index.php?_a=logout" class="cc-card block p-4 text-sm font-medium text-danger-600 hover:border-danger-600">{$LANG.account.logout}</a></li>
      </ul>
   </nav>
</div>
