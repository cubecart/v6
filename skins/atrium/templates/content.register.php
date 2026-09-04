{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_register().
 *
 * ⚠ FLAT SCALAR FIELD NAMES ONLY — NO ARRAYS. User::registerUser() runs
 *     foreach ($_POST as $k => $v) $_POST[$k] = htmlspecialchars(html_entity_decode($v));
 * html_entity_decode() on an array is a TypeError on PHP 8: registration dies
 * outright.
 *
 * ⚠ MASS ASSIGNMENT: User::registerUser() writes $_POST straight into
 * CubeCart_customer (INSERT, or UPDATE when a guest upgrades to an account).
 * The DB layer filters to real columns but there is NO allow-list, so THE FORM
 * IS THE WHITELIST. Never add an input whose name matches a CubeCart_customer
 * column — `credit`, `language`, `status`, `type`, `verify`, `registered`,
 * `ip_address` — or a customer can set it themselves.
 *
 * ⚠ No `token` field; close with a literal </form>. See content.login.php.
 *
 * Fields: first_name last_name email emailconf phone mobile password passconf
 *         terms_agree mailing_list register
 * #registration_form is bound by the validator, which also does a remote
 * ?_g=ajax_email check that posts `token` — that is JS-side, not a form field.
 *}
<div class="mx-auto max-w-2xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.register}</h1>

   <form action="{$VAL_SELF}" method="post" id="registration_form" class="mt-6" data-cc-validate>
      <div class="cc-card space-y-5 p-6">

         <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.account.contact_details}</h2>
         <div class="grid gap-4 sm:grid-cols-2">
            <div>
               <label for="first_name" class="cc-label">{$LANG.user.name_first}</label>
               <input type="text" name="first_name" id="first_name" data-msg-required="{$LANG.account.error_firstname_required}" value="{$POST.first_name}" maxlength="32" autocomplete="given-name" required>
            </div>
            <div>
               <label for="last_name" class="cc-label">{$LANG.user.name_last}</label>
               <input type="text" name="last_name" id="last_name" data-msg-required="{$LANG.account.error_lastname_required}" value="{$POST.last_name}" maxlength="32" autocomplete="family-name" required>
            </div>
            <div>
               <label for="email" class="cc-label">{$LANG.common.email}</label>
               <input type="email" name="email" id="email" data-remote="email" value="{$POST.email}" maxlength="96" autocomplete="email" required>
            </div>
            {if $CONFIG.emailconf=='1'}
            <div>
               <label for="emailconf" class="cc-label">{$LANG.account.email_confirm}</label>
               {* .nopaste is a contract: core JS blocks paste so it must be typed. *}
               <input type="email" name="emailconf" id="emailconf" data-match="#email" data-msg-match="{$LANG.account.error_email_mismatch}" class="nopaste" maxlength="96" required>
            </div>
            {/if}
            <div>
               <label for="phone" class="cc-label">{$LANG.address.phone}</label>
               <input type="tel" name="phone" id="phone" pattern="[0-9\-+().\s]+" value="{$POST.phone}" autocomplete="tel" required>
            </div>
            <div>
               <label for="mobile" class="cc-label">{$LANG.address.mobile}</label>
               <input type="tel" name="mobile" id="mobile" pattern="[0-9\-+().\s]+" data-msg-phone="{$LANG.account.error_valid_mobile_phone}" value="{$POST.mobile}" autocomplete="tel">
            </div>
         </div>

         <h2 class="pt-2 text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.account.password}</h2>
         <div class="grid gap-4 sm:grid-cols-2">
            <div>
               <label for="reg_password" class="cc-label">{$LANG.account.password}</label>
               <input type="password" name="password" id="reg_password" minlength="6" maxlength="64" autocomplete="new-password" required>
            </div>
            <div>
               <label for="passconf" class="cc-label">{$LANG.user.password_confirm}</label>
               <input type="password" name="passconf" id="passconf" minlength="6" maxlength="64" data-match="#reg_password" autocomplete="new-password" required>
            </div>
         </div>

         {if $TERMS_CONDITIONS}
         <div class="flex items-start gap-2">
            <input type="checkbox" name="terms_agree" id="terms_agree" data-msg-required="{$LANG.account.error_terms_agree}" value="1" class="mt-1" required>
            <label for="terms_agree" class="text-sm text-ink-800">{sprintf($LANG.account.register_terms_agree_link,$TERMS_CONDITIONS)}</label>
         </div>
         {/if}

         {if !isset($CONFIG.newsletter_status) || $CONFIG.newsletter_status=='1'}
         <div class="flex items-center gap-2">
            <input type="checkbox" name="mailing_list" id="mailing_list" value="1">
            <label for="mailing_list" class="text-sm text-ink-800">{$LANG.account.register_mailing}</label>
         </div>
         {/if}

         {* Must sit INSIDE the form so the captcha response posts.
            data-form-id lets the invisible-captcha callback find the form
            (content.recaptcha.head.php). ga_fid must be a bare JS identifier. *}
         {include file='templates/content.recaptcha.php' ga_fid='register'}
      </div>

      {* No hidden register field is needed: Cubecart::_register() decides this
         is a registration from the posted form itself. That matters under
         invisible captcha (mode 3), where grecaptcha calls form.submit() and a
         programmatic submit contributes neither the button's name nor its
         value, so nothing in the markup would carry the flag. NB the checkout
         checkbox in content.checkout.confirm.php is a different thing and is
         still read: it chooses between a guest and a registered account. *}
      <button type="submit" name="register" value="1" data-form-id="registration_form" class="g-recaptcha cc-btn cc-btn-primary mt-6 w-full" id="register_submit">{$LANG.account.register}</button>
   </form>

   <p class="mt-6 text-center text-sm text-ink-600">
      {$LANG.account.already_registered}
      <a href="{$STORE_URL}/login{$CONFIG.seo_ext}" class="underline">{$LANG.account.log_in}</a>
   </p>
</div>

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hidden" id="validate_email_in_use">{$LANG.account.error_email_in_use}</div>
<div class="hidden" id="validate_firstname">{$LANG.account.error_valid_first_name}</div>
<div class="hidden" id="validate_lastname">{$LANG.account.error_valid_last_name}</div>
<div class="hidden" id="validate_phone">{$LANG.account.error_valid_phone}</div>
<div class="hidden" id="validate_mobile">{$LANG.account.error_valid_mobile_phone}</div>
<div class="hidden" id="validate_password">{$LANG.account.error_password_empty}</div>
<div class="hidden" id="validate_password_length">{$LANG.account.error_password_length}</div>
<div class="hidden" id="validate_password_length_max">{$LANG.account.error_password_length_max}</div>
<div class="hidden" id="validate_password_mismatch">{$LANG.account.error_password_mismatch}</div>
{if $CONFIG.emailconf=='1'}<div class="hidden" id="validate_email_mismatch">{$LANG.account.error_email_mismatch}</div>{/if}
<div class="hidden" id="validate_terms_agree">{$LANG.account.error_terms_agree}</div>
