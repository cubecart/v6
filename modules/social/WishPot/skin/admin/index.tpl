<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="WishPot" class="tab_content">
	<h3><a href="http://www.wishpot.com" target="_blank" title="WishPot">{$TITLE}</a></h3>
	<fieldset><legend>{$LANG.module.config_settings}</legend>
	  <div><label for="module_status">{$LANG.common.status}</label><span><input type="hidden" id="module_status" name="module[status]" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="location">{$LANG.module.social_location}</label><span>
	  	<select id="location" name="module[location]" class="textbox">
	  		<option value="all" {$SELECT_location_all}>{$LANG.module.social_location_all}</option>
	  		<option value="product" {$SELECT_location_product}>{$LANG.module.social_location_product}</option>
	  		<option value="document" {$SELECT_location_document}>{$LANG.module.social_location_document}</option>
	  	</select>
	  </div>
	  <div><label for="user_id">{$LANG.wishpot.user_id}</label><span><input type="text" id="user_id" name="module[user_id]" class="textbox" value="{$MODULE.user_id}" /></span></div>
	</fieldset>
	
	<fieldset><legend>{$LANG.wishpot.choose_button}</legend>
		
		<div class="tall"><label for="button_1"><img src="https://www.wishpot.com/img/buttons/flower31px26px.png" /></label><span><input type="radio" id="button_1" name="module[button_img]" value="flower31px26px" {$CHECKED_button_img_flower31px26px} /></span></div>
		
		<div class="tall"><label for="button_2"><img src="https://www.wishpot.com/img/buttons/green_flower31px26px.png" /></label><span><input type="radio" id="button_2" name="module[button_img]" value="green_flower31px26px" {$CHECKED_button_img_green_flower31px26px} /></span></div>
		
		<div class="tall"><label for="button_3"><img src="https://www.wishpot.com/img/buttons/addtowishpot139px26px.png" /></label><span><input type="radio" id="button_3" name="module[button_img]" value="addtowishpot139px26px" {$CHECKED_button_img_addtowishpot139px26px} /></span></div>
		
		<div class="tall"><label for="button_4"><img src="https://www.wishpot.com/img/buttons/green_addtowishpot139px26px.png" /></label><span><input type="radio" id="button_4" name="module[button_img]" value="green_addtowishpot139px26px" {$CHECKED_button_img_green_addtowishpot139px26px} /></span></div>
		
		<div class="tall"><label for="button_5"><img src="https://www.wishpot.com/img/buttons/addtowishlist139px26px.png" /></label><span><input type="radio" id="button_5" name="module[button_img]" value="addtowishlist139px26px" {$CHECKED_button_img_addtowishlist139px26px} /></span></div>
		
		<div class="tall"><label for="button_6"><img src="https://www.wishpot.com/img/buttons/addtoregistry139px26px.png" /></label><span><input type="radio" id="button_6" name="module[button_img]" value="addtoregistry139px26px" {$CHECKED_button_img_addtoregistry139px26px} /></span></div>
	
	</fieldset>
  </div>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}" class="submit" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>