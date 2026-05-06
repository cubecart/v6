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
<html lang="en">
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
  </div>
  {if isset($GUI_MESSAGE)}
	{if isset($GUI_MESSAGE.errors)}
  <div id="error">
	<h2 class="first">{$LANG.gui_message.errors_detected}</h2>
	<ul>
	  {foreach from=$GUI_MESSAGE.errors item=error}<li>{$error}</li>{/foreach}
	</ul>
  </div>
	{/if}
	{if isset($GUI_MESSAGE.notices)}
	{foreach from=$GUI_MESSAGE.notices item=notice}
  <div id="notice">
	<h2 class="first">{$notice}</h2>
  </div>
	{/foreach}
	{/if}
  {/if}

  <form action="index.php" method="post" enctype="multipart/form-data">
	<div id="content">
  {if isset($STEP_HEADING)}<h1>{$STEP_HEADING}</h1>{/if}
  {if isset($SHOW_LANG_BACK) && $SHOW_LANG_BACK && !isset($MODE_LANGUAGE) && (isset($MODE_COMPAT) || isset($MODE_METHOD) || isset($MODE_PERMS) || isset($MODE_INSTALL))}
	  <p style="margin: 0 0 16px;"><a href="?reset_language=1">&larr; Change language</a></p>
  {/if}
  {if isset($MODE_LANGUAGE)}
	  <div class="lang-select-prompt" style="text-align:center; margin: 0 0 24px;">
		<div style="font-size: 48px; line-height: 1;" aria-hidden="true">&#127760;</div>
		<ul style="list-style:none; padding:0; margin:12px 0 0; font-size: 16px; line-height: 1.7;">
		  <li>Select your language</li>
		  <li lang="fr">Choisissez votre langue</li>
		  <li lang="es">Selecciona tu idioma</li>
		  <li lang="de">W&auml;hle deine Sprache</li>
		  <li lang="zh">&#36873;&#25321;&#24744;&#30340;&#35821;&#35328;</li>
		</ul>
	  </div>
	  {if isset($API_LANGUAGES)}
	  <fieldset>
		<div style="text-align:center;">
		  <label for="select_language" class="visually-hidden" style="position:absolute; clip:rect(0 0 0 0); width:1px; height:1px; overflow:hidden;">Language</label>
		  <select name="select_language" id="select_language" class="textbox" style="display:inline-block;">
			{foreach from=$API_LANGUAGES item=lang}<option value="{$lang.code}"{$lang.selected}>{$lang.name_native}</option>{/foreach}
		  </select>
		  <div style="margin-top: 12px;"><button type="submit" aria-label="Go" style="cursor: pointer;">&rarr;</button></div>
		</div>
	  </fieldset>
	  {/if}
  {/if}

  {if isset($MODE_COMPAT)}
	  <h2 class="first">{$LANG.setup.title_compat_check}</h2>
	  {foreach from=$CHECKS item=check}
	  <div>
		<span class="result">{if $check.status}<span class="pass">{$check.pass}</span>{else}<span class="fail">{$check.fail}</span>{/if}</span>
	  {$check.title}
	  </div>
	  {/foreach}
  {/if}

  {if isset($MODE_METHOD)}
	{if isset($SHOW_UPGRADE)}
	  <div id="method-upgrade" class="click-select selected">
	  <input type="radio" name="method" value="upgrade" checked="checked" />
	  <span class="icon"><img src="images/upgrade.png" alt="" /></span>
	  <h2 class="first">{$LANG_UPGRADE_CUBECART_TITLE}</h2>
	  <p>{$LANG.setup.upgrade_existing}</p>
	</div>
	{/if}
	<div id="method-install" class="click-select">
	  <input type="radio" name="method" value="install" />
	  <span class="icon"><img src="images/install.png" alt="" /></span>
	  <h2 class="first">{$LANG_INSTALL_CUBECART_TITLE}</h2>
	  <p>{$LANG.setup.install_fresh}</p>
	</div>
  {/if}

  {if isset($MODE_PERMS)}
	<h2 class="first">{$LANG.setup.title_file_permissions}</h2>
	{foreach from=$PERMISSIONS item=file}
	  <div>
		<span class="result">{if $file.status}<span class="pass">{$LANG.common.writable}</span>{else}<span class="fail">{$LANG.common.read_only}</span>{/if}</span>
		{$file.name}
	  </div>
	{/foreach}
	{if isset($PERMS_PASS)}<input type="hidden" name="permissions" value="1" />{/if}
  {/if}

  {if isset($MODE_INSTALL)}
	{if isset($PRESET_DB)}
	<input type="hidden" name="global[dbhost]" value="{$PRESET_DB.dbhost}" />
	<input type="hidden" name="global[dbdatabase]" value="{$PRESET_DB.dbdatabase}" />
	<input type="hidden" name="global[dbusername]" value="{$PRESET_DB.dbusername}" />
	<input type="hidden" name="global[dbpassword]" value="{$PRESET_DB.dbpassword}" />
	<input type="hidden" name="global[dbprefix]" value="{if isset($PRESET_DB.dbprefix)}{$PRESET_DB.dbprefix}{/if}" />
	<input type="hidden" name="global[dbport]" value="{if isset($PRESET_DB.dbport)}{$PRESET_DB.dbport}{/if}" />
	<input type="hidden" name="global[dbsocket]" value="{if isset($PRESET_DB.dbsocket)}{$PRESET_DB.dbsocket}{/if}" />
	{/if}

	<div class="install-grid">
	  {if !isset($PRESET_DB)}
	  <div class="install-grid-col">
		<h2 class="first">{$LANG.setup.title_database_settings}</h2>
		<fieldset>
		  <div><label for="form-dbhost" class="help" title="{$LANG.setup.help_dbhostname}">{$LANG.setup.db_host}</label><span><input type="text" name="global[dbhost]" id="form-dbhost"{if isset($FORM.global.dbhost)} value="{$FORM.global.dbhost}"{/if} class="textbox required" placeholder="localhost" /></span></div>
		  <div><label for="form-dbdatabase" class="help" title="{$LANG.setup.help_dbname}">{$LANG.setup.db_name}</label><span><input type="text" name="global[dbdatabase]" id="form-dbdatabase"{if isset($FORM.global.dbdatabase)} value="{$FORM.global.dbdatabase}"{/if} class="textbox required" /></span></div>
		  <div><label for="form-dbusername" class="help" title="{$LANG.setup.help_dbusername}">{$LANG.setup.db_username}</label><span><input type="text" name="global[dbusername]" id="form-dbusername"{if isset($FORM.global.dbusername)} value="{$FORM.global.dbusername}"{/if} class="textbox required" /></span></div>
		  <div><label for="form-dbpassword" class="help" title="{$LANG.setup.help_dbuserpass}">{$LANG.setup.db_password}</label><span class="password-wrap"><input type="password" autocomplete="off" name="global[dbpassword]" id="form-dbpassword"{if isset($FORM.global.dbpassword)} value="{$FORM.global.dbpassword}"{/if} class="textbox required" /><button type="button" class="password-toggle" data-target="form-dbpassword" data-text-show="{$LANG.common.show}" data-text-hide="{$LANG.common.hide}" tabindex="-1">{$LANG.common.show}</button></span></div>
		  <details class="install-advanced">
			<summary>{$LANG.common.advanced}</summary>
			<div><label for="form-dbprefix" class="help" title="{$LANG.setup.help_dbprefix}">{$LANG.setup.db_prefix}</label><span><input type="text" name="global[dbprefix]" id="form-dbprefix"{if isset($FORM.global.dbprefix)} value="{$FORM.global.dbprefix}"{/if} class="textbox" placeholder="store1_" /></span></div>
			<div><label for="form-dbport" class="help" title="{$LANG.setup.help_dbport}">{$LANG.setup.db_port}</label><span><input type="number" name="global[dbport]" id="form-dbport" min="1" max="65535"{if isset($FORM.global.dbport)} value="{$FORM.global.dbport}"{/if} class="textbox" placeholder="3306" /></span></div>
			<div><label for="form-dbsocket" class="help" title="{$LANG.setup.help_dbsocket}">{$LANG.setup.db_socket}</label><span><input type="text" name="global[dbsocket]" id="form-dbsocket"{if isset($FORM.global.dbsocket)} value="{$FORM.global.dbsocket}"{/if} class="textbox" placeholder="/var/run/mysqld/mysqld.sock" /></span></div>
			<div><label for="form-drop">{$LANG.setup.install_drop_tables}</label><span><input type="checkbox" name="drop" id="form-drop" value="1" /></span></div>
		  </details>
		</fieldset>
	  </div>
	  {/if}

	  <div class="install-grid-col">
		<h2 class="first">{$LANG.setup.title_admin_profile}</h2>
		<fieldset>
		  <div><label for="form-realname">{$LANG.common.name}</label><span><input type="text" name="admin[name]" id="form-realname"{if isset($FORM.admin.name)} value="{$FORM.admin.name}"{elseif isset($PRESET_NAME)} value="{$PRESET_NAME}"{/if} class="textbox required" /></span></div>
		  <div><label for="form-email">{$LANG.common.email}</label><span><input type="email" name="admin[email]" id="form-email"{if isset($FORM.admin.email)} value="{$FORM.admin.email}"{elseif isset($PRESET_EMAIL)} value="{$PRESET_EMAIL}"{/if} class="textbox required" /></span></div>
		  <div><label for="form-username">{$LANG.account.username}</label><span><input type="text" name="admin[username]" id="form-username"{if isset($FORM.admin.username)} value="{$FORM.admin.username}"{/if} class="textbox required" /></span></div>
		  <div><label for="form-password">{$LANG.account.password}</label><span class="password-wrap"><input type="password" name="admin[password]" id="form-password"{if isset($FORM.admin.password)} value="{$FORM.admin.password}"{/if} class="textbox required" aria-describedby="form-password-strength form-password-hint" data-min-length="10" data-min-classes="3" data-text-weak="{$LANG.setup.password_weak}" data-text-fair="{$LANG.setup.password_fair}" data-text-strong="{$LANG.setup.password_strong}" data-text-error="{$LANG.setup.error_password_weak}" /><button type="button" class="password-toggle" data-target="form-password" data-text-show="{$LANG.common.show}" data-text-hide="{$LANG.common.hide}" tabindex="-1">{$LANG.common.show}</button></span></div>
		  <div class="password-meter-row"><span class="password-meter" id="form-password-strength" aria-live="polite"><span class="password-meter-bar"><span class="password-meter-fill"></span></span><span class="password-meter-label"></span></span></div>
		  <p class="password-hint" id="form-password-hint">{$LANG.setup.password_requirements}</p>
		</fieldset>
	  </div>

	  {if !isset($PRESET_DB)}
	  <div class="connection-test-row">
		<button type="button" id="test-connection-btn" class="tiny" data-text-error="{$LANG.setup.test_connection_error}">{$LANG.setup.test_connection}</button>
		<span id="test-connection-result" aria-live="polite" role="status"></span>
	  </div>
	  {/if}
	</div>

	<div class="install-grid-col install-grid-solo">
	  <h2>{$LANG.setup.title_store_settings}</h2>
	  <fieldset>
		<div><label for="form-store_name">{$LANG.settings.store_name}</label><span><input type="text" name="config[store_name]" id="form-store_name"{if isset($FORM.config.store_name)} value="{$FORM.config.store_name}"{/if} class="textbox required" /></span></div>
		<input type="hidden" name="config[default_language]" value="{$SESSION_LANGUAGE}" />
		<div>
		  <label for="form-currency" class="help" title="{$LANG.setup.help_defaultcurrency}">{$LANG.settings.default_currency}</label>
		  <span>
			<select name="config[default_currency]" id="form-currency" class="textbox required">
			  <option value="">{$LANG.form.please_select}</option>
			  {foreach from=$CURRENCIES item=currency}<option value="{$currency.code}"{$currency.selected}>{$currency.code} - {$currency.name}</option>{/foreach}
			</select>
		  </span>
		</div>
	  </fieldset>
	</div>

	<p class="telemetry-consent"><label><input type="checkbox" name="config[allow_telemetry]" id="form-telemetry" value="1" checked="checked" /> {$LANG.setup.telemetry_note}</label></p>

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
	  <p>{$LANG.setup.upgrade_click_continue_error}</p>
	  {else}
	  <img src="images/loading.gif" align="middle" />
	  {/if}
	  </div>
	{/if}
  {/if}


  {if isset($MODE_COMPLETE)}
	{if isset($MODE_COMPLETE_INSTALL)}
	  <h2 class="first">{$LANG.setup.install_complete}</h2>
	  <div>{$LANG.setup.install_complete_note}</div>
	{/if}
	{if isset($MODE_COMPLETE_UPGRADE)}
	  <h2 class="first">{$LANG.setup.upgrade_complete}</h2>
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
		{if isset($CONTROLLER.continue) && !isset($MODE_LANGUAGE)}<span class="continue"><input type="submit" name="proceed" value="{$LANG.common.continue} &rarr;" /></span>{/if}
		{if isset($CONTROLLER.retry)}<span class="continue"><input type="submit" name="retry" value="{$LANG.setup.button_retry}" /></span>{/if}
		{if isset($CONTROLLER.restart) && !isset($MODE_COMPLETE_INSTALL)}<span class="cancel"><input type="submit" name="cancel" value="{$LANG.setup.button_restart}" class="cancel" /></span>{/if}
	  </div>
	</div>
  </form>
</div>
<div id="footer">{$LANG.setup.ecommerce_by} <a href="https://www.cubecart.com">CubeCart</a></div>
<script src="js/install.js"></script>
</body>
</html>