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
<form action="{$VAL_SELF}" method="post">
  <div id="assign" class="tab_content">
	<h3>{$LANG.catalogue.title_option_set_assign}</h3>
	<fieldset><legend>{$LANG.catalogue.title_option_sets}</legend>
		{foreach from=$OPTION_SETS item=set}
		<div>
		  <span><input type="checkbox" name="set[]" value="{$set.set_id}"></span>
		  {$set.set_name}
		</div>
		{foreachelse}
		<div>{$LANG.catalogue.no_option_sets}</div>
		{/foreach}
	</fieldset>

	<fieldset><legend>{$LANG.catalogue.title_products}</legend>
		{foreach from=$PRODUCTS item=product}
		<div>
		  <span><input type="checkbox" name="product[]" value="{$product.product_id}"></span>
		  {$product.name} {if !empty($product.product_code)}({$product.product_code}){/if}
		</div>
		{foreachelse}
		<div>{$LANG.catalogue.notify_inv_empty}</div>
		{/foreach}
	</fieldset>
  </div>
  
  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  
</form>