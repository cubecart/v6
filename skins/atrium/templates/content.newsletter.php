{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_newsletter().
 *
 * TWO MODES: $CTRL_VIEW -> read one archived newsletter; otherwise the
 * subscribe/unsubscribe form plus the archive list.
 *
 * ⚠ THE EMAIL FIELD NAME IS DYNAMIC: name="{$SUBSCRIBE_MODE}" resolves to
 * either `subscribe` or `unsubscribe`, and core branches on which one arrived.
 * Keep the variable — hardcoding either name breaks the other direction.
 * $FORM_ID is likewise supplied by core and is what the validator binds to
 * (#newsletter_form / #newsletter_form_unsubscribe).
 *
 * ⚠ Set $DISABLE_BOX_NEWSLETTER so main.php's footer box does not also render:
 * two forms sharing #newsletter_email and the #validate_* ids would make the
 * validator read whichever it found first.
 *}
{assign var=DISABLE_BOX_NEWSLETTER value=true scope='global'}

{if isset($CTRL_VIEW) && $CTRL_VIEW}
<article class="mx-auto max-w-3xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$NEWSLETTER.subject}</h1>
   <div class="prose-cc mt-6">{$NEWSLETTER.content_html}</div>
   <p class="mt-8"><a href="{$VAL_SELF}" class="cc-btn cc-btn-secondary">{$LANG.newsletter.view_newsletter_archive}</a></p>
</article>

{else}
<div class="mx-auto max-w-2xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.newsletter.subscription}</h1>

   {if !empty($IS_USER)}
   <p class="mt-4 text-sm text-ink-700">
      {if $SUBSCRIBED}
      {$LANG.newsletter.customer_is_subscribed}
      <a href="{$URL.unsubscribe}" class="underline">{$LANG.newsletter.unsubscribe}</a>
      {else}
      {$LANG.newsletter.customer_not_subscribed}
      <a href="{$URL.subscribe}" class="underline">{$LANG.newsletter.subscribe}</a>
      {/if}
   </p>
   {else}
   <form action="{$VAL_SELF}" method="post" id="{$FORM_ID}" class="cc-card mt-6 space-y-4 p-6">
      <div>
         <label for="newsletter_email" class="cc-label">{$LANG.newsletter.enter_email_subscribe_unsubscribe}</label>
         {* name is dynamic — subscribe | unsubscribe *}
         <input type="email" name="{$SUBSCRIBE_MODE}" id="newsletter_email" required autocomplete="email">
      </div>
      {include file='templates/content.recaptcha.php' ga_fid='newsletterpage'}
      {* ⚠ NO name="submit" HERE. A control of that name shadows form.submit,
         so the invisible-captcha callback's form.submit() throws TypeError —
         challenge solves, nothing submits, no error shown. *}
      <button type="submit" data-form-id="{$FORM_ID}" class="g-recaptcha cc-btn cc-btn-primary w-full" id="newsletter_page_submit">{$LANG.form.submit}</button>
   </form>
   {/if}

   {if isset($NEWSLETTERS)}
   <h2 class="mt-10 text-lg font-semibold tracking-tight text-ink-900">{$LANG.newsletter.newsletters}</h2>
   {if $NEWSLETTERS}
   <ul role="list" class="mt-4 divide-y divide-ink-200">
      {foreach from=$NEWSLETTERS item=newsletter}
      <li class="flex items-center justify-between gap-4 py-3">
         <a href="{$newsletter.view}" class="text-sm text-ink-800 hover:underline">{$newsletter.subject}</a>
         <span class="shrink-0 text-xs text-ink-500">{$newsletter.date_sent}</span>
      </li>
      {/foreach}
   </ul>
   {else}
   <p class="mt-4 text-sm text-ink-600">{$LANG.newsletter.no_archived_newsletters}</p>
   {/if}
   {/if}
</div>
{/if}

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hidden" id="validate_subscribe">{$LANG.newsletter.subscribe}</div>
<div class="hidden" id="validate_unsubscribe">{$LANG.newsletter.unsubscribe}</div>
<div class="hidden" id="validate_already_subscribed">{$LANG.newsletter.notify_already_subscribed}</div>
