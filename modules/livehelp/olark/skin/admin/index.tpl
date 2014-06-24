<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="Olark" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<a href="http://www.olark.com/?r=m92lccq0" title="Sign Up">Signup for a FREE Olark Account!</a>
  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
			<div><label for="site_id">{$LANG.olark.site_id}</label><span><input name="module[site_id]" id="site_id" class="textbox" type="text" value="{$MODULE.site_id}" /></span></div>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	</fieldset>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>