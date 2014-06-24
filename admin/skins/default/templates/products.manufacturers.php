<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if $DISPLAY_LIST}
  <div id="manufacturers" class="tab_content">
	<h3>{$LANG.catalogue.title_manufacturer}</h3>
	{if isset($MANUFACTURERS)}
	<table class="list">
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
	    	<a href="?_g=products&node=manufacturers&edit={$manufacturer.id}#add-edit" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
	    	<a href="?_g=products&node=manufacturers&delete={$manufacturer.id}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
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

  {/if}
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>