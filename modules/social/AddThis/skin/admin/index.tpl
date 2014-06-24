<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="AddThis" class="tab_content">
	<h3><a href="http://www.addthis.com" target="_blank" title="AddThis">{$TITLE}</a></h3>
	<fieldset><legend>{$LANG.module.config_settings}</legend>
	  <div><label for="module_status">{$LANG.common.status}</label><span><input type="hidden" id="module_status" name="module[status]" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="location">{$LANG.module.social_location}</label><span>
	  	<select id="location" name="module[location]" class="textbox">
	  		<option value="all" {$SELECT_location_all}>{$LANG.module.social_location_all}</option>
	  		<option value="product" {$SELECT_location_product}>{$LANG.module.social_location_product}</option>
	  		<option value="document" {$SELECT_location_document}>{$LANG.module.social_location_document}</option>
	  	</select>
	  </div>
	  <div><label for="large_icons">{$LANG.addthis.large_icons}</label><span><input type="hidden" id="large_icons" name="module[large_icons]" class="toggle" value="{$MODULE.large_icons}" /></span></div>
	  <div><label for="analytics">{$LANG.addthis.analytics}</label><span><input type="hidden" id="analytics" name="module[analytics]" class="toggle" value="{$MODULE.analytics}" /></span></div>
	  <div><label for="username">{$LANG.addthis.username}</label><span><input type="text" id="username" name="module[username]" class="textbox" value="{$MODULE.username}" /></span></div>
	  <div><label for="preferred_count">{$LANG.addthis.preferred_count}</label><span><input type="text" id="preferred_count" name="module[preferred_count]" class="textbox" value="{$MODULE.preferred_count}" /> *</span></div>
	  <div><label for="specific_buttons">{$LANG.addthis.specific_buttons}</label><span><input type="text" id="specific_buttons" name="module[specific_buttons]" class="textbox" value="{$MODULE.specific_buttons}" /> **</span></div>
	</fieldset>
	<p>* {$LANG.addthis.preferred_count_desc}</p>
	<p>** {$LANG.addthis.specific_buttons_desc}</p>
	
  </div>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}" class="submit" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>