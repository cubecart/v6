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

<form action="{$VAL_SELF}" id="password_recovery" method="post">
	<h2>{$LANG.account.recover_password}</h2>
	<p>{$LANG.account.enter_validation_key}</p>
	<div class="row">
		<div class="small-12 columns">
			<label for="email">{$LANG.common.email}</label>
			<input type="text" name="email" id="email" value="{$DATA.email}" placeholder="{$LANG.common.email} ({$LANG.common.required})" required>
		</div>
	</div>
	<div class="row">
		<div class="small-12 columns">
			<label for="validate">{$LANG.account.validation_key}</label>
			<input type="text" name="validate" id="validate" value="{$DATA.validate}" placeholder="{$LANG.account.validation_key} ({$LANG.common.required})" required>
		</div>
	</div>
	<div class="row">
		<div class="small-12 columns">
			<label for="password">{$LANG.account.new_password}</label>
			<input type="password" maxlength="64" autocomplete="off" name="password[password]" id="password" placeholder="{$LANG.account.new_password} ({$LANG.common.required})" required>
		</div>
	</div>
	<div class="row">
		<div class="small-12 columns">
			<label for="passconf">{$LANG.account.new_password_confirm}</label>
			<input type="password" maxlength="64" autocomplete="off" name="password[passconf]" id="passconf" placeholder="{$LANG.account.new_password_confirm} ({$LANG.common.required})" required>
		</div>
	</div>
	<div class="row">
		<div class="small-12 columns">
			<input type="submit" value="{$LANG.form.submit}" class="button">
		</div>
	</div>
</form>
<div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hide" id="validate_password">{$LANG.account.error_password_empty}</div>
<div class="hide" id="validate_password_length">{$LANG.account.error_password_length}</div>
<div class="hide" id="validate_password_length_max">{$LANG.account.error_password_length_max}</div>
<div class="hide" id="validate_password_mismatch">{$LANG.account.error_password_mismatch}</div>
<div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>