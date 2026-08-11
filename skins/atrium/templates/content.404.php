{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div class="mx-auto max-w-lg py-16 text-center">
   <p class="text-sm font-semibold uppercase tracking-widest text-brand-600">404</p>
   <h1 class="mt-3 text-3xl font-semibold tracking-tight text-ink-900 sm:text-4xl">
      {$LANG.common.page_not_found|default:'Page not found'}
   </h1>
   <p class="mt-4 text-ink-600">
      {$LANG.common.error_404|default:"Sorry, we couldn't find the page you were looking for."}
   </p>
   <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
      <a href="{$STORE_URL}" class="cc-btn cc-btn-primary">{$LANG.common.home}</a>
      <a href="{$ROOT_PATH}index.php?_a=contact" class="cc-btn cc-btn-secondary">{$LANG.common.contact_us|default:'Contact us'}</a>
   </div>
</div>
