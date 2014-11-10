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
<h3>Auto Install/Upgrade Token</h3>
<p>Installing anything from the CubeCart Marketplace is a breeze using install tokens. Locate the item you want and click the thunderbolt icon (<i class="fa fa-bolt"></i>) next to the file name. This will generate your install token which can be used here to install/upgrade and downgrade.</p>
<fieldset>
	<legend>Token</legend>
    <div><label for="plugin_token">Token</label><span><input type="textbox" class="textbox" name="plugin_token" id="plugin_token" value="" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX"></span></div>
    <div><label><strong>Options</strong></label></div>
    <div><label for="backup">Backup if already exists</label><span><input type="hidden" id="backup" name="backup" value="1" class="toggle"></span></div>
    <div><label for="backup">Abort if backup fails</label><span><input type="hidden" id="abort" name="abort" value="1" class="toggle"></span></div>
    <div><label>&nbsp;</label><span><input type="submit" value="{$LANG.common.go}"></span></div>
</fieldset>

	<h3>Available Plugins</h3>
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
		<td nowrap>
		  <a href="?_g=plugins&type={$module.type}&module={$module.basename}" class="edit"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
		  <a href="?_g=plugins&type={$module.type}&module={$module.basename}&delete=1"  class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
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