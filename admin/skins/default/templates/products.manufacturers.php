{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2025. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if $DISPLAY_LIST}
  <div id="manufacturers" class="tab_content">
	<h3>{$LANG.catalogue.title_manufacturer}</h3>
	{if isset($MANUFACTURERS)}
	<table>
	  <thead>
		<tr>
		  <td width="250">{$LANG.catalogue.manufacturer}</td>
		  <td>{$LANG.form.action}</td>
		</tr>
	  </thead>
	  <tbody>
		{foreach from=$MANUFACTURERS item=manufacturer}
		<tr>
		  <td><a href="?_g=products&node=manufacturers&edit={$manufacturer.id}#add-edit">{$manufacturer.name}</a></td>
		  <td>
	    	<a href="?_g=products&node=manufacturers&edit={$manufacturer.id}#add-edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
	    	{if $manufacturer.has_url}<a href="{$manufacturer.URL}" target="_blank" title="{$manufacturer.URL}"><i class="fa fa-external-link"></i></a>{/if}
	    	<a href="?_g=products&node=manufacturers&delete={$manufacturer.id}&token={$SESSION_TOKEN}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
		  </td>
		</tr>
		{/foreach}
	  </tbody>
	</table>
	<div class="pagination"><span><strong>{number_format($TOTAL_RESULTS)}</strong></span>{$PAGINATION}</div>
	{else}
	<p>{$LANG.catalogue.error_manufacturer_none}</p>
	{/if}
  </div>
  <div id="manu_add" class="tab_content">
	<h3>{$LANG.catalogue.title_manufacturer_add}</h3>
	{include file='templates/products.manufacturers.fields.php'}
	</fieldset>
	{if isset($PLUGIN_TABS)}
	{foreach from=$PLUGIN_TABS item=tab}
		{$tab}
	{/foreach}
	{/if}
	{include file='templates/element.hook_form_content.php'}
	<div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.form.submit}" class="submit">
  </div>
  </div>

  {/if}

  {if $DISPLAY_FORM}
  <div id="manu_edit" class="tab_content">
	<h3>{$LANG.catalogue.title_manufacturer_edit}</h3>
	{include file='templates/products.manufacturers.fields.php'}
	</fieldset>
	{if isset($PLUGIN_TABS)}
	{foreach from=$PLUGIN_TABS item=tab}
		{$tab}
	{/foreach}
	{/if}
	{include file='templates/element.hook_form_content.php'}
	<div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.form.submit}" class="submit">
  </div>
  </div>
  {/if}
  
</form>
<script type="text/javascript">
	var county_list = {if !empty($JSON_STATE)}{$JSON_STATE}{else}false{/if};
</script>