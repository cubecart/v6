<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="AddShoppers" class="tab_content">
	<h3>AddShoppers</h3>
	  <fieldset><legend>AddShoppers Settings</legend>
	<div><label for="status">Enable AddShoppers?</label><span><select name="module[status]" id="status" class="textbox">
	  <option value="0" {$SELECT_status_0}>No</option>
	  <option value="1" {$SELECT_status_1}>Yes</option>
	</select> </span></div>
	<div><label for="addshoppers_shop_id">Shop ID</label><span><input name="module[addshoppers_shop_id]" id="addshoppers_shop_id" type="text" class="textbox" value="{$MODULE.addshoppers_shop_id}" /> Found in your <a href="https://www.addshoppers.com/merchants" target="_blank">AddShoppers dashboard</a> under Settings -> Shops.</span></div>
	<div><label for="status">Default Sharing Buttons?</label><span><select name="module[addshoppers_default_buttons]" id="status" class="textbox">
	  <option value="0" {$SELECT_addshoppers_default_buttons_0}>Off</option>
	  <option value="1" {$SELECT_addshoppers_default_buttons_1}>On</option>
	</select> </span></div>
	<div><label for="addshoppers_api_secret">API Secret</label><span><input name="module[addshoppers_api_secret]" id="addshoppers_api_secret" type="text" class="textbox" value="{$MODULE.addshoppers_api_secret}" /> Only necessary if using Social Shopper Login. </span></div>
	
	<legend style="margin-top: 20px;">Purchase Sharing Settings</legend>
	<div><label for="status">Enable Purchase Sharing?</label><span><select name="module[addshoppers_purchase_sharing_status]" id="status" class="textbox">
	  <option value="0" {$SELECT_addshoppers_purchase_sharing_status_0}>No</option>
	  <option value="1" {$SELECT_addshoppers_purchase_sharing_status_1}>Yes</option>
	</select> </span></div>
	<div><label for="addshoppers_purchase_sharing_header">Sharing Popup Header</label><span><input name="module[addshoppers_purchase_sharing_header]" id="addshoppers_purchase_sharing_header" type="text" class="textbox" value="{$MODULE.addshoppers_purchase_sharing_header}" /> Enter the header text for the Purchase Sharing modal popup.</span></div>
	<div><label for="addshoppers_purchase_sharing_image">Image To Be Shared</label><span><input name="module[addshoppers_purchase_sharing_image]" id="addshoppers_purchase_sharing_image" type="text" class="textbox" value="{$MODULE.addshoppers_purchase_sharing_image}" /> Enter the URL of the image to be shared (usually a logo image).</span></div>
	<div><label for="addshoppers_purchase_sharing_link">URL To Be Shared</label><span><input name="module[addshoppers_purchase_sharing_link]" id="addshoppers_purchase_sharing_link" type="text" class="textbox" value="{$MODULE.addshoppers_purchase_sharing_link}" /> Enter the URL that will be shared.</span></div>
	<div><label for="addshoppers_purchase_sharing_title">Share Title</label><span><input name="module[addshoppers_purchase_sharing_title]" id="addshoppers_purchase_sharing_title" type="text" class="textbox" value="{$MODULE.addshoppers_purchase_sharing_title}" /> Enter the title of the share.</span></div>
	<div><label for="addshoppers_purchase_sharing_description">Share Description</label><span><input name="module[addshoppers_purchase_sharing_description]" id="addshoppers_purchase_sharing_description" type="text" class="textbox" value="{$MODULE.addshoppers_purchase_sharing_description}" /> Enter the description/content of the share.</span></div>
	
  </fieldset>
  </div>
  <div class="form_control">
	<input type="submit" value="Save" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>

  <div style="padding: 0px 20px;">
  	<p>You can find your Social Analytics reports, add or edit your Social Rewards, get the code for sharing buttons, install more apps, and way more at your <a href="https://www.addshoppers.com/merchants" target="_blank">AddShoppers dashboard</a>. Need help? See our <a href="http://help.addshoppers.com" target="_blank">help site</a> or send us an email: help@addshoppers.com</p>
  </div>