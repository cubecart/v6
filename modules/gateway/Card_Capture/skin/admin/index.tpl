<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="Card_Capture" class="tab_content">
  		<h3>{$LANG.card_capture.module_title}</h3>
  		<p>{$LANG.card_capture.module_description}</p>
  		<fieldset><legend>{$LANG.card_capture.title_config_basic}</legend>
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
			<div><label for="validation">{$LANG.card_capture.card_validation}</label><span><input type="hidden" name="module[validation]" id="validation" class="toggle" value="{$MODULE.validation}" /></span></div>
    		<div><label for="issue_info">{$LANG.card_capture.card_show_issue}</label><span><input type="hidden" name="module[issue_info]" id="issue_info" class="toggle" value="{$MODULE.issue_info}" /></span></div>
    		<div><label for="cvv">{$LANG.card_capture.card_cvv_store}</label><span><input type="hidden" name="module[cvv]" id="cvv" class="toggle" value="{$MODULE.cvv}" /></span></div>
    		<div><label for="cvv_req">{$LANG.card_capture.card_cvv_require}</label><span><input type="hidden" name="module[cvv_req]" id="cvv_req" class="toggle" value="{$MODULE.cvv_req}" /></span></div>
	  	<div><label for="status">{$LANG.card_capture.confirmation_email}</label><span><input type="hidden" name="module[confirmation_email]" id="confirmation_email" class="toggle" value="{$MODULE.confirmation_email}" /></span></div>
		</fieldset>
		<fieldset><legend>{$LANG.card_capture.title_cards_accept}</legend>
			<table>
			  <tbody>
				<tr>
				  <td>Visa</td>
				  <td><input type="hidden" name="module[cards][Visa]" id="Visa" class="toggle" value="{$MODULE_CARDS.Visa}" /></td>
				</tr>
				<tr>
				  <td>MasterCard</td>
				  <td><input type="hidden" name="module[cards][MasterCard]" id="MasterCard" class="toggle" value="{$MODULE_CARDS.MasterCard}" /></td>
				</tr>
				<tr>
				  <td>Discover</td>
				  <td><input type="hidden" name="module[cards][Discover]" id="Discover" class="toggle" value="{$MODULE_CARDS.Discover}" /></td>
				</tr>
				<tr>
				  <td>American Express</td>
				  <td><input type="hidden" name="module[cards][Amex]" id="Amex" class="toggle" value="{$MODULE_CARDS.Amex}" /></td>
				</tr>
				<tr>
				  <td>Bankcard</td>
				    <td><input type="hidden" name="module[cards][Bankcard]" id="Bankcard" class="toggle" value="{$MODULE_CARDS.Bankcard}" /></td>
				  </tr>
				  <tr>
				    <td>China UnionPay</td>
				    <td><input type="hidden" name="module[cards][China_UnionPay]" id="China_UnionPay" class="toggle" value="{$MODULE_CARDS.China_UnionPay}" /></td>
				  </tr>
				  <tr>
				    <td>Diners Club</td>
				    <td><input type="hidden" name="module[cards][Diners_Club]" id="Diners_Club" class="toggle" value="{$MODULE_CARDS.Diners_Club}" /></td>
				  </tr>
				  <tr>
				    <td>JCB</td>
				    <td><input type="hidden" name="module[cards][JCB]" id="JCB" class="toggle" value="{$MODULE_CARDS.JCB}" /></td>
				  </tr>
				  <tr>
				    <td>Switch</td>
				    <td><input type="hidden" name="module[cards][Switch]" id="Switch" class="toggle" value="{$MODULE_CARDS.Switch}" /></td>
				  </tr>
				  <tr>
				    <td>Maestro</td>
				    <td><input type="hidden" name="module[cards][Maestro]" id="Maestro" class="toggle" value="{$MODULE_CARDS.Maestro}" /></td>
				  </tr>
				  <tr>
				    <td>Solo</td>
				    <td><input type="hidden" name="module[cards][Solo]" id="Solo" class="toggle" value="{$MODULE_CARDS.Solo}" /></td>
				  </tr>
				  <tr>
				    <td>Laser</td>
				    <td><input type="hidden" name="module[cards][Laser]" id="Laser" class="toggle" value="{$MODULE_CARDS.Laser}" /></td>
				  </tr>
				  <tr>
				    <td>Carte Blanche</td>
				    <td><input type="hidden" name="module[cards][Carte_Blanche]" id="Carte_Blanche" class="toggle" value="{$MODULE_CARDS.Carte_Blanche}" /></td>
				  </tr>
				  <tr>
				    <td>enRoute</td>
				    <td><input type="hidden" name="module[cards][enRoute]" id="enRoute" class="toggle" value="{$MODULE_CARDS.enRoute}" /></td>
				  </tr>
				  <tr>
				    <td>{$LANG.common.other}</td>
				    <td><input type="hidden" name="module[cards][Other]" id="Other" class="toggle" value="{$MODULE_CARDS.Other}" /></td>
				  </tr>
			  </tbody>
			</table>
		  </fieldset>
		  <p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  		<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>