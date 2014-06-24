<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="By_Weight" class="tab_content">
  <h3>{$TITLE}</h3>
  <div>{$LANG.by_weight.module_description}</div>
  <fieldset><legend>{$LANG.module.cubecart_settings}</legend>
 	<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	<!--<div><label for="name">{$LANG.common.name}</label><span><input type="text" name="module[name]" id="name" value="{$MODULE.name}" class="textbox" /></span> {$LANG.module.shipping_name_eg}</div>-->
	<div><label for="name_class1">{$LANG.by_weight.name_class1}</label><span><input type="text" name="module[name_class1]" id="name_class1" value="{if $MODULE.name_class1}{$MODULE.name_class1}{else}{$LANG.by_weight.rate_1}{/if}" class="textbox" /></span> {$LANG.by_weight.shipping_name_eg1}</div>
	<div><label for="name_class2">{$LANG.by_weight.name_class2}</label><span><input type="text" name="module[name_class2]" id="name_class2" value="{if $MODULE.name_class2}{$MODULE.name_class2}{else}{$LANG.by_weight.rate_2}{/if}" class="textbox" /></span> {$LANG.by_weight.shipping_name_eg2}</div>
	<div><label for="tax">{$LANG.catalogue.tax_type}</label>
	  <span>
		<select name="module[tax]" id="tax">
		  {foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
		</select>
	  </span>
	</div>
	<div><label for="status">{$LANG.catalogue.tax_included}</label><span><input type="hidden" name="module[tax_included]" id="tax_included" class="toggle" value="{$MODULE.tax_included}" /></span></div>
	<div><label for="packagingWeight">{$LANG.by_weight.packaging_weight}</label><span><input name="module[packagingWeight]" id="packagingWeight" class="textbox" type="text" value="{$MODULE.packagingWeight}" /></span></div>
  </fieldset>
  {for $i = 1; $i <= 4; $i++}
  {assign var=zoneCountries value="zone`$i`Countries"}
  {assign var=zoneHandling value="zone`$i`Handling"}
  {assign var=zoneRatesClass1 value="zone`$i`RatesClass1"}
  {assign var=zoneRatesClass2 value="zone`$i`RatesClass2"}
  <fieldset><legend>{$LANG.by_weight.zone} {$i}</legend>
	<div><label for="zone{$i}Countries">{$LANG.by_weight.countries}</label><span><input name="module[zone{$i}Countries]" id="zone{$i}Countries" class="textbox" type="text" value="{$MODULE.$zoneCountries}" /></span></div>
	<div><label for="zone{$i}Handling">{$LANG.basket.shipping_handling}</label><span><input name="module[zone{$i}Handling]" id="zone{$i}Handling" class="textbox" type="text" value="{$MODULE.$zoneHandling}" /></span></div>
	<div><label for="zone{$i}RatesClass1">{if $MODULE.name_class1} {$MODULE.name_class1} {$LANG.by_weight.rate} {else} {$LANG.by_weight.rate_1} {$LANG.by_weight.rate} {/if}</label><span><input name="module[zone{$i}RatesClass1]" id="zone{$i}RatesClass1" class="textbox" type="text" value="{$MODULE.$zoneRatesClass1}" /></span></div>
	<div><label for="zone{$i}RatesClass2">{if $MODULE.name_class2} {$MODULE.name_class2} {$LANG.by_weight.rate} {else} {$LANG.by_weight.rate_2} {$LANG.by_weight.rate} {/if}</label><span><input name="module[zone{$i}RatesClass2]" id="zone{$i}RatesClass2" class="textbox" type="text" value="{$MODULE.$zoneRatesClass2}" /></span></div>
  </fieldset>
 {/for}
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>