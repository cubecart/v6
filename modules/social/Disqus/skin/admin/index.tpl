<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Disqus" class="tab_content">
	<h3><a href="http://www.disqus.com" target="_blank" title="Disqus">{$TITLE}</a></h3>
	<fieldset><legend>{$LANG.module.config_settings}</legend>
	  <div><label for="module_status">{$LANG.common.status}</label><span><input type="hidden" id="module_status" name="module[status]" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="location">{$LANG.module.social_location}</label><span>
	  	<select id="location" name="module[location]" class="textbox">
	  		<option value="all" {$SELECT_location_all}>{$LANG.module.social_location_all}</option>
	  		<option value="product" {$SELECT_location_product}>{$LANG.module.social_location_product}</option>
	  		<option value="document" {$SELECT_location_document}>{$LANG.module.social_location_document}</option>
	  	</select>
	  </div>
	  <div><label for="shortname">{$LANG.disqus.shortname}</label><span><input type="text" id="shortname" name="module[shortname]" class="textbox" value="{$MODULE.shortname}" /></span></div>
	  <div><label for="developer">{$LANG.disqus.developer}</label><span><input type="hidden" id="developer" name="module[developer]" class="toggle" value="{$MODULE.developer}" /></span></div>
	</fieldset>
  </div>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}" class="submit" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>