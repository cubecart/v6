<p>{$LANG.account.already_registered} <a href="{$STORE_URL}/login.html">{$LANG.account.login_here}</a></p>
<h2>{$LANG.account.register}</h2>
<form action="{$VAL_SELF}" id="registration_form" method="post" name="registration">
   {foreach from=$LOGIN_HTML item=html}
   {$html}
   {/foreach}
   <div class="row">
      <div class="small-4 columns"><label for="title" class="show-for-medium-up">{$LANG.user.title}</label><input type="text" name="title" id="title" value="{$DATA.title}" placeholder="{$LANG.user.title}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="first_name" class="show-for-medium-up">{$LANG.user.name_first}</label><input type="text" name="first_name" id="first_name" value="{$DATA.first_name}" placeholder="{$LANG.user.name_first} {$LANG.form.required}" required ></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="last_name" class="show-for-medium-up">{$LANG.user.name_last}</label><input type="text" name="last_name" id="last_name" value="{$DATA.last_name}"  placeholder="{$LANG.user.name_last} {$LANG.form.required}" required></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="email" class="show-for-medium-up">{$LANG.common.email}</label><input type="text" name="email" id="email" value="{$DATA.email}" placeholder="{$LANG.common.email}  {$LANG.form.required}" required ></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="phone" class="show-for-medium-up">{$LANG.address.phone}</label><input type="text" name="phone" id="phone"  value="{$DATA.phone}" placeholder="{$LANG.address.phone} {$LANG.form.required}" required></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="mobile" class="show-for-medium-up">{$LANG.address.mobile}</label><input type="text" name="mobile" id="mobile"  value="{$DATA.mobile}" placeholder="{$LANG.address.mobile}"></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="password" class="show-for-medium-up">{$LANG.account.password}</label><input type="password" autocomplete="off" name="password" id="password" placeholder="{$LANG.account.password} {$LANG.form.required}" required ></div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns"><label for="passconf" class="show-for-medium-up">{$LANG.account.password_confirm}</label><input type="password" autocomplete="off" name="passconf" id="passconf" placeholder="{$LANG.account.password_confirm}  {$LANG.form.required}" required ></div>
   </div>
   {include file='templates/content.recaptcha.php'}
   {if $TERMS_CONDITIONS}
   <div class="row">
      <div class="small-12 large-8 columns"><input type="checkbox" id="terms" name="terms_agree" value="1" {$TERMS_CONDITIONS_CHECKED}><label for="terms">{$LANG.account.register_terms_agree_link|replace:'%s':{$TERMS_CONDITIONS}}</label></div>
   </div>
   {/if}
   <div class="row">
      <div class="small-12 large-8 columns">
         <input type="checkbox" id="mailing" name="mailing_list" value="1" {if isset($DATA.mailing_list) && $DATA.mailing_list == 1}checked{/if}><label for="mailing">{$LANG.account.register_mailing}</label>
      </div>
   </div>
   <div class="row">
      <div class="small-12 large-8 columns clearfix">
      	  <input type="submit" name="register" value="{$LANG.account.register}" class="button">
	      <button type="reset" class="button secondary right"><i class="fa fa-refresh"></i> {$LANG.common.reset}</button>
      </div>
   </div>
</form>
<div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hide" id="validate_email_in_use">{$LANG.account.error_email_in_use}</div>
<div class="hide" id="validate_firstname">{$LANG.account.error_firstname_required}</div>
<div class="hide" id="validate_lastname">{$LANG.account.error_lastname_required}</div>
<div class="hide" id="validate_terms_agree">{$LANG.account.error_terms_agree}</div>
<div class="hide" id="validate_password">{$LANG.account.error_password_empty}</div>
<div class="hide" id="validate_password_length">{$LANG.account.error_password_length}</div>
<div class="hide" id="validate_password_mismatch">{$LANG.account.error_password_mismatch}</div>
<div class="hide" id="validate_phone">{$LANG.account.error_valid_phone}</div>
<div class="hide" id="validate_mobile">{$LANG.account.error_valid_mobile_phone}</div>
<div class="hide" id="validate_required">{$LANG.form.required}</div>