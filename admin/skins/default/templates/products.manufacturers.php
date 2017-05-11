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
		  <td>{$manufacturer.name}</td>
		  <td>
	    	<a href="?_g=products&node=manufacturers&edit={$manufacturer.id}#add-edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
	    	<a href="?_g=products&node=manufacturers&delete={$manufacturer.id}&token={$SESSION_TOKEN}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
		  </td>
		</tr>
		{/foreach}
	  </tbody>
	</table>
	<div class="pagination"><span>{$TOTAL_RESULTS}</span>{$PAGINATION}</div>
	{else}
	<p>{$LANG.catalogue.error_manufacturer_none}</p>
	{/if}
  </div>
  <div id="manu_add" class="tab_content">
	<h3>{$LANG.catalogue.title_manufacturer_add}</h3>
	<fieldset><legend>{$LANG.catalogue.title_manufacturer_add}</legend>
		<div><label for="manu_name">{$LANG.catalogue.manufacturer}</label><span><input type="text" class="textbox required" id="manu_name" name="manufacturer[name]" value="{$EDIT.name}"></span></div>
		<div><label for="manu_site">{$LANG.common.url}</label><span><input type="text" class="textbox" id="manu_site" name="manufacturer[URL]" value="{$EDIT.URL}"></span></div>
	</fieldset>
	
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
	<fieldset><legend>{$LANG.catalogue.title_manufacturer_edit}</legend>
		<div><label for="manu_name">{$LANG.catalogue.manufacturer}</label><span><input type="text" class="textbox required" id="manu_name" name="manufacturer[name]" value="{$EDIT.name}"></span></div>
		<div><label for="manu_site">{$LANG.common.url}</label><span><input type="text" class="textbox" id="manu_site" name="manufacturer[URL]" value="{$EDIT.URL}"></span></div>
	</fieldset>
	<div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.form.submit}" class="submit">
  </div>
  </div>
  {if isset($PLUGIN_TABS)}
	{foreach from=$PLUGIN_TABS item=tab}
		{$tab}
	{/foreach}
  {/if}
  {/if}
  
</form>
