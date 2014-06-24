<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Pinterest" class="tab_content">
	<h3><a href="http://www.pinterest.com" target="_blank" title="WishPot">{$TITLE}</a></h3>
	<fieldset><legend>{$LANG.module.config_settings}</legend>
	  <div><label for="module_status">{$LANG.common.status}</label><span><input type="hidden" id="module_status" name="module[status]" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="location">{$LANG.module.social_location}</label><span>
	  	<select id="location" name="module[location]" class="textbox">
	  		<option value="all" {$SELECT_location_all}>{$LANG.module.social_location_all}</option>
	  		<option value="product" {$SELECT_location_product}>{$LANG.module.social_location_product}</option>
	  		<option value="document" {$SELECT_location_document}>{$LANG.module.social_location_document}</option>
	  	</select>
	  </div>
	  <div><label for="username">{$LANG.pinterest.username}</label><span><input type="text" id="username" name="module[username]" class="textbox" value="{$MODULE.username}" /></span></div>
	</fieldset>
	
	<fieldset><legend>{$LANG.pinterest.choose_button}</legend>
		
		<div class="tall">
			<label for="button_1"><img src="http://passets-cdn.pinterest.com/images/follow-on-pinterest-button.png" /></label>
			<span><input type="radio" id="button_1" name="module[button_img]" value="follow_on_pinterest_button" {$CHECKED_button_img_follow_on_pinterest_button} /></span>
		</div>
		
		<div class="tall">
			<label for="button_2"><img src="http://passets-cdn.pinterest.com/images/pinterest-button.png" /></label>
			<span><input type="radio" id="button_2" name="module[button_img]" value="pinterest_button" {$CHECKED_button_img_pinterest_button} /></span>
		</div>
		
		<div class="tall">
			<label for="button_3"><img src="http://passets-cdn.pinterest.com/images/small-p-button.png" /></label><span><input type="radio" id="button_3" name="module[button_img]" value="small_p_button" {$CHECKED_button_img_small_p_button} /></span>
		</div>
		
		<div class="tall">
			<label for="button_4"><img src="http://passets-cdn.pinterest.com/images/big-p-button.png" /></label>
			<span><input type="radio" id="button_4" name="module[button_img]" value="big_p_button" {$CHECKED_button_img_big_p_button} /></span>
		</div>
	
	</fieldset>
  </div>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}" class="submit" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>