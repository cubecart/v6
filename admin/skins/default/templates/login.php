{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<!DOCTYPE html>
<html lang="{$HTML_LANG}">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>{$LANG.dashboard.title_admin_cp}</title>
  <link href='//fonts.googleapis.com/css?family=Roboto:400,700,700italic,400italic&subset=cyrillic,cyrillic-ext,latin,greek-ext,greek,latin-ext' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/layout.css?{$VERSION_HASH}" media="screen">
  <meta name="robots" content="noindex,nofollow">
</head>
<body class="login">
<div class="preauth-body">
  <div id="logo"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/logo.cubecart.svg" alt="CubeCart Logo"></div>

  <div class="preauth-wrapper">
  {include file='templates/common.gui_message.php'}
  <form action="{$VAL_SELF}" class="ignore-dirty" method="post" enctype="application/x-www-form-urlencoded" name="acp-login">
	<div id="login-box">
	{if isset($TWOFA)}
	  <h1>{$LANG.account.twofa_title}</h1>
	  {if $TWOFA.method == 'email'}
	  <p style="color:#555;font-size:0.9em">{$LANG.account.twofa_email_sent} {$TWOFA.email_hint}</p>
	  {else}
	  <p style="color:#555;font-size:0.9em">{$LANG.account.twofa_enter_totp}</p>
	  {/if}
	  <div><span><input type="text" name="twofa_code" id="twofa_code" class="textbox required" inputmode="numeric" autocomplete="one-time-code" maxlength="8" autofocus placeholder="000000"></span><label for="twofa_code">{$LANG.account.twofa_code_label}</label>:</div>
	  <div id="login-box-foot">
	    {if $TWOFA.method == 'email' && $TWOFA.can_resend}
	    <span><a href="{$TWOFA.resend_url}">{$LANG.account.twofa_resend}</a></span>
	    {else}
	    <span>&nbsp;</span>
	    {/if}
	    <input type="submit" value="{$LANG.account.twofa_verify}" class="submit">
	  </div>
	  <p style="color:#888;font-size:0.85em;text-align:center;margin-top:8px">{$LANG.account.twofa_backup_hint}</p>
	{elseif isset($RECOVERY)}
	  <h1>{$LANG.account.title_password_new}</h1>
	  <div><span><input type="text" name="email" id="email" class="textbox required" value="{$REQUEST.email}"></span><label for="email">{$LANG.common.email}</label>:</div>
	  <div><span><input type="password" autocomplete="off" name="password[new]" id="password" class="textbox required"></span><label for="password">{$LANG.account.new_password}</label>:</div>
	  <div><span><input type="password" autocomplete="off" name="password[confirm]" id="passconf" class="textbox required"></span><label for="passconf">{$LANG.account.new_password_confirm}</label>:</div>
	  <div id="login-box-foot">
	    <span><a href="?_g=login">{$LANG.account.login_return}</a></span>
		<input type="hidden" name="validate" id="validate" value="{$REQUEST.validate}">
	    <input name="login" type="submit" id="login" value="{$LANG.form.submit}" class="submit">
	  </div>
	{elseif isset($PASSWORD)}
	  <h1>{$LANG.account.forgotten_password}</h1>
	  <div><span><input type="text" name="email" id="email" class="textbox required" value="{$EMAIL}"></span><label for="email">{$LANG.common.email}</label>:</div>
	  <div id="login-box-foot">
	    <span><a href="?_g=login">{$LANG.account.login_return}</a></span>
	    <input name="login" type="submit" id="login" value="{$LANG.form.submit}" class="submit">
	  </div>
	{else}
	  <h1>{$LANG.account.title_login_acp}</h1>
	  <div><span><input type="text" name="username" id="username" class="textbox required" value="{$USERNAME}"></span><label for="username">{$LANG.account.username}</label>:</div>
	  <div><span><input type="password" name="password" id="password" class="textbox required" value="{$PASSWORD}"></span><label for="password">{$LANG.account.password}</label>:</div>
	  <div id="login-box-foot">
		<span><a href="?_g=password">{$LANG.account.forgotten_password}</a></span>
		<input type="hidden" name="redir" value="{$REDIRECT_TO}">
		<input name="login" type="submit" id="login" value="{$LANG.account.log_in}" class="submit">
	  </div>
	{/if}
	</div>
	
  </form>
  <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/jquery.min.js?{$VERSION_HASH}"></script>
  <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/jquery-ui.min.js?{$VERSION_HASH}"></script>
  <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/plugins.php?{$VERSION_HASH}"></script>
  <!-- Common JavaScript functionality -->
  <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.js?{$VERSION_HASH}"></script>
  </div>
  {include file='templates/ccpower.php'}
  </div>
</body>
</html>