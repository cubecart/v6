<form id="form-settings" action="{$PHP_SELF}" method="post" enctype="multipart/form-data">

<div id="taxclasses" class="tab_content">
  <h3>{$LANG.settings.title_tax_class}</h3>
  <fieldset><legend>{$LANG.settings.title_tax_class_current}</legend>
	{foreach from=$TAX_CLASSES item=class}
	<div><input type="text" name="class[{$class.id}][tax_name]" value="{$class.tax_name}" class="textbox">
	<a href="{$VAL_SELF}&delete_class={$class.id}" class="delete" title="{$LANG.settings.tax_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
	</div>
	{/foreach}
  </fieldset>
  <fieldset><legend>{$LANG.settings.title_tax_class_add}</legend>
	<div><label for="addclass">{$LANG.settings.tax_class_name}</label><span><input name="addclass[tax_name]" id="addclass" type="text" class="textbox" value=""></span></div>
  </fieldset>
</div>

<div id="taxdetails" class="tab_content">
  <h3>{$LANG.settings.title_tax_detail}</h3>
  <fieldset><legend>{$LANG.settings.title_tax_detail_current}</legend>
	<table class="list">
	  <thead>
		<tr>
		  <td width="50">{$LANG.common.status}</td>
		  <td width="302">{$LANG.settings.tax_name}</td>
		  <td width="302">{$LANG.settings.tax_name_display}</td>
		  <td>{$LANG.settings.tax_number}</td>
		  <td width="20">&nbsp;</td>
		</tr>
	  </thead>
	  <tbody>
		{foreach from=$TAX_DETAILS item=detail}
		<tr>
		  <td align="center"><input type="hidden" name="detail[{$detail.id}][status]" id="detail_{$detail.id}" value="{$detail.status}" class="toggle"></td>
		  <td><span class="editable" name="detail[{$detail.id}][name]">{$detail.name}</span></td>
		  <td><span class="editable" name="detail[{$detail.id}][display]">{$detail.display}</span></td>
		  <td align="center"><a href="{$VAL_SELF}&delete_detail={$detail.id}" class="delete" title="{$LANG.settings.tax_delete}" ><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a></td>
		</tr>
		{/foreach}
	  </tbody>
    </table>
  </fieldset>
  <fieldset><legend>{$LANG.settings.title_tax_detail_add}</legend>
	<div><label for="detail-name">{$LANG.settings.tax_name}</label><span><input name="adddetail[name]" id="detail-name" type="text" class="textbox" value=""></span></div>
	<div><label for="detail-display">{$LANG.settings.tax_name_display}</label><span><input name="adddetail[display]" id="detail-display" type="text" class="textbox" value=""></span></div>
	<div><label for="detail-status">{$LANG.common.status}</label><span><select name="adddetail[status]" id="detail-status" class="textbox">
	  <option value="0">{$LANG.common.disabled}</option>
	  <option value="1">{$LANG.common.enabled}</option>
	</select></span></div>
  </fieldset>
</div>

<div id="taxrules" class="tab_content">
  <h3>{$LANG.settings.title_tax_rule}</h3>
  <fieldset><legend>{$LANG.settings.title_tax_rule_current}</legend>
	<table>
	  <thead>
		<tr>
	      <td width="50">{$LANG.common.status}</td>
	      <td width="100">{$LANG.settings.title_tax_class}</td>
	      <td>{$LANG.settings.title_tax_detail}</td>
	      <td width="150">{$LANG.address.country}</td>
	      <td width="150">{$LANG.address.state}</td>
	      <td width="50">{$LANG.basket.total_sub}</td>
	      <td width="50">{$LANG.basket.shipping}</tc>
	      <td width="125">{$LANG.settings.tax_rate}</td>
	      <td width="20">&nbsp;</td>
		</tr>
	  </thead>
	  <tbody class="list">
		{foreach from=$TAX_RULES item=rule}
		<tr>
		  <td align="center"><input type="hidden" name="rule[{$rule.id}][active]" id="rule_{$rule.id}" value="{$rule.active}" class="toggle"></td>
		  <td>{$rule.class}</td>
		  <td>{$rule.detail}</td>
		  <td>{$rule.country}</td>
		  <td>{$rule.state}</td>
		  <td align="center"><input type="hidden" name="rule[{$rule.id}][goods]" id="goods_{$rule.id}" value="{$rule.goods}" class="toggle"></td>
		  <td align="center"><input type="hidden" name="rule[{$rule.id}][shipping]" id="shipping_{$rule.id}" value="{$rule.shipping}" class="toggle"></td>
		  <td nowrap="nowrap"><input type="text" name="rule[{$rule.id}][tax_percent]" class="textbox number" style="text-align: right;" value="{$rule.tax_percent}"> %</td>
		  <td align="center"><a href="{$VAL_SELF}&delete_rule={$rule.id}" class="delete" title="{$LANG.notification.confirm_delete}" ><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a></td>
		</tr>
		{foreachelse}
  		<tr>
		  <td align="center" colspan="9">{$LANG.form.none}</td>
		</tr>
		{/foreach}
	  </tbody>
	</table>
  </fieldset>
  
  <fieldset><legend>{$LANG.settings.title_tax_rule_add}</legend>
	<div><label for="rule-class">{$LANG.settings.tax_class}</label><span><select name="addrule[type_id]" class="textbox" id="rule-class">
	  {foreach from=$TAX_CLASSES item=class}<option value="{$class.id}">{$class.tax_name}</option>{/foreach}
	</select></span></div>
	<div><label for="rule-detail">{$LANG.settings.tax_detail}</label><span><select name="addrule[details_id]" class="textbox" id="rule-detail">
	  {foreach from=$TAX_DETAILS item=detail}<option value="{$detail.id}">{$detail.display}</option>{/foreach}
	</select></span></div>
	
	<div><label for="country-list">{$LANG.address.country}</label><span><select name="addrule[country_id]" id="country-list" class="textbox no-custom-zone" title="{$LANG.common.regions_all}">
	  {foreach from=$COUNTRIES item=country}<option value="{$country.numcode}" {if $country.numcode == $CONFIG.store_country}selected="selected"{/if}>{$country.name}</option>{/foreach}
	</select></span></div>
	<div><label for="state-list">{$LANG.address.state}</label><span><input name="addrule[county_id]" type="text" id="state-list" class="textbox" value="{$VAL_TAX_STATE}"></span></div>
	
	<div><label for="rule-taxrate">{$LANG.settings.tax_rate}</label><span><input name="addrule[tax_percent]" id="rule-taxrate" type="text" class="textbox number"></span></div>
	<div><label for="rule-applyto">{$LANG.settings.tax_apply_to}</label><span>
		<input type="hidden" name="addrule[goods]" id="rule-goods" value="0" class="toggle"> {$LANG.settings.tax_on_goods} 
		<input type="hidden" name="addrule[shipping]" id="rule-shipping" value="0" class="toggle"> {$LANG.settings.tax_on_shipping} 
	</span></div>
	
	<div><label for="rule-status">{$LANG.common.status}</label><span><select name="addrule[active]" id="rule-status" class="textbox">
	  <option value="0">{$LANG.common.disabled}</option>
	  <option value="1">{$LANG.common.enabled}</option>
	</select></span></div>
  </fieldset>
</div>

{include file='templates/element.hook_form_content.php'}

<div class="form_control">
  <input type="submit" id="submit" class="button" value="{$LANG.common.save}">
  <input type="hidden" value="{$FORM_HASH}">
  <input type="hidden" name="previous-tab" id="previous-tab" value="">
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</div>
</form>

<script type="text/javascript">
var county_list = {$VAL_JSON_COUNTY};
</script>