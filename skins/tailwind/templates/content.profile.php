<form action="{$VAL_SELF}" method="post" id="profile_form">
<div class="lg:grid lg:grid-cols-2 lg:gap-3">
  <div>
    <h2>{$LANG.account.your_details}</h2>

    <p class="text-sm mt-2 mb-5">{$LANG.account.update_your_details}</p>
      
    <fieldset class="md:w-2/3">
        {include file='templates/element.form.textbox.php' label="{$LANG.user.title}" value="{$USER.title}" name="title"}

        {include file='templates/element.form.textbox.php' label="{$LANG.user.name_first}" value="{$USER.first_name}" name="first_name" transform='capitalize' required=true}

        {include file='templates/element.form.textbox.php' label="{$LANG.user.name_last}" value="{$USER.last_name}" name="last_name" transform='capitalize' required=true maxlength=32}

        {include file='templates/element.form.textbox.php' label="{$LANG.user.email}" value="{$USER.email}" name="email" required=true maxlength=96}

        {if $CONFIG.emailconf=='1'}
          {include file='templates/element.form.textbox.php' label="{$LANG.account.email_confirm}" value="{$USER.email}" name="emailconf" required=true maxlength=96}
        {/if}

        {include file='templates/element.form.textbox.php' label="{$LANG.address.phone}" value="{$USER.phone}" name="phone" required=true maxlength=20}

        {include file='templates/element.form.textbox.php' label="{$LANG.address.mobile}" value="{$USER.mobile}" name="mobile" maxlength=20}
        
    </fieldset>
  </div>
<div>
    {if $ACCOUNT_EXISTS}
    <h2 class="mt-8 lg:mt-0">{$LANG.account.password_change}</h2>
    <p class="text-sm mt-2 mb-5">{$LANG.account.update_your_password}</p>
    <fieldset class="md:w-2/3">
    {include file='templates/element.form.textbox.php' type='password' label="{$LANG.user.password_current}" name="passold" maxlength=64 autocomplete="off"}

    {include file='templates/element.form.textbox.php' type='password' label="{$LANG.user.password_new}" name="passnew" maxlength=64 autocomplete="off"}

    {include file='templates/element.form.textbox.php' type='password' label="{$LANG.user.password_new}" name="passconf" maxlength=64 autocomplete="off"}
    {/if}
  </fieldset>   
</div>
<div>
{include file='templates/element.form.submit.php' value="{$LANG.common.update}" name="update" reset=true}  
</div>
</form>

<div class="hidden" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hidden" id="validate_firstname">{$LANG.account.error_firstname_required}</div>
<div class="hidden" id="validate_lastname">{$LANG.account.error_lastname_required}</div>
<div class="hidden" id="validate_phone">{$LANG.account.error_valid_phone}</div>
<div class="hidden" id="validate_mobile">{$LANG.account.error_valid_mobile_phone}</div>
<div class="hidden" id="validate_password_mismatch">{$LANG.account.error_password_mismatch}</div>
{if $CONFIG.emailconf=='1'}<div class="hidden" id="validate_email_mismatch">{$LANG.account.error_email_mismatch}</div>
{/if}
<div class="hidden" id="validate_password_length">{$LANG.account.error_password_length}</div>
<div class="hidden" id="validate_password_length_max">{$LANG.account.error_password_length_max}</div>