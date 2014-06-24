<div>
  <form action="{$VAL_SELF}" method="post">
  {if isset($DISPLAY_LIST)}
  <div id="customer-list" class="tab_content">
    <h3>{$LANG.customer.title_list}</h3>
    <p>
	<strong>{$LANG.customer.title_key_type}</strong>
	  <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/user_registered.png" alt="{$LANG.customer.title_key_registered}"> - {$LANG.customer.title_key_registered}
	  <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/user_ghost.png" alt="{$LANG.customer.title_key_unregistered}"> - {$LANG.customer.title_key_unregistered}
	</p>
	<table>
	  <thead>
		<tr>
		  <td>{$THEAD.status}</td>
		  <td>{$THEAD.type}</td>
		  <td>{$THEAD.customer}</td>
		  <td>{$THEAD.email}</td>
		  <td>{$THEAD.registered}</td>
		  <td>{$THEAD.no_orders}</td>
		  <td>&nbsp;</td>
		</tr>
	  </thead>
	  <tbody class="list">
		{foreach from=$CUSTOMERS item=customer}
		<tr>
		  <td align="center"><input type="hidden" name="status[{$customer.customer_id}]" id="status_{$customer.customer_id}" value="{$customer.status}" class="toggle"></td>
		  <td align="center">
		  	{if $customer.type==1}
		  	<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/user_registered.png" alt="{$LANG.customer.title_key_registered}">
		  	{elseif $customer.type==2}
		  	<img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/user_ghost.png" alt="{$LANG.customer.title_key_unregistered}">
		  	{/if}
		  </td>
		  <td><a href="{$customer.edit}">{$customer.last_name}, {$customer.first_name}</a> {if !empty($customer.groups)}({$customer.groups}){/if}</td>
		  <td>{$customer.email}</td>
		  <td>{$customer.registered}</td>
		  <td align="center"><a href="?_g=orders&customer_id={$customer.customer_id}">{$customer.order_count}</a></td>
		  <td align="center">
			<a href="{$customer.edit}" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
			<a href="{$customer.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		  </td>
		</tr>
		{foreachelse}
		<tr>
		  <td colspan="7" align="center"><strong>{$LANG.form.none}</strong></td>
		</tr>
		{/foreach}
	  </tbody>
	  <tfoot>
	    <tr>
	      <td colspan="7">
		  	<div class="pagination">
			  	<span>{$LANG.common.total}: {$TOTAL_RESULTS}</span>
			  	{$PAGINATION}&nbsp;
			</div>
	    </td>
	  </tr>
	  </tfoot>
	</table>

	{if isset($CUSTOMER_EXPORT_LIST)}
	{foreach from=$CUSTOMER_EXPORT_LIST item=export}
	<input type="submit" name="external_report[{$export.folder}]" class="submit" value="{$LANG.customer.export_to} {$export.description}">
	{/foreach}
	{/if}
  </div>

  <div id="customer-groups" class="tab_content">
	<h3>{$LANG.customer.title_groups}</h3>
	<fieldset id="group-list" class="list"><legend>{$LANG.customer.title_groups_available}</legend>
	  {if isset($CUSTOMER_GROUPS)}
	  {foreach from=$CUSTOMER_GROUPS item=group}
	  <div>
		<span class="actions">
		  <a href="#" class="remove" name="group_delete" rel="{$group.group_id}" title="{$LANG.common.delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		</span>
		<strong><span class="editable" name="group_edit[{$group.group_id}][name]">{$group.group_name}</span></strong>
		<br>
		{if !empty($group.group_description)}
		<span class="editable" name="group_edit[{$group.group_id}][description]">{$group.group_description}</span>
		{else}
		<span class="editable" name="group_edit[{$group.group_id}][description]"></span>
		{/if}
	  </div>
	  {/foreach}
	  {else}
	  <div>{$LANG.form.none}</div>
	  {/if}
	</fieldset>
	<fieldset><legend>{$LANG.customer.title_groups_create}</legend>
	  <div><label for="group_add_name">{$LANG.common.name}</label><span><input type="text" name="group_add[group_name]" id="group_add_name" class="textbox"></span></div>
	  <div><label for="group_add_description">{$LANG.common.description}</label><span><textarea name="group_add[group_description]" id="group_add_description" class="textbox"></textarea></span></div>
	</fieldset>
  </div>

  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input type="hidden" name="customer_id" value="{$CUSTOMER.customer_id}">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" name="save" value="{$LANG.common.save}">
  </div>
  {/if}

  {if isset($DISPLAY_CUSTOMER_FORM)}
	<div id="general" class="tab_content">
	<h3>{$ADD_EDIT_CUSTOMER}</h3>
	  <fieldset><legend>{$LANG.common.details}</legend>
		<div><label for="cust-title">{$LANG.user.title}</label><span><input type="text" name="customer[title]" id="cust-title" class="textbox capitalize" value="{$CUSTOMER.title}"></span></div>
		<div><label for="cust-firstname">{$LANG.user.name_first}</label><span><input type="text" name="customer[first_name]" id="cust-firstname" value="{$CUSTOMER.first_name}" class="textbox capitalize"></span></div>
		<div><label for="cust-lastname">{$LANG.user.name_last}</label><span><input type="text" name="customer[last_name]" id="cust-lastname" value="{$CUSTOMER.last_name}" class="textbox capitalize"></span></div>
		<div>
		  <label for="cust-type">{$LANG.customer.customer_type}</label>
		  <span>
		    <select name="customer[type]" id="cust-type" class="textbox">
		      <option value="1" {if $CUSTOMER.type==1}selected="selected"{/if}>{$LANG.customer.title_key_registered}</option>
		      <option value="2" {if $CUSTOMER.type==2}selected="selected"{/if}>{$LANG.customer.title_key_unregistered}</option>
		    </select>
		  </span>
		</div>
	  </fieldset>
	  <fieldset><legend>{$LANG.account.contact_details}</legend>
		<div><label for="cust-email">{$LANG.common.email}</label><span><input type="text" name="customer[email]" id="cust-email" value="{$CUSTOMER.email}" class="textbox"></span></div>
		<div><label for="cust-phone">{$LANG.address.phone}</label><span><input type="text" name="customer[phone]" id="cust-phone" value="{$CUSTOMER.phone}" class="textbox"></span></div>
		<div><label for="cust-mobile">{$LANG.address.mobile}</label><span><input type="text" name="customer[mobile]" id="cust-mobile" value="{$CUSTOMER.mobile}" class="textbox"></span></div>
		<div><label>{$LANG.newsletter.customer_subscribed}</label><span>
		<select name="customer[subscription_status]" id="subscription_status" class="textbox">
		  <option value="0" {if !$CUSTOMER.subscription_status}selected="selected"{/if}>{$LANG.common.no}</option>
		  <option value="1" {if $CUSTOMER.subscription_status}selected="selected"{/if}>{$LANG.common.yes}</option>
		</select></span></div>
		<div><label>{$LANG.common.ip_address}</label><span>{$CUSTOMER.ip_address}</span></div>
	  </fieldset>
	  <fieldset><legend>{$LANG.account.password}</legend>
		<div><label for="cust-password">{$LANG.user.password_new}</label><span><input type="password" autocomplete="off" name="customer[password]" id="cust-password" class="textbox"></span></div>
		<div><label for="cust-passconf">{$LANG.user.password_confirm}</label><span><input type="password" autocomplete="off" name="customer[passconf]" id="cust-passconf" rel="cust-password" class="textbox confirm"></span></div>
	  </fieldset>
	</div>

	<div id="address" class="tab_content">
	  {if isset($DISPLAY_ADDRESS_EDIT)}
	  <h3>{$LANG.address.address_edit}</h3>
	  <div><label for="address_desc">{$LANG.common.description}</label><span><input type="text" name="address[description]" id="address_desc" value="{$ADDRESS.description}" class="textbox"></span></div>
	  <div><label for="address_title">{$LANG.user.title}</label><span><input type="text" name="address[title]" id="address_title" value="{$ADDRESS.title}" class="textbox capitalize"></span></div>
	  <div><label for="address_firstname">{$LANG.user.name_first}</label><span><input type="text" name="address[first_name]" id="address_firstname" value="{$ADDRESS.first_name}" class="textbox capitalize"></span></div>
	  <div><label for="address_lastname">{$LANG.user.name_last}</label><span><input type="text" name="address[last_name]" id="address_lastname" value="{$ADDRESS.last_name}" class="textbox capitalize"></span></div>
	  <div><label for="address_company">{$LANG.address.company_name}</label><span><input type="text" name="address[company_name]" id="address_company" value="{$ADDRESS.company_name}" class="textbox"></span></div>
	  <div><label for="address_line1">{$LANG.address.line1}</label><span><input type="text" name="address[line1]" id="address_line1" value="{$ADDRESS.line1}" class="textbox"></span></div>
	  <div><label for="address_line2">{$LANG.address.line2}</label><span><input type="text" name="address[line2]" id="address_line2" value="{$ADDRESS.line2}" class="textbox"></span></div>
	  <div><label for="address_town">{$LANG.address.town}</label><span><input type="text" name="address[town]" id="address_town" value="{$ADDRESS.town}" class="textbox"></span></div>
	  <div><label for="country_list">{$LANG.address.country}</label><span>
		<select name="address[country]" id="country-list" class="textbox">
		  {foreach from=$COUNTRIESL item=country}<option value="{$country.id}" {$country.selected}>{$country.name}</option>{/foreach}
		</select>
	  </span></div>
	  <div><label for="state-list">{$LANG.address.state}</label><span><input type="text" name="address[state]" id="state-list" value="{$ADDRESS.state}" class="textbox"></span></div>
	  <div><label for="address_postcode">{$LANG.address.postcode}</label><span><input type="text" name="address[postcode]" id="address_postcode" value="{$ADDRESS.postcode}" class="textbox uppercase"></span></div>
	  <div><label for="billing">{$LANG.address.billing_address}</label><span><input type="hidden" name="address[billing]" id="billing" value="{$ADDRESS.billing}" class="toggle"></div>
	  <div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="address[default]" id="default" value="{$ADDRESS.default}" class="toggle"></div>
	  <input type="hidden" name="address[address_id]" value="{$ADDRESS.address_id}">
	  {/if}

	  {if isset($DISPLAY_ADDRESS_LIST)}
	  <h3>{$LANG.address.address_book}</h3>
	  <div id="address-list">
		{if isset($ADDRESS_LIST)}
		{foreach from=$ADDRESS_LIST item=address}
		<div class="note">
		  <span class="actions">
			<a href="{$address.edit}#address" class="edit" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
			<a href="{$address.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		  </span>
		  <strong>{$address.description}</strong> - {$address.title} {$address.first_name} {$address.last_name}
		  {if !empty({$address.company_name})}({$address.company_name}){/if}
		  <br>
		  {$address.line1}, {if !empty($address.line2)}{$address.line2}, {/if}{$address.town}, {$address.state_name}, {$address.postcode}, {$address.country_name}
		</div>
		{/foreach}
		{else}
	    <div>{$LANG.address.notify_address_none}</div>
	    {/if}
	  </div>
	  <div><a href="#" class="colorbox address-form">{$LANG.address.address_add}</a></div>
	  {/if}
	</div>

	{if isset($DISPLAY_CUSTOMER_GROUPS)}
	<div id="groups" class="tab_content">
	  <h3>{$LANG.customer.title_groups}</h3>
	  <fieldset id="membership" class="list"><legend>{$LANG.customer.title_groups_membership}</legend>
		{if isset($CUSTOMER_GROUPS)}
		{foreach from=$CUSTOMER_GROUPS item=group}
		<div>
		  <span class="actions">
			<a href="#" class="remove" name="membership_delete" rel="{$group.membership_id}" title="{$LANG.common.delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
		  </span>
		  {$group.group_name}
		</div>
		{/foreach}
	    {/if}
	  </fieldset>
	  <fieldset><legend>{$LANG.customer.title_groups_membership_add}</legend>
		<div>
		  <select id="group-join" name="membership_add[]" class="add display">
			<option value="">{$LANG.form.please_select}</option>
			{foreach from=$ALL_CUSTOMER_GROUPS item=group}<option value="{$group.group_id}">{$group.group_name}</option>{/foreach}
		  </select>
		  <a href="#" class="add" target="membership" title="{$LANG.common.add}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/add.png" alt="{$LANG.common.add}"></a>
		</div>
	  </fieldset>
	</div>
	{/if}

	{include file='templates/element.hook_form_content.php'}

	<div class="form_control">
	  <input type="hidden" name="customer_id" value="{$CUSTOMER.customer_id}">
	  <input type="hidden" name="previous-tab" id="previous-tab" value="">
	  <input type="submit" name="save" value="{$LANG.common.save}">
	</div>
  {/if}
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
  </form>

  <div style="display: none;">
	<div id="address-form" class="tb-form">
	  <h3>{$LANG.address.address_add}</h3>
	  <div><label for="edit_description">{$LANG.common.description}</label><span><input type="text" name="address[description][]" id="edit_description" class="textbox add display"> *</span></div>
	  <div><label for="edit_title">{$LANG.user.title}</label><span><input type="text" name="address[title][]" id="edit_title" class="textbox add capitalize"></span></div>
	  <div><label for="edit_first_name">{$LANG.user.name_first}</label><span><input type="text" name="address[first_name][]" id="edit_first_name" class="textbox add capitalize"> *</span></div>
	  <div><label for="edit_last_name">{$LANG.user.name_last}</label><span><input type="text" name="address[last_name][]" id="edit_last_name" class="textbox add capitalize"> *</span></div>
	  <div><label for="edit_company_name">{$LANG.address.company_name}</label><span><input type="text" name="address[company_name][]" id="edit_company_name" class="textbox add"></span></div>
	  <div><label for="edit_line1">{$LANG.address.line1}</label><span><input type="text" name="address[line1][]" id="edit_line1" class="textbox add"> *</span></div>
	  <div><label for="edit_line2">{$LANG.address.line2}</label><span><input type="text" name="address[line2][]" id="edit_line2" class="textbox add"></span></div>
	  <div><label for="edit_town">{$LANG.address.town}</label><span><input type="text" name="address[town][]" id="edit_town" class="textbox add"> *</span></div>
	  <div>
		<label for="edit_country">{$LANG.address.country}</label>
		<span>
		  <select name="address[country][]" id="edit_country" rel="edit_state" class="textbox add country-list">
		  {foreach from=$COUNTRIES item=country}<option value="{$country.id}" {$country.selected}>{$country.name}</option>{/foreach}
		  </select>
		 *</span>
	  </div>
	  <div><label for="edit_state">{$LANG.address.state}</label><span><input type="text" name="address[state][]" id="edit_state" class="textbox add state-list"> *</span></div>
	  <div><label for="edit_postcode">{$LANG.address.postcode}</label><span><input type="text" name="address[postcode][]" id="edit_postcode" class="textbox add uppercase"> *</span></div>
	  <div><label for="billing">{$LANG.address.billing_address}</label><span><input type="hidden" name="address[billing][]" id="billing"  class="toggle"></div>
	  <div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="address[default][]" id="default" class="toggle"></div>
	  <input type="hidden" name="add_div_class" value="note">
	  <input type="button" value="{$LANG.common.add}" class="add" target="address-list" onclick="$.fn.colorbox.close()">
	</div>
  </div>
  <script type="text/javascript">
	var county_list = {$JSON_STATE}
  </script>
</div>