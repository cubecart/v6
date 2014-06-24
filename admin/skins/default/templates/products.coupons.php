<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if isset($DISPLAY_COUPONS)}

  <div id="coupons" class="tab_content">
	<h3>{$LANG.catalogue.title_coupons}</h3>
	<table class="list">
	  <thead>
		<tr>
		  <td>{$THEAD_COUPON.status}</td>
		  <td>{$THEAD_COUPON.code}</td>
		  <td>{$THEAD_COUPON.value}</td>
		  <td>{$THEAD_COUPON.expires}</td>
		  <td>{$THEAD_COUPON.time_used}</td>
		  <td>&nbsp;</td>
		</tr>
	  </thead>
	  <tbody>
		{foreach from=$COUPONS item=coupon}
		<tr>
		  <td align="center"><input type="hidden" id="status_{$coupon.coupon_id}" name="status[{$coupon.coupon_id}]" value="{$coupon.status}" class="toggle"></td>
		  <td><a href="{$coupon.link_edit}" class="edit" title="{$LANG.common.edit}">{$coupon.code}</a></td>
		  <td>{$coupon.value}</td>
		  <td>{$coupon.expires}</td>
		  <td align="center">{$coupon.count} / {$coupon.allowed_uses}</td>
		  <td>
			<a href="{$coupon.link_edit}" class="edit" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
			<a href="{$coupon.link_delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		  </td>
		</tr>
		{foreachelse}
		<tr>
		  <td align="center" colspan="6">{$LANG.catalogue.notify_coupons_none}</td>
		</tr>
		{/foreach}
	  </tbody>
	</table>
	{$PAGINATION_COUPONS}
  </div>

  <div id="certificates" class="tab_content">
	<h3>{$LANG.catalogue.gift_certificates}</h3>
	<table class="list">
	  <thead>
		<tr>
		  <td>{$THEAD_CERTIFICATE.status}</td>
		  <td>{$THEAD_CERTIFICATE.code}</td>
		  <td>{$THEAD_CERTIFICATE.value}</td>
		  <td>{$THEAD_CERTIFICATE.expires}</td>
		  <td>{$THEAD_CERTIFICATE.cart_order_id}</td>
		</tr>
	  </thead>
	  <tbody>
		{if isset($CERTIFICATES)}
		{foreach from=$CERTIFICATES item=certificate}
		<tr>
		  <td align="center"><input type="hidden" id="status_{$certificate.coupon_id}" name="status[{$certificate.coupon_id}]" value="{$certificate.status}" class="toggle"></td>
		  <td>{$certificate.code}</td>
		  <td>{$certificate.value}</td>
		  <td>{$certificate.expires}</td>
		  <td><a href="?_g=orders&action=edit&order_id={$certificate.cart_order_id}">{$certificate.cart_order_id}</a></td>
		</tr>
		{/foreach}
		{else}
		<tr>
		  <td align="center" colspan="5">{$LANG.catalogue.notify_certs_none}</td>
		</tr>
		{/if}
	  </tbody>
	</table>
	{$PAGINATION_CERTIFICATES}
  </div>
 {/if}


 {if isset($DISPLAY_FORM)}
  <div id="edit-coupon" class="tab_content">
	<h3>{$LEGEND}</h3>
	<fieldset><legend>{$LANG.catalogue.title_coupon_detail}</legend>
	  <div><label for="form-code">{$LANG.catalogue.coupon_code}</label><span><input type="text" name="coupon[code]" id="form-code" value="{$COUPON.code}" class="textbox"></span></div>
	  <div><label for="form-description">{$LANG.common.description}</label><span><textarea name="coupon[description]" id="form-description" class="textbox">{$COUPON.description}</textarea></span></div>
	</fieldset>
	<fieldset><legend>{$LANG.catalogue.title_coupon_value}</legend>
	  <div>
		<label for="form-type">{$LANG.catalogue.discount_type}</label>
		<span>
		  <select name="discount_type" id="form-type">
			{foreach from=$DISCOUNTS item=discount}
			<option value="{$discount.index}" {$discount.selected}>{$discount.title}</option>
			{/foreach}
		  </select>
		</span>
	  </div>
	  <div><label for="form-value">{$LANG.catalogue.discount_value}</label><span><input type="text" name="discount_value" id="form-value" value="{$COUPON.discount_value}" class="textbox number"></span></div>
	</fieldset>
	<fieldset><legend>{$LANG.catalogue.title_coupon_limits}</legend>
	  <div><label for="form-expires">{$LANG.catalogue.title_coupon_expires} (YYYY-MM-DD)</label><span><input type="text" name="coupon[expires]" id="form-expires" value="{$COUPON.expires}" class="textbox date number"></span></div>
	  <div><label for="form-allowed">{$LANG.catalogue.allowed_uses}</label><span><input type="text" name="coupon[allowed_uses]" id="form-allowed" value="{$COUPON.allowed_uses}" class="textbox number"></span></div>
	  {if $DISPLAY_TIMES_USED}
	  <div><label>{$LANG.catalogue.title_coupon_count}</label><span><input type="text" disabled="disabled" readonly="readonly" class="textbox number" value="{$COUPON.count}"></span></div>
	  {/if}
	  <div><label for="form-minimum">{$LANG.catalogue.minimum_subtotal}</label><span><input type="text" name="coupon[min_subtotal]" id="form-minimum" value="{$COUPON.min_subtotal}" class="textbox number"></span></div>
	  <div><label for="form-shipping">{$LANG.catalogue.coupon_shipping}</label><span><input type="hidden" name="coupon[shipping]" id="form-shipping" class="toggle" value="{$COUPON.shipping}"></span></div>
	  <input type="hidden" name="coupon[coupon_id]" value="{$COUPON.coupon_id}">
	</fieldset>
  </div>
  <div id="edit-products" class="tab_content">
	<fieldset>
	  <div id="assigned-prods" class="list">
	  	{foreach from=$PRODUCTS item=product}
		<div>
		  <span class="actions"><a href="#" class="remove" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a></span>
		  <input type="hidden" name="product[]" value="{$product.product_id}">{$product.name}
		</div>
		{/foreach}
	  </div>
	  <div>
		<label for="form-product">{$LANG.common.product}</label>
		<span>
		  <input type="hidden" name="product[]" id="result_form-product" class="add">

		  <input type="text" id="form-product" rel="product" class="ajax textbox add display">

		  <a href="#" target="assigned-prods" class="add"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/add.png" alt="{$LANG.common.add}"></a>
		</span>
	  </div>
	  <div><label for="form-subtotal">{$LANG.catalogue.coupon_subtotal}</label><span><input type="hidden" name="coupon[subtotal]" id="form-subtotal" class="toggle" value="{$COUPON.subtotal}"></span></div>
	  <div>{$LANG.catalogue.coupon_no_shipping}</div>
	</fieldset>
	<fieldset><legend>{$LANG.catalogue.title_coupon_products}</legend>
	  <div>
		<label for="prod-list">{$LANG.catalogue.title_product_list}:</label>
		<span>
		  <select name="incexc" id="prod-list">
			{foreach from=$INCEXC item=incexc}
			<option value="{$incexc.index}" {$incexc.selected}>{$incexc.title}</option>
			{/foreach}
    	  </select>
		</span>
	  </div>
	</fieldset>
  </div>
  {/if}

  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input type="hidden" name="save" value="{$FORM_HASH}">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>
