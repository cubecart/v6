<form action="{$VAL_SELF}" method="post">
  <h2>{$LANG.account.recover_password}</h2>
  <p>{$LANG.account.recover_password_text}</p>
  <fieldset>
	<div><label for="recover_email">{$LANG.common.email}</label><span><input type="text" name="email" id="recover_email" class="required"> *</span></div>
  </fieldset>
  <div><input type="submit" value="{$LANG.form.submit}" class="button_submit"></div>
  </form>