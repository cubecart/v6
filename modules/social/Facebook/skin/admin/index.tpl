<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Facebook" class="tab_content">
	<h3><a href="http://www.facebook.com" target="_blank" title="Facebook">{$TITLE}</a></h3>
	
	<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	  <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" id="status" name="module[status]" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="appId">{$LANG.facebook.appid}</label><span><input type="text" id="appid" name="module[appid]" class="textbox" value="{$MODULE.appid}" /></span></div>
	  <p>{$LANG.facebook.appid_info}</p>
	</fieldset>
	
	
	<fieldset><legend>{$LANG.facebook.like_button}</legend>
	  <div><label for="like_status">{$LANG.common.status}</label><span><input type="hidden" id="like_status" name="module[like_status]" class="toggle" value="{$MODULE.like_status}" /></span></div>
	  <div><label for="like_location">{$LANG.module.social_location}</label><span>
	  	<select id="location" name="module[like_location]" class="textbox">
	  		<option value="all" {$SELECT_like_location_all}>{$LANG.module.social_location_all}</option>
	  		<option value="product" {$SELECT_like_location_product}>{$LANG.module.social_location_product}</option>
	  		<option value="document" {$SELECT_like_location_document}>{$LANG.module.social_location_document}</option>
	  	</select>
	  </div>
	  <div><label for="button_text">{$LANG.facebook.button_text}</label><span>
	  	<select id="button_text" name="module[button_text]" class="textbox">
	  		<option value="like" {$SELECT_button_text_like}>Like</option>
	  		<option value="recommend" {$SELECT_button_text_recommend}>Recommend</option>
	  	</select>
	  </div>
	  <div><label for="button_showfaces">{$LANG.facebook.button_showfaces}</label><span><input type="hidden" id="button_showfaces" name="module[button_showfaces]" class="toggle" value="{$MODULE.button_showfaces}" /></span></div>
	  <div><label for="button_width">{$LANG.facebook.button_width}</label><span><input type="text" id="button_width" name="module[button_width]" class="textbox" value="{$MODULE.button_width}" /></span></div>
	  <div><label for="button_color">{$LANG.facebook.button_color}</label><span>
	  	<select id="button_color" name="module[button_color]" class="textbox">
	  		<option value="light" {$SELECT_button_color_light}>Light</option>
	  		<option value="dark" {$SELECT_button_color_dark}>Dark</option>
	  	</select>
	  </div>
	   <div><label for="button_layout">{$LANG.facebook.button_layout}</label><span>
	  	<select id="button_layout" name="module[button_layout]" class="textbox">
	  		<option value="standard" {$SELECT_button_layout_standard}>standard</option>
	  		<option value="button_count" {$SELECT_button_layout_button_count}>button_count</option>
	  		<option value="box_count" {$SELECT_button_layout_box_count}>box_count</option>
	  	</select>
	  </div>
	</fieldset>
	
	<fieldset><legend>{$LANG.facebook.comments}</legend>
	  <div><label for="comments_status">{$LANG.common.status}</label><span><input type="hidden" id="comments_status" name="module[comments_status]" class="toggle" value="{$MODULE.comments_status}" /></span></div>
	  <div><label for="comments_location">{$LANG.module.social_location}</label><span>
	  	<select id="location" name="module[comments_location]" class="textbox">
	  		<option value="all" {$SELECT_comments_location_all}>{$LANG.module.social_location_all}</option>
	  		<option value="product" {$SELECT_comments_location_product}>{$LANG.module.social_location_product}</option>
	  		<option value="document" {$SELECT_comments_location_document}>{$LANG.module.social_location_document}</option>
	  	</select>
	  </div>
	  <!--<div><label for="comments_width">{$LANG.facebook.comments_width}</label><span><input type="text" id="comments_width" name="module[comments_width]" class="textbox" value="{$MODULE.comments_width}" /></span></div>-->
	  <div><label for="comments_numposts">{$LANG.facebook.comments_numposts}</label><span><input type="text" id="comments_numposts" name="module[comments_numposts]" class="textbox" value="{$MODULE.comments_numposts}" /></span></div>
	  <!--<div><label for="comments_publish_feed">{$LANG.facebook.comments_feed}</label><span><input type="hidden" id="comments_publish_feed" name="module[comments_publish_feed]" class="toggle" value="{$MODULE.comments_publish_feed}" /></span></div>-->

	  
	</fieldset>
  </div>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}" class="submit" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>