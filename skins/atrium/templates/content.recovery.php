{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_recovery(). Step 2 of password
 * reset, reached from the emailed link.
 *
 * ⚠ THESE FIELD NAMES ARE ARRAYS ON PURPOSE — password[password] and
 * password[passconf] — unlike register/profile, which must be flat. This page
 * does not go through the User::registerUser() htmlspecialchars loop, so arrays
 * are safe here. Do not "normalise" them to flat names.
 *
 * `validate` carries the emailed one-time token; `email` identifies the account.
 * Both must survive as hidden fields or the reset cannot be verified.
 * #password_recovery is a key in the validator's `forms` map
 * (3.cubecart.validate.js); its equalTo target is #password.
 *}
<div class="mx-auto max-w-md">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.password_reset|default:$LANG.account.password}</h1>

   <form action="{$VAL_SELF}" method="post" id="password_recovery" data-cc-validate class="cc-card mt-6 space-y-4 p-6">
      <div>
         <label for="password" class="cc-label">{$LANG.account.password}</label>
         <input type="password" name="password[password]" id="password" minlength="6" maxlength="64" autocomplete="new-password" required>
      </div>
      <div>
         <label for="passconf" class="cc-label">{$LANG.user.password_confirm}</label>
         <input type="password" name="password[passconf]" id="passconf" minlength="6" maxlength="64" data-match="#password" autocomplete="new-password" required>
      </div>

      {* One-time reset token + account, straight from the emailed link. *}
      <input type="hidden" name="validate" value="{$VALIDATE}">
      <input type="hidden" name="email" value="{$EMAIL}">

      <button type="submit" class="cc-btn cc-btn-primary w-full">{$LANG.common.submit}</button>
   </form>
</div>

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hidden" id="validate_password">{$LANG.account.error_password_empty}</div>
<div class="hidden" id="validate_password_length">{$LANG.account.error_password_length}</div>
<div class="hidden" id="validate_password_length_max">{$LANG.account.error_password_length_max}</div>
<div class="hidden" id="validate_password_mismatch">{$LANG.account.error_password_mismatch}</div>
