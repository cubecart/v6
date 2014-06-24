<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="exchange" class="tab_content">
	<h3>{$LANG.settings.title_currency}</h3>
	<table class="list">
	  <thead>
	  <tr>
		<td align="center">{$LANG.common.status}</td>
		<td align="center" width="70">{$LANG.settings.currency_iso}</td>
		<td align="center" width="302">{$LANG.settings.currency_name}</td>
		<td align="center">{$LANG.settings.currency_symbol_left}</td>
		<td align="center" width="102">{$LANG.settings.currency_exchange_rate}</td>
		<td align="center">{$LANG.settings.currency_symbol_right}</td>
		<td align="center">{$LANG.settings.currency_decimal_places}</td>
		<td align="center" width="120">{$LANG.settings.currency_updated}</td>
		<td align="center" width="70">{$LANG.common.delete}</td>
	  </tr>
	  </thead>
	  <tbody>
	  {foreach from=$CURRENCIES item=currency}
	  <tr>
		<td align="center"><input type="hidden" name="currency[{$currency.code}][active]" id="{$currency.code}" class="toggle" value="{$currency.active}"></td>
		<td align="center"><strong>{$currency.code}</strong></td>
		<td><span class="editable" name="currency[{$currency.code}][name]">{$currency.name}</span></td>
		<td><input type="text" name="currency[{$currency.code}][symbol_left]" class="textbox number edited" value="{$currency.symbol_left}"></td>
		<td align="right"><span class="editable number-right" name="currency[{$currency.code}][value]">{$currency.value}</span></td>
		<td><input type="text" name="currency[{$currency.code}][symbol_right]" class="textbox number edited" value="{$currency.symbol_right}"></td>
		<td><input type="text" name="currency[{$currency.code}][decimal_places]" class="textbox number edited" value="{$currency.decimal_places}"></td>
		<td align="center">{$currency.updated}</td>
		<td align="center"><a href="{$VAL_SELF}&delete={$currency.code}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a></td>
	  </tr>
	  {/foreach}
	  </tbody>
	</table>
	
	{include file='templates/element.hook_form_content.php'}
	
	<div class="form_control">
	  <input type="submit" name="update_manual" class="button" value="{$LANG.common.save}"> &nbsp;
	  <input type="submit" name="autoupdate" class="button" value="{$LANG.settings.currency_ecb}">
	</div>
  </div>
  <div id="addrate" class="tab_content">
  <h3>{$LANG.settings.title_currency_add}</h3>
	<fieldset>
	  <div><label for="currency-name">{$LANG.settings.currency_name}</label><span><input name="add[name]" id="currency-name" type="text" class="textbox required"></span></div>
	  <div><label for="currency-code"><a href="http://en.wikipedia.org/wiki/ISO_4217" target="_blank">{$LANG.settings.currency_iso}</a></label><span><input name="add[code]" id="currency-code" type="text" class="textbox number required"></span></div>
	  <div><label for="currency-code">{$LANG.settings.currency_exchange_rate}</label><span><input name="add[value]" id="currency-code" type="text" class="textbox number required"></span></div>
	  <div><label for="currency-decimal">{$LANG.settings.currency_decimal_places}</label><span><input name="add[decimal_places]" id="currency-code" type="text" class="textbox number required"></span></div>
	  <div><label for="currency-left">{$LANG.settings.currency_symbol_left}</label><span><input name="add[symbol_left]" id="currency-code" type="text" class="textbox number"></span></div>
	  <div><label for="currency-right">{$LANG.settings.currency_symbol_right}</label><span><input name="add[symbol_right]" id="currency-code" type="text" class="textbox number"></span></div>
	</fieldset>
	
	{include file='templates/element.hook_form_content.php'}
	
	<div class="form_control">
	  <input type="hidden" name="save" value="{$FORM_HASH}">
	  <input type="hidden" name="previous-tab" id="previous-tab" value="">
	  <input type="submit" name="update_manual" class="button" value="{$LANG.common.save}"> &nbsp;
	</div>
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>