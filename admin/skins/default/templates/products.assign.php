<form action="{$VAL_SELF}" method="post">
  <div id="assign" class="tab_content">
	<h3>{$LANG.catalogue.title_category_assign_to}</h3>
	<fieldset><legend>{$LANG.catalogue.title_products}</legend>
	  <div class="list" style="height: 200px; overflow: auto;">
	  	<input type="checkbox" name="" value="" id="product_check">{$LANG.form.check_uncheck}
		{foreach from=$PRODUCTS item=product}
		<div>
		  <span style="float: right; margin: 5px 3px; display: inline;">{if !empty($product.product_code)}({$product.product_code}){/if}</span>
		  <span><input type="checkbox" name="product[]" value="{$product.product_id}"></span>
		  {$product.name}
		</div>
		{foreachelse}
		<div>{$LANG.catalogue.notify_inv_empty}</div>
		{/foreach}
	  </div>
	</fieldset>

	<fieldset><legend>{$LANG.catalogue.title_prices_update}</legend>
	  <div>
		<select name="price[what]">
		  <option value="products">{$LANG.catalogue.update_checked_products}</option>
		  <option value="categories">{$LANG.catalogue.update_checked_categories}</option>
		</select>
		<select name="price[method]">
		  <option value="fixed">{$LANG.catalogue.update_by_amount}</option>
		  <option value="percent">{$LANG.catalogue.update_by_percent}</option>
		</select>
		<select name="price[action]">
		  <option value="0">{$LANG.common.subtract}</option>
		  <option value="1">{$LANG.common.add}</option>
		</select>
		<input type="text" name="price[value]" value="" class="textbox number">
	  </div>
	</fieldset>
	{if isset($CATEGORIES)}
	<fieldset><legend>{$LANG.settings.title_category}</legend>
	  <div class="list" style="height: 200px; overflow: auto;">
		{foreach from=$CATEGORIES item=category}
		<div>
		  <span><input type="checkbox" name="category[]" value="{$category.id}"></span>
		  {$category.name}
		</div>
		{/foreach}
	  </div>
	</fieldset>
	{/if}
  </div>
  
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>