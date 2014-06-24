<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="hipay" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<p>{$LANG.hipay.module_description}</p>

  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>

			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>

			<div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>
			
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

			<div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" /></span></div>

			<div><label for="description">{$LANG.common.description} *</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>

			<div><label for="email_ack">{$LANG.hipay.email_ack}</label><span><input name="module[email_ack]" id="email_ack" class="textbox" type="text" value="{$MODULE.email_ack}" /></span></div>

      <div><label for="code_to_encrypt">{$LANG.hipay.code_to_encrypt}</label><span><input name="module[code_to_encrypt]" id="code_to_encrypt" class="textbox" type="text" value="{$MODULE.code_to_encrypt}" /> - {$LANG.hipay.code_to_encrypt_desc}</span></div>

      <div>
        <label for="age_group">{$LANG.hipay.age_group}</label>
          <span>
            <select name="module[age_group]">
                  <option value="ALL" {$SELECT_age_group_ALL}>ALL</option>
                  <option value="12" {$SELECT_age_group_12}>+12</option>
                  <option value="16" {$SELECT_age_group_16}>+16</option>
                  <option value="18" {$SELECT_age_group_18}>+18</option>
            </select>
          </span>
       </div>

      <div>
        <label for="locale">{$LANG.hipay.locale}</label>
          <span>
            <select name="module[locale]">
                  <option value="pt_PT" {$SELECT_locale_pt_PT}>pt_PT</option>
                  <option value="fr_FR" {$SELECT_locale_fr_FR}>fr_FR</option>
                  <option value="fr_BE" {$SELECT_locale_fr_BE}>fr_BE</option>
                  <option value="en_GB" {$SELECT_locale_en_GB}>en_GB</option>
                  <option value="en_US" {$SELECT_locale_en_US}>en_US</option>
                  <option value="nl_NL" {$SELECT_locale_nl_NL}>nl_NL</option>
                  <option value="nl_BE" {$SELECT_locale_nl_BE}>nl_BE</option>
                  <option value="es_ES" {$SELECT_locale_es_ES}>es_ES</option>
                  <option value="de_DE" {$SELECT_locale_de_DE}>de_DE</option>
              </select>
            </span>
          </div>

      <div>
        <label for="locale">{$LANG.hipay.currency}</label>
          <span>
            <select name="module[currency]">
                  <option value="EUR" {$SELECT_currency_EUR}>EUR</option>
                  <option value="USD" {$SELECT_currency_USD}>USD</option>
                  <option value="CAD" {$SELECT_currency_CAD}>CAD</option>
                  <option value="AUD" {$SELECT_currency_AUD}>AUD</option>
                  <option value="CHF" {$SELECT_currency_CHF}>CHF</option>
                  <option value="SEK" {$SELECT_currency_SEK}>SEK</option>
                  <option value="GBP" {$SELECT_currency_GBP}>GBP</option>
              </select>
            </span>
          </div>



      <div>
        <label for="email">{$LANG.hipay.debug_mode}</label>
          <span>
            <select name="module[debug_mode]">
                  <option value="0" {$SELECT_debug_mode_0}>{$LANG.hipay.off}</option>
                  <option value="1" {$SELECT_debug_mode_1}>{$LANG.hipay.on}</option>
              </select>  - {$LANG.hipay.debug_mode_desc}
            </span>
          </div>


			<div>
				<label for="email">{$LANG.hipay.mode}</label>
					<span>
						<select name="module[testMode]">
        					<option value="1" {$SELECT_testMode_1}>{$LANG.hipay.mode_sandbox}</option>
        					<option value="0" {$SELECT_testMode_0}>{$LANG.hipay.mode_live}</option>
    					</select>
    				</span>
    			</div>




      <fieldset><legend>{$LANG.hipay.mode_sandbox}</legend>
        <div><label for="sandbox_account_id">{$LANG.hipay.sandbox_account_id}</label><span><input name="module[sandbox_account_id]" id="sandbox_account_id" class="textbox" type="text" value="{$MODULE.sandbox_account_id}" /></span></div>

        <div><label for="sandbox_website_id">{$LANG.hipay.sandbox_website_id}</label><span><input name="module[sandbox_website_id]" id="sandbox_website_id" class="textbox" type="text" value="{$MODULE.sandbox_website_id}" OnBlur="return getHipayCategory(1);" /></span></div>

        <div><label for="sandbox_website_category">{$LANG.hipay.sandbox_website_category}</label><span><input name="module[sandbox_website_category]" id="sandbox_website_category" class="textbox" type="text" value="{$MODULE.sandbox_website_category}" /></span></div>

        <div><label for="sandbox_website_password">{$LANG.hipay.sandbox_website_password}</label><span><input name="module[sandbox_website_password]" id="sandbox_website_password" class="textbox" type="text" value="{$MODULE.sandbox_website_password}" /></span></div>

      </fieldset>


      <fieldset><legend>{$LANG.hipay.mode_live}</legend>
        <div><label for="live_account_id">{$LANG.hipay.live_account_id}</label><span><input name="module[live_account_id]" id="live_account_id" class="textbox" type="text" value="{$MODULE.live_account_id}" /></span></div>

        <div><label for="live_website_id">{$LANG.hipay.live_website_id}</label><span><input name="module[live_website_id]" id="live_website_id" class="textbox" type="text" value="{$MODULE.live_website_id}" OnBlur="return getHipayCategory(0);" /></span></div>

        <div><label for="live_website_category">{$LANG.hipay.live_website_category}</label><span><input name="module[live_website_category]" id="live_website_category" class="textbox" type="text" value="{$MODULE.live_website_category}" /></span></div>

        <div><label for="live_website_password">{$LANG.hipay.live_website_password}</label><span><input name="module[live_website_password]" id="live_website_password" class="textbox" type="text" value="{$MODULE.live_website_password}" /></span></div>

      </fieldset>

      </fieldset>

      <p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>



<script type="text/javascript">
function getHipayCategory(sandbox) {
  if (sandbox == 1)
    website_id = $("#sandbox_website_id").val();
  else
    website_id = $("#live_website_id").val();
    if (website_id == "") {
      $("#sandbox_website_category").val("");
    } else {
      $.post("modules/gateway/Hipay/admin/get_category.php?sandbox=" + sandbox + "&website=" + website_id, function(data){
        if (data != "0")
          if (sandbox == 1)
            $("#sandbox_website_category").val(data);
          else
            $("#live_website_category").val(data);
        else
          if (sandbox == 1)
            $("#sandbox_website_category").val("");
          else
            $("#live_website_category").val("");
      });
    }
}
</script>