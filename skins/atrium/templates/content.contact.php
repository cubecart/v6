{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::_contact().
 *
 * ⚠ THESE FIELD NAMES ARE ARRAYS ON PURPOSE — contact[name], contact[email],
 * contact[phone], contact[dept], contact[subject], contact[enquiry], and
 * attachments[] for uploads. This page does not pass through the User class's
 * htmlspecialchars/trim loops, so arrays are safe here.
 *
 * ⚠ enctype="multipart/form-data" is REQUIRED — attachments[] is a file input.
 * ⚠ No `token` field; close with a literal </form>.
 * #contact_form is a key in the validator's `forms` map
 * (3.cubecart.validate.js). Captcha must sit inside the form.
 *}
<div class="mx-auto max-w-2xl">
   <h1 class="text-xl font-semibold tracking-tight text-ink-900">{$LANG.documents.document_contact}</h1>

   <form action="{$VAL_SELF}" method="post" id="contact_form" enctype="multipart/form-data" class="mt-6">
      <div class="cc-card space-y-4 p-6">
         <div class="grid gap-4 sm:grid-cols-2">
            <div>
               <label for="contact_name" class="cc-label">{$LANG.common.name}</label>
               <input type="text" name="contact[name]" id="contact_name" value="{$POST.name}" autocomplete="name" required>
            </div>
            <div>
               <label for="contact_email" class="cc-label">{$LANG.common.email}</label>
               <input type="email" name="contact[email]" id="contact_email" value="{$POST.email}" autocomplete="email" required>
            </div>
            <div>
               <label for="contact_phone" class="cc-label">{$LANG.address.phone}</label>
               <input type="tel" name="contact[phone]" id="contact_phone" value="{$POST.phone}" autocomplete="tel">
            </div>
            {if $DEPARTMENTS}
            <div>
               <label for="contact_dept" class="cc-label">{$LANG.contact.department|default:$LANG.common.department}</label>
               <select name="contact[dept]" id="contact_dept">
                  {foreach from=$DEPARTMENTS item=dept}
                  <option value="{$dept.value|default:$dept}">{$dept.name|default:$dept}</option>
                  {/foreach}
               </select>
            </div>
            {/if}
         </div>

         <div>
            <label for="contact_subject" class="cc-label">{$LANG.common.subject}</label>
            <input type="text" name="contact[subject]" id="contact_subject" value="{$POST.subject}" required>
         </div>

         <div>
            <label for="contact_enquiry" class="cc-label">{$LANG.contact.enquiry|default:$LANG.common.message}</label>
            <textarea name="contact[enquiry]" id="contact_enquiry" rows="6" required>{$POST.enquiry}</textarea>
         </div>

         {if $ALLOW_ATTACHMENTS}
         <div>
            <label for="attachments" class="cc-label">{$LANG.common.attachments|default:''}</label>
            <input type="file" name="attachments[]" id="attachments" multiple>
         </div>
         {/if}

         {include file='templates/content.recaptcha.php' ga_fid='contact'}
      </div>

      <button type="submit" data-form-id="contact_form" class="g-recaptcha cc-btn cc-btn-primary mt-6 w-full" id="contact_submit">{$LANG.form.submit}</button>
   </form>
</div>

<div class="hidden" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hidden" id="validate_phone">{$LANG.account.error_valid_phone}</div>
