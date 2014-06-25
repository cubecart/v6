<div id="plugins" class="tab_content">
	<h3>Installed Plugins</h3>
	<!--
	<p>{$LANG.form.sort_by} 
	  <select name="order" class="auto_submit show_submit">
	    <option value="alpha"{$ORDER_SELECT.alpha}>{$LANG.common.name}</option>
	    <option value="pop"{$ORDER_SELECT.pop}>{$LANG.common.popularity}</option>
	  </select>
	</p>
	-->
	{if $PLUGINS_LINK}
	<p>{$LANG.gateway.plugins_link}</p>
	{/if}
	<table class="list">
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
	  	<td align="center"><input type="hidden" id="status_{$module.basename}" name="status[{$module.basename}]" value="{$module.config.status}" class="toggle"></td>
		<td><a href="?_g=modules&type={$module.type}&module={$module.basename}">{$module.name}</a><br>{$module.description}</td>
		<td>
		  <a href="?_g=modules&type={$module.type}&module={$module.basename}" class="edit"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		  {if $module.mobile_optimized=='true'}
		  <a href="javascript:alert('{$LANG.module.mobile_optimized}');"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/phone.png" title="{$LANG.module.mobile_optimized}"></a>
		  {/if}
		</td>
	  </tr>
	{/foreach}
	</tbody>
	</table>
	
	 {include file='templates/element.hook_form_content.php'}
	
</div>