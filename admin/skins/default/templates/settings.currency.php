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
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="exchange" class="tab_content">
	<h3>{$LANG.settings.title_currency}</h3>
	<table>
	  <thead>
	  <tr>
		<td style="text-align:center">{$LANG.common.status}</td>
		<td style="text-align:center">{$LANG.settings.currency_iso}</td>
		<td style="text-align:center">{$LANG.settings.currency_name}</td>
		<td style="text-align:center">{$LANG.settings.currency_symbol_left}</td>
		<td style="text-align:center">{$LANG.settings.currency_exchange_rate}</td>
		<td style="text-align:center">{$LANG.settings.currency_symbol_right}</td>
		<td style="text-align:center">{$LANG.settings.currency_decimal_places}</td>
		<td style="text-align:center">{$LANG.settings.currency_symbol_decimal}</td>
		<td style="text-align:center">{$LANG.settings.currency_symbol_thousand}</td>
		<td style="text-align:center">{$LANG.settings.currency_updated}</td>
		<td style="text-align:center">{$LANG.common.delete}</td>
	  </tr>
	  </thead>
	  <tbody>
	  {foreach from=$CURRENCIES item=currency}
	  <tr>
		<td style="text-align:center"><input type="hidden" name="currency[{$currency.code}][active]" id="{$currency.code}" class="toggle" value="{$currency.active}"></td>
		<td><strong>{$currency.code}</strong></td>
		<td><span class="editable" name="currency[{$currency.code}][name]">{$currency.name}</span></td>
		<td style="text-align:center"><input type="text" name="currency[{$currency.code}][symbol_left]" class="textbox number edited" value="{$currency.symbol_left}"></td>
		<td style="text-align:right"><span class="editable number-right" name="currency[{$currency.code}][value]">{$currency.value}</span></td>
		<td style="text-align:center"><input type="text" name="currency[{$currency.code}][symbol_right]" class="textbox number edited" value="{$currency.symbol_right}"></td>
		<td style="text-align:center"><input type="text" name="currency[{$currency.code}][decimal_places]" class="textbox number edited" value="{$currency.decimal_places}"></td>
		<td style="text-align:center"><input type="text" name="currency[{$currency.code}][symbol_decimal]" class="textbox number edited" value="{$currency.symbol_decimal}" maxlength="10"></td>
		<td style="text-align:center"><input type="text" name="currency[{$currency.code}][symbol_thousand]" class="textbox number edited" value="{$currency.symbol_thousand}" maxlength="10"></td>
		<td style="text-align:center">{if $currency.updated}{$currency.updated}{else}{$LANG.common.unknown}{/if}</td>
		<td style="text-align:center"><a href="{$VAL_SELF}&delete={$currency.code}&token={$SESSION_TOKEN}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
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
	<fieldset><legend>{$LANG.settings.title_currency_add}</legend>
	  <div><label for="currency-active">{$LANG.common.status}</label><span><input name="add[active]" id="currency-active" type="hidden" class="toggle" value=""></span></div>
	  <div><label for="currency-name">{$LANG.settings.currency_name}</label><span><input name="add[name]" id="currency-name" type="text" class="textbox required"></span></div>
	  <div><label for="currency-code"><a href="http://en.wikipedia.org/wiki/ISO_4217" target="_blank">{$LANG.settings.currency_iso}</a></label><span><input name="add[code]" id="currency-code" type="text" class="textbox number required"></span></div>
	  <div><label for="currency-value">{$LANG.settings.currency_exchange_rate}</label><span><input name="add[value]" id="currency-value" type="text" class="textbox number required"></span></div>
	  <div><label for="currency-decimal_places">{$LANG.settings.currency_decimal_places}</label><span><input name="add[decimal_places]" id="currency-decimal_places" type="text" class="textbox number required"></span></div>
	  <div><label for="currency-symbol_left">{$LANG.settings.currency_symbol_left}</label><span><input name="add[symbol_left]" id="currency-symbol_left" type="text" class="textbox number"></span></div>
	  <div><label for="currency-symbol_right">{$LANG.settings.currency_symbol_right}</label><span><input name="add[symbol_right]" id="currency-symbol_right" type="text" class="textbox number"></span></div>
	  <div><label for="currency-symbol_decimal">{$LANG.settings.currency_symbol_decimal}</label><span><input name="add[symbol_decimal]" id="currency-symbol_decimal" type="text" class="textbox number"></span></div>
	  <div><label for="currency-symbol_thousand">{$LANG.settings.currency_symbol_thousand}</label><span><input name="add[symbol_thousand]" id="currency-symbol_thousand" type="text" class="textbox number"></span></div>
	</fieldset>
	
	{include file='templates/element.hook_form_content.php'}
	
	<div class="form_control">
	  <input type="hidden" name="save" value="{$FORM_HASH}">
	  <input type="hidden" name="previous-tab" id="previous-tab" value="">
	  <input type="submit" name="update_manual" class="button" value="{$LANG.common.save}"> &nbsp;
	</div>
  </div>
  
</form>