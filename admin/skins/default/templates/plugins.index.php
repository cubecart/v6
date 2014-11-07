{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 *}
<form action="{$VAL_SELF}" method="post">

<div id="plugins" class="tab_content">
<h3>Install/Upgrade/Downgrade Plugin</h3>

<fieldset>
	<p>Installing anything from the CubeCart Marketplace is a breeze using install tokens. Locate the item you want and click the thunderbolt icon (<i class="fa fa-bolt"></i>) next to the file name.</p>
    <div><label for="plugin_token">Plugin Token</label><span><input type="textbox" class="textbox" name="plugin_token" id="plugin_token" value="" placeholder="e.g. XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX"> <input type="submit" value="{$LANG.common.go}"></span></div>
</fieldset>

	<h3>Installed Plugins</h3>
	{if is_array($MODULES)}
	<table>
	<thead>
		<tr>
		<th>Status</th>
		<th>Name &amp; Description</th>
		<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$MODULES item=module}
	  <tr>
	  	<td align="center">
	  		<input type="hidden" id="status_{$module.basename}" name="status[{$module.basename}]" value="{$module.config.status}" class="toggle">
	  		<input type="hidden" name="type[{$module.basename}]" value="{$module.type}" />
	  	</td>
		<td><a href="?_g=plugins&type={$module.type}&module={$module.basename}">{$module.name}</a><br>{$module.description}</td>
		<td>
		  <a href="?_g=plugins&type={$module.type}&module={$module.basename}" class="edit"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
		  {if $module.mobile_optimized=='true'}
		  <a href="javascript:alert('{$LANG.module.mobile_optimized}');"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/phone.png" title="{$LANG.module.mobile_optimized}"></a>
		  {/if}
		</td>
	  </tr>
	{/foreach}
	</tbody>
	</table>
	{include file='templates/element.hook_form_content.php'}
	<div class="form_control">
		<input type="submit" value="{$LANG.common.save}">
	</div>
	{else}
	<p>{$LANG.form.none}</p>
	{/if}
		<input type="hidden" name="token" value="{$SESSION_TOKEN}">
	</form>
</div>