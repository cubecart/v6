{$cubecart->showAdminHeader()}
<form method="post" enctype="multipart/form-data">
  <div id="ShareYourCart" class="tab_content">
	<h3>{$LANG.shareyourcart.module_title}</h3>
	  <fieldset><legend>{$LANG.module.config_settings}</legend>
	<div><label for="status">{$LANG.shareyourcart.openid_allow}</label><span><select name="module[status]" id="status" class="textbox">
	  <option value="0" {$SELECT_status_0}>{$LANG.common.disabled}</option>
	  <option value="1" {$SELECT_status_1}>{$LANG.common.enabled}</option>
	</select>	
	</span></div>	
  </fieldset>
  </div>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}" name="syc-status-form" />
  </div>
  <div>
		<label for="scope">{$LANG.module.scope}</label>
		<span>
			<select name="module[scope]">
						<option value="both" {$SELECT_scope_both}>{$LANG.module.both}</option>
						<option value="main" {$SELECT_scope_main}>{$LANG.module.main}</option>
						<option value="mobile" {$SELECT_scope_mobile}>{$LANG.module.mobile}</option>
				</select>
		</span>
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>
{if $show_details}
{$cubecart->showAdminPage('<input type="hidden" name="token" value="'|cat:$SESSION_TOKEN|cat:'" />',true,false)}	
{$cubecart->showButtonCustomizationPage('<input type="hidden" name="token" value="'|cat:$SESSION_TOKEN|cat:'" />',false,true)}	
{/if}