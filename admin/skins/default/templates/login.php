<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>{$LANG.dashboard.title_admin_cp}</title>
  <!--[if IE 7]><link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/ie7.css" media="screen"><![endif]-->
  <link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/layout.css" media="screen">
</head>
<body class="preauth-body">
  {include file='templates/common.gui_message.php'}
  <div class="preauth-wrapper{if !$SKIP_AD} preauth-wrapper-left-shift{/if}">
  <div id="logo"></div>
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
	  <h1>{if $SSL.state=='mixed'}<span id="login_ssl_switch"><a href="{$SSL.url}"><img src="{$SSL.icon}"></a></span>{elseif $SSL.state=='forced'}<span id="login_ssl_switch"><img src="{$SSL.icon}"></span>{/if}{$LANG.account.title_login_acp}</h1>
	  {if $TRIAL_LIMITS} 
  		<p>
  		<strong style="display: inline-block; width: 55px;">Version:</strong> CubeCart Lite (<a href="{$TRIAL_LIMITS.url}">Upgrade</a>)<br>
  		<strong style="display: inline-block; width: 55px;">Limits:</strong> {$TRIAL_LIMITS.orders} orders / {$TRIAL_LIMITS.customers} customers / {$TRIAL_LIMITS.administrator} Administrator<br>
  		</p>
  	  {/if}
	  <div><span><input type="text" name="username" id="username" class="textbox required" value="{$USERNAME}"></span><label for="username">{$LANG.account.username}</label>:</div>
	  <div><span><input type="password" name="password" id="password" class="textbox required" value="{$PASSWORD}"></span><label for="password">{$LANG.account.password}</label>:</div>
	  <div id="login-box-foot">
		<span><a href="?_g=password">{$LANG.account.forgotten_password}</a></span>
		<input type="hidden" name="redir" value="{$REDIRECT_TO}">
		<input name="login" type="submit" id="login" value="{$LANG.account.log_in}" class="submit no-change">
	  </div>
	{/if}
	</div>
	<input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>
	<!-- Include JavaScript last - YSlow! rates it better this way -->
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>

  <!--[if lte IE 6]>
  <script type="text/javascript">
	{literal}var IE6UPDATE_OPTIONS = {icons_path: "http://static.ie6update.com/hosted/ie6update/images/"}{/literal}
  </script>
  <script type="text/javascript" src="http://static.ie6update.com/hosted/ie6update/ie6update.js"></script>
  <![endif]-->
  <script type="text/javascript" src="js/plugins.php"></script>
  <!-- Common JavaScript functionality -->
  <script type="text/javascript" src="js/common.js"></script>
  <script type="text/javascript" src="js/admin.js"></script>
  </div>
</body>
</html>