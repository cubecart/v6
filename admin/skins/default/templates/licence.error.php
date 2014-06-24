<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>{$LANG.gui_message.error}</title>
  <!--[if IE 7]><link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/ie7.css" media="screen"><![endif]-->
  <link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/layout.css" media="screen">
</head>
<body>
  <div id="licence-error">
	<h2>{$LANG.gui_message.error_oops}</h2>
	<ul>
	{foreach from=$ERRORS item=error}
	  <li>{$error.message}</li>
	{/foreach}
	</ul>
	<p><a href="?{$RANDOM_STRING}">{$LANG.gui_message.retry}</a></p>
	<form id="update_key" method="post" action="?{$RANDOM_STRING}">
	<fieldset><legend>{$LANG.settings.software_licence_key}</legend>
	<div><label for="current">{$LANG.settings.current_value}</label>{$LICENSE_KEY}</div>
	<div><label for="current">{$LANG.settings.new_value} *</label><input type="text" name="new_licence_key" value="" class="textbox"></div>
	<input type="submit" value="{$LANG.common.update}" name="submit">
	<p>* Submit empty field to use restricted free version (CubeCart Lite).</p>
	</fieldset>
	<input type="hidden" name="token" value="{$SESSION_TOKEN}">
	</form>
	<p>{$LANG.gui_message.contact_support}</p>
  </div>
</body>
</html>