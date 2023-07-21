{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title>CubeCart&reg; {$VERSION} Installer</title>
  <meta charset="utf-8" />
  <link href='//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="styles/style.css" media="screen" />
  {if isset($REFRESH)}<meta http-equiv="refresh" content="5" />{/if}
</head>
<body>
<div id="frame">
  <div id="header">
	<div id="language">
	  <form action="index.php" method="post" enctype="multipart/form-data">
		<select name="language" id="language-select" class="textbox">
		{foreach from=$LANG_LIST item=lang}<option value="{$lang.code}"{$lang.selected}>{$lang.title}</option>{/foreach}
		</select>
		<input type="submit" value="{$LANG.common.update}" class="mini_button" />
	  </form>
	</div>
  </div>
  <div id="promo">  
  Setup a hosted CubeCart store instantly at <a href="https://hosted.cubecart.com" target="_blank">https://hosted.cubecart.com</a></div>
  {if isset($PROGRESS)}
  <div id="progress">
	<div class="container">
	  <div class="indicator" style="width: {$PROGRESS.percent}% !important;">&nbsp;</div>
	</div>
	<div class="text">{$PROGRESS.message}</div>
  </div>
  {/if}

  

  {if isset($GUI_MESSAGE)}
	{if isset($GUI_MESSAGE.errors)}
  <div id="error">
	<h3 class="first">{$LANG.gui_message.errors_detected}</h3>
	<ul>
	  {foreach from=$GUI_MESSAGE.errors item=error}<li>{$error}</li>{/foreach}
	</ul>
  </div>
	{/if}
	{if isset($GUI_MESSAGE.notices)}
	{foreach from=$GUI_MESSAGE.notices item=notice}
  <div id="notice">
	<h3 class="first">{$notice}</h3>
  </div>
	{/foreach}
	{/if}
  {/if}

  <form action="index.php" method="post" enctype="multipart/form-data">
	<div id="content">
  {if isset($MODE_COMPAT)}
	  <h3 class="first">{$LANG.setup.title_compat_check}</h3>
	  {foreach from=$CHECKS item=check}
	  <div>
		<span class="result">{if $check.status}<span class="pass">{$check.pass}</span>{else}<span class="fail">{$check.fail}</span>{/if}</span>
	  {$check.title}
	  </div>
	  {/foreach}
  {/if}

  {if isset($MODE_METHOD)}
	{if isset($SHOW_UPGRADE)}
	  <div id="method-upgrade" class="click-select">
	  <input type="radio" name="method" value="upgrade" />
	  <span class="icon"><img src="images/upgrade.gif" alt="" /></span>
	  <h3 class="first">{$LANG_UPGRADE_CUBECART_TITLE}</h3>
	  <p>{$LANG.setup.upgrade_existing}</p>
	</div>
	{/if}
	<div id="method-install" class="click-select{if isset($SHOW_UPGRADE)} faded{/if}">
	  <input type="radio" name="method" value="install" />
	  <span class="icon"><img src="images/install.gif" alt="" /></span>
	  <h3 class="first">{$LANG_INSTALL_CUBECART_TITLE}</h3>
	  <p>{$LANG.setup.install_fresh}</p>
	</div>
  {/if}

  {if isset($MODE_LICENCE)}
	<h3 class="first">{$LANG.setup.title_licence}</h3>
	<div><textarea class="license" readonly>{$SOFTWARE_LICENCE}</textarea></div>
	<div><input type="checkbox" id="licence_agree" name="licence" value="1" /> <label for="licence_agree">{$LANG.setup.licence_agree}</label></div>
  {/if}

  {if isset($MODE_PERMS)}
	<h3 class="first">{$LANG.setup.title_file_permissions}</h3>
	{foreach from=$PERMISSIONS item=file}
	  <div>
		<span class="result">{if $file.status}<span class="pass">{$LANG.common.writable}</span>{else}<span class="fail">{$LANG.common.read_only}</span>{/if}</span>
		{$file.name}
	  </div>
	{/foreach}
	{if isset($PERMS_PASS)}<input type="hidden" name="permissions" value="1" />{/if}
  {/if}

  {if isset($MODE_INSTALL)}
	<h3>{$LANG.setup.title_database_settings}</h3>
	<fieldset>
	  <div><label for="form-dbhost" class="help" rel="" title="{$LANG.setup.help_dbhostname}">{$LANG.setup.db_host}</label><span><input type="text" name="global[dbhost]" id="form-dbhost" value="{$FORM.global.dbhost}" class="textbox required" /></span></div>
	  <div><label for="form-dbdatabase" class="help" rel="" title="{$LANG.setup.help_dbname}">{$LANG.setup.db_name}</label><span><input type="text" name="global[dbdatabase]" id="form-dbdatabase" value="{$FORM.global.dbdatabase}" class="textbox required" /></span></div>
	  <div><label for="form-dbusername" class="help" rel="" title="{$LANG.setup.help_dbusername}">{$LANG.account.username}</label><span><input type="text" name="global[dbusername]" id="form-dbusername" value="{$FORM.global.dbusername}" class="textbox required" /></span></div>
	  <div><label for="form-dbpassword" class="help" rel="" title="{$LANG.setup.help_dbuserpass}">{$LANG.account.password}</label><span><input type="password" autocomplete="off" name="global[dbpassword]" id="form-dbpassword" value="{$FORM.global.dbpassword}" class="textbox" /></span></div>
	  <div><label for="form-dbpassconf" class="help" rel="" title="{$LANG.setup.help_dbconfirmpass}">{$LANG.account.password_confirm}</label><span><input type="password" autocomplete="off" name="global[dbpassconf]" rel="form-dbpassword" id="form-dbpassconf" value="{$FORM.global.dbpassconf}" class="textbox confirm" /></span></div>
	  <div><label for="form-dbprefix" class="help" rel="" title="{$LANG.setup.help_dbprefix}">{$LANG.setup.db_prefix}</label><span><input type="text" name="global[dbprefix]" id="form-dbprefix" value="{$FORM.global.dbprefix}" class="textbox" /></span></div>
	  <div><label for="form-dbport" class="help" rel="" title="{$LANG.setup.help_dbport}">{$LANG.setup.db_port} {$LANG.common.optional}</label><span><input type="text" name="global[dbport]" id="form-dbport" value="{$FORM.global.dbport}" class="textbox" /></span></div>
	  <div><label for="form-dbsocket" class="help" rel="" title="{$LANG.setup.help_dbsocket}">{$LANG.setup.db_socket} {$LANG.common.optional}</label><span><input type="text" name="global[dbsocket]" id="form-dbsocket" value="{$FORM.global.dbsocket}" class="textbox" /></span></div>
	</fieldset>
	<h3>{$LANG.setup.title_store_settings}</h3>
	<fieldset>
	  <div><label for="form-store_name" rel="">{$LANG.settings.store_name}</label><span><input type="text" name="config[store_name]" id="form-store_name" value="{$FORM.config.store_name}" class="textbox required" /></span></div>
	  <div>
		<label for="form-language" class="help" rel="" title="{$LANG.setup.help_defaultlang}">{$LANG.settings.default_language}</label>
		<span>
		  <select name="config[default_language]" id="form-language" class="textbox required">
			<option value="">{$LANG.form.please_select}</option>
			{foreach from=$LANGUAGES item=language}<option value="{$language.code}"{$language.selected}>{$language.title}</option>{/foreach}
		  </select>
		</span>
	  </div>
	  <div>
		<label for="form-currency" class="help" rel="" title="{$LANG.setup.help_defaultcurrency}">{$LANG.settings.default_currency}</label>
		<span>
		  <select name="config[default_currency]" id="form-currency" class="textbox required">
			<option value="">{$LANG.form.please_select}</option>
			{foreach from=$CURRENCIES item=currency}<option value="{$currency.code}"{$currency.selected}>{$currency.code} - {$currency.name}</option>{/foreach}
		  </select>
		</span>
	  </div>
	</fieldset>
	<h3>{$LANG.setup.title_admin_profile}</h3>
	<fieldset>
	  <div><label for="form-username" rel="">{$LANG.account.username}</label><span><input type="text" name="admin[username]" id="form-username" value="{$FORM.admin.username}" class="textbox required" /></span></div>
	  <div><label for="form-password" rel="">{$LANG.account.password}</label><span><input type="password" name="admin[password]" id="form-password" value="{$FORM.admin.password}" class="textbox required" /></span></div>
	  <div><label for="form-passconf" rel="">{$LANG.account.password_confirm}</label><span><input type="password" name="admin[passconf]" id="form-passconf" rel="form-password" value="{$FORM.admin.passconf}" class="textbox required confirm" /></span></div>
	  <div><label for="form-realname" rel="">{$LANG.user.name_full}</label><span><input type="text" name="admin[name]" id="form-realname" value="{$FORM.admin.name}" class="textbox required" /></span></div>
	  <div><label for="form-email" rel="">{$LANG.common.email}</label><span><input type="text" name="admin[email]" id="form-email" value="{$FORM.admin.email}" class="textbox required" /></span></div>
	</fieldset>
	<h3>{$LANG.setup.title_advanced_settings}</h3>
	<fieldset>
	  <div><label for="form-drop" class="help" title="{$LANG.setup.install_drop_tables_explained}">{$LANG.setup.install_drop_tables}</label><span><input type="checkbox" name="drop" id="form-drop" value="1" /> {$LANG.setup.install_drop_tables_explained}</span></div>
	</fieldset>
	<input type="hidden" name="progress" value="0" />
  {/if}

  {if isset($MODE_UPGRADE)}
	{if isset($MODE_UPGRADE_CONFIRM)}
	<div>{$LANG_UPGRADE_FROM_TO}<br />
	{$LANG.setup.upgrade_will_reload}<br />
	<br />{$LANG.setup.upgrade_click_continue}</div>
	<input type="hidden" name="progress" value="0" />
	{/if}

	{if isset($MODE_UPGRADE_PROGRESS)}
	  <div>
	  <p>{$LANG_UPGRADE_IN_PROGRESS}</p>
	  {if isset($GUI_MESSAGE)}
	  <p>Please click continue to continue upgrade.</p>
	  {else}
	  <img src="images/loading.gif" align="middle" />
	  {/if}
	  </div>
	{/if}
  {/if}


  {if isset($MODE_COMPLETE)}
	{if isset($MODE_COMPLETE_INSTALL)}
	  <h3 class="first">{$LANG.setup.install_complete}</h3>
	  <div>{$LANG.setup.install_complete_note}</div>
	{/if}
	{if isset($MODE_COMPLETE_UPGRADE)}
	  <h3 class="first">{$LANG.setup.upgrade_complete}</h3>
	  <div>{$LANG.setup.upgrade_complete_note}</div>
	{/if}
  {/if}
  	{if isset($MODE_COMPLETE_UPGRADE) && isset($SHOW_LINKS)}
  	<p class="url_change">{$LANG.setup.urls_changed}</p>
  	{/if}
  	{if isset($SHOW_LINKS)}
	  <div>
		<h4>{$LANG.setup.link_admin_panel}</h4>
		<a href="{$ADMIN_URL}" target="_blank">{$ADMIN_URL}</a><br>
		{$LANG.setup.link_admin_panel_note}
		<h4>{$LANG.setup.link_store_front}</h4>
		<a href="{$STORE_URL}" target="_blank">{$STORE_URL}</a>
	  </div>
	{/if}
	  <div id="toolbar">
		{if isset($CONTROLLER.continue)}<span class="continue"><input type="submit" name="proceed" value="{$LANG.common.continue} &raquo;" /></span>{/if}
		{if isset($CONTROLLER.retry)}<span class="continue"><input type="submit" name="retry" value="{$LANG.setup.button_retry}" /></span>{/if}
		{if isset($CONTROLLER.restart)}<span class="cancel"><input type="submit" name="cancel" value="{$LANG.setup.button_restart}" class="cancel" /></span>{/if}
	  </div>
	</div>
  </form>
</div>
<div id="footer">eCommerce by <a href="http://www.cubecart.com">CubeCart</a></div>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/plugins/jquery.pstrength.js"></script>
<script type="text/javascript" src="js/install.js"></script>
</body>
</html>