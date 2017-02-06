{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
  <div id="zone-list" class="tab_content">
	<h3>{$LANG.settings.allowed_zones}</h3>
	<fieldset id="enabled-zones"><legend>{$LANG.module.title_regions_enabled}</legend>
	  {foreach from=$ENABLED_COUNTRIES item=country}
	  <div>
		<span class="actions"><a href="#" class="remove dynamic" title="{$LANG.messages.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></span>
		<input type="hidden" name="zones[]" value="{$country.numcode}">
		{$country.name}
	  </div>
	  {/foreach}
	</fieldset>

	<fieldset><legend>{$LANG.module.title_regions_add}</legend>
	  <div class="inline-add">
		<label for="add-zone">{$LANG.country.title_zone_add}</label>
		<span>
		  <select id="add-zone" name="zones[]" class="textbox add display">
			<option value="">{$LANG.form.please_select}</option>
			{foreach from=$ALL_COUNTRIES item=country}
			<option value="{$country.numcode}">{$country.name}</option>
			{/foreach}
		  </select>
		  <a href="#" class="add" target="enabled-zones"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a>
		</span>
	  </div>
	</fieldset>
  </div>