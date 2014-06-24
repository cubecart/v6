<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Per_Category" class="tab_content">
	<h3>{$TITLE}</h3>
	<p>{$LANG.per_category.module_description}</p>
	<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	  <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label for="name">{$LANG.common.name}</label><span><input type="text" name="module[name]" id="name" value="{$MODULE.name}" class="textbox" /></span> {$LANG.module.shipping_name_eg}</div>
	  <div><label for="handling">{$LANG.per_category.tax_type}</label>
	  <span>
		<select name="module[tax]" id="tax">
		{foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
		</select>
	  </span>
	  </div>
	  <div><label for="status">{$LANG.catalogue.tax_included}</label><span><input type="hidden" name="module[tax_included]" id="tax_included" class="toggle" value="{$MODULE.tax_included}" /></span></div>
	  <div>
		<label for="mod_handling">{$LANG.basket.shipping_handling}</label>
		<span><input name="module[handling]" id="mod_handling" type="text" class="textbox" value="{$MODULE.handling}" /></span>
	  </div>
	  <div>
		<label for="mod_national">{$LANG.per_category.countries_national}</label>
		<span><input type="text" name="module[national]" id="mod_national" value="{$MODULE.national}" class="textbox" /> {$LANG.common.eg} GB </span>
	  </div>
	  <div>
		<label for="mod_intl">{$LANG.per_category.countries_international}</label>
		<span><input type="text" name="module[international]" id="mod_intl" value="{$MODULE.international}" class="textbox" /> {$LANG.common.eg} FR,ES,IT,PT,DE,DK</label>
	  </div>
	</fieldset>
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>