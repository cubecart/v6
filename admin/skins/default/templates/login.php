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
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>{$LANG.dashboard.title_admin_cp}</title>
  <link href='//fonts.googleapis.com/css?family=Roboto:400,700,700italic,400italic&subset=cyrillic,cyrillic-ext,latin,greek-ext,greek,latin-ext' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/layout.css?{$VERSION_HASH}" media="screen">
</head>
<body class="login">
<div class="preauth-body">
  <div id="logo"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/cubecart-logo.png" alt="CubeCart Logo"></div>

  <div class="preauth-wrapper">
  {include file='templates/common.gui_message.php'}
  <form action="{$VAL_SELF}" method="post" enctype="application/x-www-form-urlencoded" name="acp-login">
	<div id="login-box">
	{if isset($RECOVERY)}
	  <h1>{$LANG.account.title_password_new}</h1>
	  <div><span><input type="text" name="validate" id="validate" class="textbox required" value="{$REQUEST.validate}"></span><label for="validate">{$LANG.account.validation_key}</label>:</div>
	  <div><span><input type="text" name="email" id="email" class="textbox required" value="{$REQUEST.email}"></span><label for="email">{$LANG.common.email}</label>:</div>
	  <div><span><input type="password" autocomplete="off" name="password[new]" id="password" class="textbox required"></span><label for="password">{$LANG.account.new_password}</label>:</div>
	  <div><span><input type="password" autocomplete="off" name="password[confirm]" id="passconf" class="textbox required"></span><label for="passconf">{$LANG.account.new_password_confirm}</label>:</div>
	  <div id="login-box-foot">
	    <span><a href="?_g=login">{$LANG.account.login_return}</a></span>
	    <input name="login" type="submit" id="login" value="{$LANG.form.submit}" class="submit no-change">
	  </div>
	{elseif isset($PASSWORD)}
	  <h1>{$LANG.account.forgotten_password}</h1>
	  <div><span><input type="text" name="username" id="username" class="textbox required" value="{$USERNAME}"></span><label for="username">{$LANG.account.username}</label>:</div>
	  <div><span><input type="text" name="email" id="email" class="textbox required" value="{$EMAIL}"></span><label for="email">{$LANG.common.email}</label>:</div>
	  <div id="login-box-foot">
	    <span><a href="?_g=login">{$LANG.account.login_return}</a></span>
	    <input name="login" type="submit" id="login" value="{$LANG.form.submit}" class="submit no-change">
	  </div>
	{else}
	  <h1>{if $SSL.state}<span id="login_ssl_switch"><a href="{$SSL.url}"><img src="{$SSL.icon}"></a></span>{/if}{$LANG.account.title_login_acp}</h1>
	  <div><span><input type="text" name="username" id="username" class="textbox required" value="{$USERNAME}"></span><label for="username">{$LANG.account.username}</label>:</div>
	  <div><span><input type="password" name="password" id="password" class="textbox required" value="{$PASSWORD}"></span><label for="password">{$LANG.account.password}</label>:</div>
	  <div id="login-box-foot">
		<span><a href="?_g=password">{$LANG.account.forgotten_password}</a></span>
		<input type="hidden" name="redir" value="{$REDIRECT_TO}">
		<input name="login" type="submit" id="login" value="{$LANG.account.log_in}" class="submit no-change">
	  </div>
	{/if}
	</div>
	
  </form>
  <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/jquery-1.11.2.min.js"></script>
  <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/jquery-ui-1.11.2.min.js"></script>
  <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/plugins.php?{$VERSION_HASH}"></script>
  <!-- Common JavaScript functionality -->
  <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.js?{$VERSION_HASH}"></script>
  </div>
  {include file='templates/ccpower.php'}
  </div>
</body>
</html>