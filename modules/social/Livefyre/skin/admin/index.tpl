<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
?>
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Livefyre" class="tab_content">
	<h3><a href="http://www.livefyre.com" target="_blank" title="Livefyre">{$TITLE}</a></h3>
	<fieldset><legend>{$LANG.module.config_settings}</legend>
	  <div><label for="module_status">{$LANG.common.status}</label><span><input type="hidden" id="module_status" name="module[status]" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="location">{$LANG.module.social_location}</label><span>
	  	<select id="location" name="module[location]" class="textbox">
	  		<option value="all" {$SELECT_location_all}>{$LANG.module.social_location_all}</option>
	  		<option value="product" {$SELECT_location_product}>{$LANG.module.social_location_product}</option>
	  		<option value="document" {$SELECT_location_document}>{$LANG.module.social_location_document}</option>
	  	</select>
	  </div>
	  <div><label for="site_id">{$LANG.livefyre.site_id}</label><span><input type="text" id="site_id" name="module[site_id]" class="textbox" value="{$MODULE.site_id}" /></span></div>
	</fieldset>
  </div>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}" class="submit" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>