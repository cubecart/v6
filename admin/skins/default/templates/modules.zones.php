  <div id="zone-list" class="tab_content">
	<h3>{$LANG.settings.allowed_zones}</h3>
	<fieldset id="enabled-zones" class="list"><legend>{$LANG.module.title_regions_enabled}</legend>
	  {foreach from=$ENABLED_COUNTRIES item=country}
	  <div>
		<span class="actions"><a href="#" class="remove dynamic" title="{$LANG.messages.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a></span>
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
		  <a href="#" class="add" target="enabled-zones"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/add.png" alt="{$LANG.common.add}"></a>
		</span>
	  </div>
	</fieldset>
  </div>