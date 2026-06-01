{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<form action="?" method="get">
  <div id="export" class="tab_content">
	<h3>{$LANG.catalogue.title_export}</h3>
	<p>{$LANG.catalogue.export_explain}</p>
	<div>
	  {$LANG.catalogue.export_products_per}
		<select class="auto_submit textbox" name="per_page">
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
	    <tr style="vertical-align:top"><td style="padding-top:6px">{$format.name}</td><td>{$format.parts}</td><td style="text-align:center;padding-top:6px"><a href="{$format.link}" target="_blank" class="button tiny" title="{$LANG.catalogue.export_url}"><i class="fa fa-link"></i></a></td></tr>
	  {/foreach}
	  </tbody> 
	</table>
  </div>
  
</form>