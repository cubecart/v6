{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 *}
<form action="{$VAL_SELF}" method="post">
  <h2>{$LANG.account.recover_password}</h2>
  <p>{$LANG.account.recover_password_text}</p>
  <fieldset>
	<div><label for="recover_email">{$LANG.common.email}</label><span><input type="text" name="email" id="recover_email" class="required"> *</span></div>
  </fieldset>
  <div><input type="submit" value="{$LANG.form.submit}" class="button_submit"></div>
  </form>