<div id="marketplace" class="tab_content">
	<h3>{$LANG.navigation.nav_marketplace}</h3>
</div>
<div id="plugins" class="tab_content">
	<h3>Installed Plugins</h3>
	
	<p>{$LANG.form.sort_by} 
	  <select name="order" class="auto_submit show_submit">
	    <option value="alpha"{$ORDER_SELECT.alpha}>{$LANG.common.name}</option>
	    <option value="pop"{$ORDER_SELECT.pop}>{$LANG.common.popularity}</option>
	  </select>
	</p>
	{if $PLUGINS_LINK}
	<p>{$LANG.gateway.plugins_link}</p>
	{/if}
	<div class="list">
	{foreach from=$MODULES item=module}
	  <div class="module">
		<span class="actions">
		  {if $module.mobile_optimized=='true'}
		  <a href="javascript:alert('{$LANG.module.mobile_optimized}');"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/phone.png"></a>
		  {/if}
		  <a href="?_g=modules&type={$module.type}&module={$module.node}" class="edit">
			<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}">
		  </a>
		</span>
		<span class="toggle"><input type="hidden" id="status_{$module.basename}" name="status[{$module.basename}]" value="{$module.config.status}" class="toggle"></span>
		<a href="?_g=modules&type={$module.type}&module={$module.node}">{$module.name}</a>
	  </div>
	{/foreach}
	</div>
	
	 {include file='templates/element.hook_form_content.php'}
	
</div>