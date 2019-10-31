{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<h2>{$LANG.documents.document_contact}</h2>
<p>{$CONTACT.description}</p>
<form action="{$VAL_SELF}" id="contact_form" method="post">
   <div class="row">
      <div class="small-12 large-8 columns"><label for="contact_name">{$LANG.common.name}</label><input type="text" name="contact[name]" id="contact_name" value="{$MESSAGE.name}" placeholder="{$LANG.common.name} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="contact_email">{$LANG.common.email}</label><input type="text" name="contact[email]" id="contact_email" value="{$MESSAGE.email}" placeholder="{$LANG.common.email} {$LANG.form.required}"></div>
   </div>
   {if $CONTACT.phone}
   <div class="row">
      <div class="small-12 large-8 columns"><label for="contact_email">{$LANG.address.phone}</label><input type="text" name="contact[phone]" id="contact_phone" value="{$MESSAGE.phone}" placeholder="{$LANG.address.phone}{if $CONTACT.phone=='2'} {$LANG.form.required}{/if}"{if $CONTACT.phone=='2'} required="required"{/if}></div>
   </div>
   {/if}
   {if isset($DEPARTMENTS)}
   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="contact_dept">{$LANG.common.department}</label>
         <select name="contact[dept]" id="contact_dept">
            <option value="">{$LANG.form.please_select}</option>
            {foreach from=$DEPARTMENTS item=dept}
            <option value="{$dept.key}"{$dept.selected}>{$dept.name}</option>
            {/foreach}
         </select>
      </div>
   </div>
   {/if}
   <div class="row">
      <div class="small-12 large-8 columns"><label for="contact_subject">{$LANG.common.subject}</label><input type="text" name="contact[subject]" id="contact_subject" value="{$MESSAGE.subject}" placeholder="{$LANG.common.subject} {$LANG.form.required}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="contact_enquiry">{$LANG.common.enquiry}</label><textarea name="contact[enquiry]" id="contact_enquiry" placeholder="{$LANG.common.enquiry} {$LANG.form.required}" required>{$MESSAGE.enquiry}</textarea></div>
   </div>
   {include file='templates/content.recaptcha.php'}
   <input type="submit" class="button g-recaptcha" id="contact_submit" value="{$LANG.documents.send_message}">
</form>
<div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>
<div class="hide" id="validate_phone">{$LANG.account.error_valid_phone}</div>