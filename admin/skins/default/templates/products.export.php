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
<form action="?" method="get">
  <div id="export" class="tab_content">
	<h3>{$LANG.catalogue.title_export}</h3>
	<p>{$LANG.catalogue.export_explain}</p>
	<div>
	  {$LANG.catalogue.export_products_per}
		<select class="auto_submit" name="per_page">
		{foreach from=$LIMITS item=limit}<option value="{$limit.per_page}"{$limit.selected}>{$limit.per_page}</option>{/foreach}
		</select>
		<input type="hidden" name="_g" value="products">
		<input type="hidden" name="node" value="export">
		<input type="submit" value="{$LANG.common.go}" style="display: none;"/>
	</div>
	<table>
	  <thead>
	  	<th>{$LANG.email.export_format}</th><th>{$LANG.catalogue.export_parts}</th><th>{$LANG.catalogue.export_url}</th>
	  </thead>
	  <tbody>
	  {foreach from=$FORMATS item=format}
	    <tr><td>{$format.name}</td><td style="text-align:center">{$format.parts}</td><td style="text-align:center"><a href="{$format.link}" target="_blank"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/link.png" alt=""></a></td></tr>
	  {/foreach}
	  </tbody> 
	</table>
  </div>
  
</form>