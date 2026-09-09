{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Exit-intent newsletter modal. Store Settings → "Show exit modal"
 * (config[exit_modal]) is the switch; main.php is the only call site, because
 * core never fetches this template itself.
 *
 * NOT included from main.checkout.php on purpose: an interstitial thrown over
 * a customer who is mid-payment costs more than a subscriber is worth.
 *
 * ⚠ Form contract — same as box.newsletter.php, with every id suffixed so the
 * two forms can coexist on one page:
 *   name="subscribe" / name="force_unsubscribe"   the payload
 *   id="newsletter_form_exit"                     target of data-form-id
 *   class="g-recaptcha" + data-form-id            how invisible reCAPTCHA
 *                                                 (content.recaptcha.head.php)
 *                                                 finds and submits the form
 * Duplicating box.newsletter.php's ids here is what breaks the reference
 * implementation in foundation: the captcha renders once, against the footer.
 *
 * No validate_* divs: Atrium serves those from element.validation_messages.php
 * as one JSON blob, and a second copy of #validate_email in the document would
 * be an id collision for any plugin that looks one up.
 *}
{if (!isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1') && !empty($CONFIG.exit_modal)}
{if !$IS_USER || !$CTRL_SUBSCRIBED}
{if !isset($DISABLE_BOX_NEWSLETTER) || !$DISABLE_BOX_NEWSLETTER}
<div x-data="ccExitModal()" x-show="open" x-cloak x-trap.noscroll="open"
     @keydown.escape.window="close()"
     x-transition.opacity.duration.150ms
     class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/80 p-4"
     role="dialog" aria-modal="true" aria-labelledby="exit-modal-title">

   <div class="relative w-full max-w-md rounded-cc-lg border border-ink-200 bg-ink-100 p-6 shadow-lg" @click.stop>
      <button type="button" @click="close()"
              class="absolute end-3 top-3 rounded-cc p-2 text-ink-500 hover:bg-ink-200 hover:text-ink-900">
         <span class="cc-sr-only">{$LANG.common.close|default:'Close'}</span>
         <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"/>
         </svg>
      </button>

      <h2 id="exit-modal-title" class="pe-8 text-lg font-semibold text-ink-900">{$LANG.email.exit_title}</h2>
      <p class="mt-2 text-sm text-ink-600">{$LANG.email.exit_copy}</p>

      <form action="{$VAL_SELF}" method="post" id="newsletter_form_exit" data-cc-validate class="mt-4">
         <label for="newsletter_email_exit" class="cc-sr-only">{$LANG.newsletter.enter_email_signup}</label>
         <div class="flex gap-2">
            <input name="subscribe" id="newsletter_email_exit" type="email" maxlength="250"
                   title="{$LANG.newsletter.subscribe}"
                   placeholder="{$LANG.common.eg} joe@example.com"
                   class="min-w-0 flex-1">
            <button type="submit" id="subscribe_button_exit" class="cc-btn cc-btn-primary g-recaptcha shrink-0" data-form-id="newsletter_form_exit">
               {$LANG.newsletter.subscribe}
            </button>
            <input type="hidden" name="force_unsubscribe" id="force_unsubscribe_exit" value="0">
         </div>
         <div id="newsletter_recaptcha_exit" class="mt-3">
            {include file='templates/content.recaptcha.php' ga_fid='NewsletterExit'}
         </div>
      </form>
   </div>
</div>
{/if}
{/if}
{/if}
