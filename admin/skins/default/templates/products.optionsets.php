<form action="{$VAL_SELF}" method="post">
  <div id="assign" class="tab_content">
	<h3>{$LANG.catalogue.title_option_set_assign}</h3>
	<fieldset><legend>{$LANG.catalogue.title_option_sets}</legend>
	  <div class="list" style="height: 200px; overflow: auto;">
		{foreach from=$OPTION_SETS item=set}
		<div>
		  <span><input type="checkbox" name="set[]" value="{$set.set_id}"></span>
		  {$set.set_name}
		</div>
		{foreachelse}
		<div>{$LANG.catalogue.no_option_sets}</div>
		{/foreach}
	  </div>
	</fieldset>

	<fieldset><legend>{$LANG.catalogue.title_products}</legend>
	  <div class="list" style="height: 200px; overflow: auto;">
		{foreach from=$PRODUCTS item=product}
		<div>
		  <span><input type="checkbox" name="product[]" value="{$product.product_id}"></span>
		  <span style="float: right; margin: 5px 3px; display: inline;">{if !empty($product.product_code)}({$product.product_code}){/if}</span>
		  {$product.name}
		</div>
		{foreachelse}
		<div>{$LANG.catalogue.notify_inv_empty}</div>
		{/foreach}
	  </div>
	</fieldset>

  </div>
  
  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>