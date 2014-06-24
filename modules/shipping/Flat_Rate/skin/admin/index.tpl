<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Flat_Rate" class="tab_content">
  <h3>{$TITLE}</h3>
  <p>{$LANG.flat_rate.module_description}</p>
  <fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	<div><label for="name">{$LANG.common.name}</label><span><input type="text" name="module[name]" id="name" value="{$MODULE.name}" class="textbox" /></span> {$LANG.module.shipping_name_eg}</div>
	<div><label for="cost">{$LANG.basket.shipping_cost}</label><span><input name="module[cost]" id="cost" class="textbox" type="text" value="{$MODULE.cost}" /></span></div>
	<div><label for="handling">{$LANG.basket.shipping_handling}</label><span><input name="module[handling]" id="handling" class="textbox" type="text" value="{$MODULE.handling}" /></span></div>
	<div><label for="tax">{$LANG.catalogue.tax_type}</label>
		<span>
			<select name="module[tax]" id="tax">
			  {foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
			</select>
		</span>
	</div>
	<div><label for="status">{$LANG.catalogue.tax_included}</label><span><input type="hidden" name="module[tax_included]" id="tax_included" class="toggle" value="{$MODULE.tax_included}" /></span></div>
  </fieldset>
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>