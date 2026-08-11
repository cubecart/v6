{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_login().
 *
 * ⚠ NEVER author a `token` field, and ALWAYS close with a literal </form>:
 * GUI::display() injects the CSRF token by regex on every </form>, and
 * Sanitize::checkToken() wipes $_POST entirely when the token is missing —
 * silent, total data loss.
 *
 * Fields: username, password, remember, redir. $REDIR is the post-login return
 * page. #login_form is a key in the validator's `forms` map
 * (3.cubecart.validate.js).
 *}
<div class="mx-auto max-w-md">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.login}</h1>

   <form action="{$VAL_SELF}" method="post" id="login_form" class="cc-card mt-6 space-y-4 p-6">
      <div>
         <label for="username" class="cc-label">{$LANG.user.email_address}</label>
         <input type="text" name="username" id="username" value="{$USERNAME}" autocomplete="username" required>
      </div>
      <div>
         <label for="password" class="cc-label">{$LANG.account.password}</label>
         <input type="password" name="password" id="password" maxlength="64" autocomplete="current-password" required>
      </div>

      <div class="flex items-center justify-between gap-4">
         <span class="flex items-center gap-2">
            <input type="checkbox" name="remember" id="remember" value="1">
            <label for="remember" class="text-sm text-ink-800">{$LANG.account.remember_me}</label>
         </span>
         <a href="{$STORE_URL}/index.php?_a=recover" class="text-sm underline">{$LANG.account.forgotten_password}</a>
      </div>

      <input type="hidden" name="redir" value="{$REDIR}">
      {* No name="submit": it would shadow HTMLFormElement.submit() and break
         any programmatic submit. $_POST['submit'] is read nowhere. *}
      <button type="submit" class="cc-btn cc-btn-primary w-full">{$LANG.account.login}</button>
   </form>

   <p class="mt-6 text-center text-sm text-ink-600">
      {$LANG.account.no_account|default:''}
      <a href="{$STORE_URL}/register{$CONFIG.seo_ext}" class="underline">{$LANG.account.register}</a>
   </p>
</div>

{* Validator message sources — read by .text() into the `msg` cache at
   DOM-ready (3.cubecart.validate.js). Must be in the DOM, hidden by CSS. *}
<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hidden" id="empty_password">{$LANG.account.error_password_empty}</div>
<div class="hidden" id="validate_password_length_max">{$LANG.account.error_password_length_max}</div>
