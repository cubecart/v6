{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_profile().
 *
 * ⚠ FLAT SCALAR FIELD NAMES ONLY — NO ARRAYS, same reason as register.
 * User::update() intersects $_POST with the loaded customer row and unsets only
 * salt, password, new_password, customer_id, status and type. EVERYTHING ELSE
 * POSTED IS WRITTEN, so the form is the whitelist — never add a field named
 * after a CubeCart_customer column (`credit` is a column and is NOT unset).
 *
 * ⚠ No `token` field; close with a literal </form>.
 *
 * Fields: first_name last_name email emailconf phone mobile
 *         passold passnew passconf update
 * #profile_form is a key in the validator's `forms` map
 * (3.cubecart.validate.js); its equalTo targets are #acc_email and #passnew —
 * keep those two ids exactly.
 *
 * Two visual sections, ONE form: core reads them together on `update`.
 *}
<div class="mx-auto max-w-2xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.account.your_details}</h1>

   <form action="{$VAL_SELF}" method="post" id="profile_form" data-cc-validate class="mt-6">
      <div class="cc-card space-y-5 p-6">
         {* update_your_details is a full sentence, not a label — hence the
            heading/paragraph split. *}
         <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.account.your_details}</h2>
         <p class="-mt-3 text-sm text-ink-600">{$LANG.account.update_your_details}</p>

         <div class="grid gap-4 sm:grid-cols-2">
            <div>
               <label for="first_name" class="cc-label">{$LANG.user.name_first}</label>
               <input type="text" name="first_name" id="first_name" value="{$USER.first_name|capitalize}" maxlength="32" autocomplete="given-name" required>
            </div>
            <div>
               <label for="last_name" class="cc-label">{$LANG.user.name_last}</label>
               <input type="text" name="last_name" id="last_name" value="{$USER.last_name|capitalize}" maxlength="32" autocomplete="family-name" required>
            </div>
            <div>
               {* id="acc_email" is the equalTo target for emailconf — keep it. *}
               <label for="acc_email" class="cc-label">{$LANG.common.email}</label>
               <input type="email" name="email" id="acc_email" value="{$USER.email}" maxlength="96" autocomplete="email" required>
            </div>
            {if $CONFIG.emailconf=='1'}
            <div>
               <label for="emailconf" class="cc-label">{$LANG.account.email_confirm}</label>
               <input type="email" name="emailconf" id="emailconf" data-match="#acc_email" data-msg-match="{$LANG.account.error_email_mismatch}" class="nopaste" maxlength="96" required>
            </div>
            {/if}
            <div>
               <label for="phone" class="cc-label">{$LANG.address.phone}</label>
               <input type="tel" name="phone" id="phone" pattern="[0-9\-+().\s]+" value="{$USER.phone}" autocomplete="tel" required>
            </div>
            <div>
               <label for="mobile" class="cc-label">{$LANG.address.mobile}</label>
               <input type="tel" name="mobile" id="mobile" pattern="[0-9\-+().\s]+" data-msg-phone="{$LANG.account.error_valid_mobile_phone}" value="{$USER.mobile}" autocomplete="tel">
            </div>
         </div>
      </div>

      <div class="cc-card mt-6 space-y-5 p-6">
         <div>
            {* password_change is the label; update_your_password is the
               instruction sentence. *}
            <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.account.password_change}</h2>
            <p class="mt-1 text-sm text-ink-600">{$LANG.account.update_your_password}</p>
         </div>
         <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
               <label for="passold" class="cc-label">{$LANG.user.password_current}</label>
               <input type="password" name="passold" id="passold" maxlength="64" autocomplete="current-password">
            </div>
            <div>
               {* id="passnew" is the equalTo target for passconf — keep it. *}
               <label for="passnew" class="cc-label">{$LANG.user.password_new}</label>
               <input type="password" name="passnew" id="passnew" minlength="6" maxlength="64" autocomplete="new-password">
            </div>
            <div>
               <label for="passconf" class="cc-label">{$LANG.user.password_confirm}</label>
               <input type="password" name="passconf" id="passconf" minlength="6" maxlength="64" data-match="#passnew" autocomplete="new-password">
            </div>
         </div>
      </div>

      <div class="mt-6 flex gap-3">
         <button type="submit" name="update" value="1" class="cc-btn cc-btn-primary">{$LANG.common.update}</button>
         <button type="reset" class="cc-btn cc-btn-secondary">{$LANG.common.reset}</button>
      </div>
   </form>
</div>

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hidden" id="validate_firstname">{$LANG.account.error_firstname_required}</div>
<div class="hidden" id="validate_lastname">{$LANG.account.error_lastname_required}</div>
<div class="hidden" id="validate_phone">{$LANG.account.error_valid_phone}</div>
<div class="hidden" id="validate_mobile">{$LANG.account.error_valid_mobile_phone}</div>
<div class="hidden" id="validate_password_length">{$LANG.account.error_password_length}</div>
<div class="hidden" id="validate_password_length_max">{$LANG.account.error_password_length_max}</div>
<div class="hidden" id="validate_password_mismatch">{$LANG.account.error_password_mismatch}</div>
{if $CONFIG.emailconf=='1'}<div class="hidden" id="validate_email_mismatch">{$LANG.account.error_email_mismatch}</div>{/if}
