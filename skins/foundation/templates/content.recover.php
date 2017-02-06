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
<form action="{$VAL_SELF}" method="post" id="recover_password">
  <h2>{$LANG.account.recover_password}</h2>
  <p>{$LANG.account.recover_password_text}</p>
  <label for="email">{$LANG.common.email}</label>
  <input type="text" name="email" id="email" class="required" placeholder="{$LANG.common.email} {$LANG.form.required}">
  <div><input type="submit" value="{$LANG.form.submit}" class="button"></div>
</form>
<div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>