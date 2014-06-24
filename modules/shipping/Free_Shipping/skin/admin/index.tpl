<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="Free_Shipping" class="tab_content">
  <h3>{$TITLE}</h3>
  <p>{$LANG.free_shipping.module_description}</p>
  <fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	<div><label for="name">{$LANG.common.name}</label><span><input type="text" name="module[name]" id="name" value="{$MODULE.name}" class="textbox" /></span> {$LANG.module.shipping_name_eg}</div>
	<div><label for="minimum-subtotal">{$LANG.free_shipping.min_subtotal}</label><span><input name="module[trigger]" id="trigger" class="textbox" type="text" value="{$MODULE.trigger}" /></span></div>
	<div><label for="maximum-orderweight">{$LANG.free_shipping.max_orderweight}</label><span><input name="module[maxweight]" id="maxweight" class="textbox" type="text" value="{$MODULE.maxweight}" /></span></div>
  </fieldset>
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
	<input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>
