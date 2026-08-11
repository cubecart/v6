{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ Form contract — do not rename:
 *   name="subscribe"          the email address
 *   id="newsletter_email"     focus reveals the captcha
 *   name="force_unsubscribe"  hidden, must be present
 *   id="newsletter_form_box"  referenced by data-form-id on the submit button
 *   class="g-recaptcha" + data-form-id   how the captcha script finds the form
 *                                        (content.recaptcha.head.php)
 *
 * config.xml sets <newsletter_recaptcha>true</newsletter_recaptcha>, which makes
 * Newsletter::subscribe() enforce a captcha for anonymous subscribers.
 *
 * $DISABLE_BOX_NEWSLETTER lets a page suppress this box (the newsletter page
 * itself does, to avoid two competing forms).
 *}
{if !isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1'}
{if !isset($DISABLE_BOX_NEWSLETTER) || !$DISABLE_BOX_NEWSLETTER}
<div id="box-newsletter">
   <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.newsletter.mailing_list}</h3>
   {if $IS_USER}
      {if $CTRL_SUBSCRIBED}
      <p class="mt-3 text-sm text-ink-600">
         {$LANG.newsletter.customer_is_subscribed}
         <a href="{$STORE_URL}/index.php?_a=newsletter&action=unsubscribe" class="underline">{$LANG.newsletter.click_to_unsubscribe}</a>
      </p>
      {else}
      <p class="mt-3 text-sm text-ink-600">
         {$LANG.newsletter.customer_not_subscribed}
         <a href="{$STORE_URL}/index.php?_a=newsletter&action=subscribe" class="underline">{$LANG.newsletter.click_to_subscribe}</a>
      </p>
      {/if}
   {else}
   <form action="{$VAL_SELF}" method="post" id="newsletter_form_box" data-cc-validate class="mt-3" x-data="ccNewsletter()">
      <label for="newsletter_email" class="cc-sr-only">{$LANG.newsletter.enter_email_signup}</label>
      <div class="flex gap-2">
         <input name="subscribe" id="newsletter_email" type="email" maxlength="250"
                @focus="showCaptcha = true"
                title="{$LANG.newsletter.subscribe}"
                placeholder="{$LANG.common.eg} joe@example.com"
                class="min-w-0 flex-1">
         <button type="submit" id="subscribe_button" class="cc-btn cc-btn-primary g-recaptcha shrink-0" data-form-id="newsletter_form_box">
            {$LANG.newsletter.subscribe}
         </button>
         <input type="hidden" name="force_unsubscribe" id="force_unsubscribe" value="0">
      </div>
      <div id="newsletter_recaptcha" x-show="showCaptcha" x-cloak x-collapse class="mt-3">
         {include file='templates/content.recaptcha.php' ga_fid='Newsletter'}
      </div>
   </form>
   {* Validation strings read by core JS. The ids are looked up by core and by
      plugins — keep them exactly as they are. *}
   <div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
   <div class="hidden" id="validate_already_subscribed">{$LANG.newsletter.notify_already_subscribed} {$LANG.newsletter.continue_to_unsubscribe}</div>
   <div class="hidden" id="validate_subscribe">{$LANG.newsletter.subscribe}</div>
   <div class="hidden" id="validate_unsubscribe">{$LANG.newsletter.unsubscribe}</div>
   {/if}
</div>
{/if}
{/if}
