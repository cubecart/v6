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
{if $DISPLAY_FORM}
  <div id="general" class="tab_content">
  <h3>{$LANG.catalogue.title_import}</h3>
	<fieldset><legend>{$LANG.catalogue.title_import_csv}</legend>
	  <div><label for="import_source">{$LANG.catalogue.import_source} ({$LANG.common.max}: {$UPLOAD_LIMIT})</label><span><input type="file" name="source" id="import_source"></span></div>
	  <div><label for="import_format">{$LANG.catalogue.import_format}</label><span><select name="format" id="import_format">
		<option value="">{$LANG.catalogue.unknown_format}</option>
	  </select></span></div>
	  <div><label for="opt_delimiter">{$LANG.catalogue.delimiter}</label><span><select name="delimiter" id="opt_delimiter">
		<option value=",">,</option>
		<option value=";">;</option>
		<option value="tab">{$LANG.catalogue.delimiter_tab}</option>
	  </select></span></div>
	</fieldset>
	<input type="hidden" name="upload" value="true">
  </div>
  {if isset($REVERTS)}
  <div id="revert" class="tab_content">
	<fieldset><legend>{$LANG.catalogue.title_import_delete_previous}</legend>
	  <table>
	  {foreach from=$REVERTS item=revert}
	  <tr>
	     <td><input type="checkbox" name="revert[]" value="{$revert.date_added}"></td>
	     <td><span class="actions">{$revert.Count} {$LANG.catalogue.imported_products}</span></td>
	     <td>{$revert.date_added_fuzzy}</td>
	  </tr>
	  {/foreach}
	  </table>
	</fieldset>
  </div>
  {/if}
{/if}

  {if isset($DISPLAY_CONFIRMATION)}
  <div id="general" class="tab_content">
  <h3>{$LANG.catalogue.title_import}</h3>
	{if isset($EXAMPLES)}
	<fieldset><legend>{$LANG.catalogue.title_data_example}</legend>
	  {foreach from=$EXAMPLES item=example}
	  <div><strong>{$example.column}:</strong> {$example.value}</div>
	  {/foreach}
	</fieldset>
	<fieldset><legend>{$LANG.catalogue.title_import_options}</legend>
	  <div><label for="opt_truncate">{$LANG.catalogue.import_replace_existing}</label><span><input type="checkbox" name="option[truncate]" id="opt_truncate" value="1"></span></div>
	</fieldset>
	{/if}

	{if isset($MAPS)}
	<fieldset>
	  {foreach from=$MAPS item=map}
	  <div rel="{$map.offset}">
		<label>{$map.example}</label>
		<span>
		  <select name="map[{$map.offset}]" class="unique">
			<option value="">{$LANG.catalogue.import_column_ignore}</option>
			{foreach from=$COLUMNS item=column}<option value="{$column.column}">{$column.title}</option>{/foreach}
		  </select>
		</span>
	  </div>
	  {/foreach}
	</fieldset>
	<fieldset><legend>{$LANG.catalogue.title_import_options}</legend>
	  <div><label for="opt_headers">{$LANG.catalogue.import_csv_headers}</label><span><input type="checkbox" name="option[headers]" id="opt_headers" value="1"></span></div>
	  <div><label for="opt_truncate">{$LANG.catalogue.import_replace_existing}</label><span><input type="checkbox" name="option[truncate]" id="opt_truncate" value="1"></span></div>
	  <input type="hidden" name="delimiter" value="{$IMPORT.delimiter}">
	</fieldset>
	{/if}

	<input type="hidden" name="process" value="{$IMPORT.format}">
  </div>
  {/if}
  
  {include file='templates/element.hook_form_content.php'}
  <p>{$LANG.catalogue.import_disclaimer}</p>
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  
</form>