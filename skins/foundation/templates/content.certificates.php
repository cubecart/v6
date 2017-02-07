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
<form id="gc_form" action="{$VAL_SELF}" method="post">
  <h2>{$LANG.catalogue.gift_certificates}</h2>
  <p>{$LANG_CERT_VALUES}</p>
	<div class="row"><div class="small-12 large-8 columns"><label for="gc-value">{$LANG.common.value} ({$CONFIG.default_currency})</label><input type="text" name="gc[value]" id="gc-value" value="{$POST.value}" placeholder="{$LANG.common.value} {$LANG.form.required}" required></div></div>
	<div class="row"><div class="small-12 large-8 columns">
	  <label for="gc-method">{$LANG.catalogue.delivery_method}</label>
		<select name="gc[method]" id="gc-method">
		  {if in_array($GC.delivery, array(1,3))}<option value="e">{$LANG.common.email}</option>{/if}
		  {if in_array($GC.delivery, array(2,3))}<option value="m">{$LANG.common.post}</option>{/if}
		</select>
	  </div>
	</div>
	<div class="row"><div class="small-12 large-8 columns"><label for="gc-name">{$LANG.catalogue.recipient_name}</label><input type="text" name="gc[name]" id="gc-name" value="{$POST.name}" placeholder="{$LANG.catalogue.recipient_name} {$LANG.form.required}" required></div></div>
	{if in_array($GC.delivery, array(1,3))}
	<div class="row"><div class="small-12 large-8 columns" id="gc-method-e"><label for="gc-email">{$LANG.catalogue.recipient_email}</label><input type="text" name="gc[email]" id="gc-email" placeholder="{$LANG.catalogue.recipient_email} {$LANG.form.required}" value="{$POST.email}"></div></div>
	{/if}
	<div class="row"><div class="small-12 large-8 columns"><label for="gc-message">{$LANG.common.message} {$LANG.common.optional}</label><span><textarea name="gc[message]" id="gc-message">{$POST.message}</textarea></div></div>
  <div><input type="submit" class="button" name="Submit" value="{$LANG.catalogue.add_to_basket}"{if !$ctrl_allow_purchase} disabled="disabled"{/if}></div>
</form>
<div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
<div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>