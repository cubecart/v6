{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_recover(). Step 1 of password
 * reset: ask for the email, send a reset link.
 * Field: email. #recover_password is a key in the validator's `forms` map
 * (3.cubecart.validate.js).
 * ⚠ No `token` field; literal </form>. Captcha must sit inside the form.
 *}
<div class="mx-auto max-w-md">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.forgotten_password}</h1>
   <p class="mt-2 text-sm text-ink-600">{$LANG.account.password_recovery_notice|default:''}</p>

   <form action="{$VAL_SELF}" method="post" id="recover_password" data-cc-validate class="cc-card mt-6 space-y-4 p-6">
      <div>
         <label for="email" class="cc-label">{$LANG.user.email_address}</label>
         <input type="email" name="email" id="email" value="{$POST.email}" autocomplete="email" required>
      </div>

      {include file='templates/content.recaptcha.php' ga_fid='recover'}

      <button type="submit" data-form-id="recover_password" class="g-recaptcha cc-btn cc-btn-primary w-full" id="recover_submit">{$LANG.account.recover_password|default:$LANG.common.submit}</button>
   </form>

   <p class="mt-6 text-center text-sm">
      <a href="{$STORE_URL}/login{$CONFIG.seo_ext}" class="underline">{$LANG.account.login}</a>
   </p>
</div>

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
