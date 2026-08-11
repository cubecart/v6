{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_withdraw(). No form.
 *
 * ⚠ $WITHDRAWAL_CONFIRMATION is PRE-RENDERED HTML from core carrying the
 * legally-required acknowledgement wording — print it raw, never |escape.
 *}
<div class="mx-auto max-w-2xl py-8 text-center">
   <svg class="mx-auto size-12 text-success-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
      <path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/>
   </svg>

   <h1 class="mt-4 text-xl font-semibold tracking-tight text-ink-900">{$LANG.withdrawal.confirmation_title}</h1>

   <div class="prose-cc mx-auto mt-4 text-start">{$WITHDRAWAL_CONFIRMATION}</div>

   <a href="{$STORE_URL}" class="cc-btn cc-btn-primary mt-8">{$LANG.basket.continue_shopping}</a>
</div>
