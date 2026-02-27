{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<form action="{$VAL_SELF}" method="post">
  {if $DISPLAY_FORM}
  <div id="general" class="tab_content">
  <h3>{$ADD_EDIT_ADMIN}</h3>
	<fieldset><legend>{$LANG.common.details}</legend>
	  <div><label for="admin-name">{$LANG.common.name}</label><span><input type="text" name="admin[name]" id="admin-name" value="{$ADMIN.name}" class="textbox required capitalize"></span></div>
	  <div><label for="admin-username">{$LANG.account.username}</label><span><input type="text" name="admin[username]" id="admin-username" value="{$ADMIN.username}" class="textbox required"></span></div>
	  <div><label for="admin-email">{$LANG.common.email}</label><span><input type="text" name="admin[email]" id="admin-email" value="{$ADMIN.email}" class="textbox required"></span></div>
	  <div><label for="admin-lang">{$LANG.settings.default_language}</label><span><select name="admin[language]" id="admin-lang" class="textbox">
	  {foreach from=$LANGUAGES item=language}<option value="{$language.code}" {$language.selected}>{$language.title}</option>{/foreach}
	  </select></span></div>

	  {if $IS_SUPER && isset($ADMIN.super_user)}
	  <div><label for="admin-super">{$LANG.admins.super_user}</label><span><input type="hidden" name="admin[super_user]" id="admin-super" class="toggle" value="{$ADMIN.super_user}"></span></div>
	  {/if}

	  <div><label for="order_notify">{$LANG.admins.notifications}</label><span><input type="hidden" name="admin[order_notify]" id="order_notify" class="toggle" value="{$ADMIN.order_notify}"></span></div>

	  {if $LINKED}
	  <div><label>{$LANG.admins.account_linked}</label><span><a href="?_g=customers&action=edit&customer_id={$ADMIN.customer_id}">{$LANG.admins.account_link_view}</a> &nbsp; [<a href="{$UNLINK}" class="delete">{$LANG.common.remove}</a>]</span></div>
	  {else}
	  <div><label for="admin-customer">{$LANG.admins.account_link}</label><span>
		<input type="hidden" id="result_admin-customer" name="admin[customer_id]"><input type="text" id="admin-customer" class="ajax textbox" rel="user">
	  </span></div>
	  {/if}
	  <div><label for="tour_shown">{$LANG.admins.tour_shown}</label><span><input type="hidden" name="admin[tour_shown]" id="tour_shown" class="toggle" value="{$ADMIN.tour_shown}"></span></div>
	</fieldset>
	<fieldset><legend>{$LANG.account.new_password}</legend>
	  <div><label for="admin-password">{$LANG.account.password}</label><span><input type="password" autocomplete="off" name="password" id="admin-password" class="textbox"></span></div>
	  <div><label for="admin-passconf">{$LANG.user.password_confirm}</label><span><input type="password" autocomplete="off" name="passconf" id="admin-passconf" rel="admin-password" class="textbox confirm"></span></div>
	  <div class="nostripe">{$LANG.account.pw_cause_logout}</div>
	</fieldset>
	<fieldset><legend>{$LANG.common.notes}</legend>
	  <div><label for="admin-notes">{$LANG.common.notes}</label><span><textarea name="admin[notes]" id="admin-notes" class="textbox">{$ADMIN.notes}</textarea></span></div>
	</fieldset>
  </div>

  <div id="overview" class="tab_content">
  	<fieldset><legend>{$LANG.common.details}</legend>
		<div><label for="admin-logins">{$LANG.admins.login_count}</label><span><input type="text" id="admin-logins" class="textbox number" name="admin[logins]" value="{$ADMIN.logins}"></span></div>
		<div><label>{$LANG.admins.login_last}</label><span>{$ADMIN.last_login}</span></div>
	</fieldset>
  </div>

  {if isset($ADMIN_TWOFA)}
  <div id="twofa" class="tab_content">
  <h3>{$LANG.admins.twofa_title}</h3>

  {if isset($TWOFA_BACKUP_CODES)}
  <fieldset style="border-color:#e6a817;background:#fffbe6">
    <legend style="color:#a06000">&#9888; {$LANG.admins.twofa_backup_save_legend}</legend>
    <p>{$LANG.admins.twofa_backup_save_info}</p>
    <ul style="font-family:monospace;font-size:1.1em;column-count:2;list-style:none;padding:0">
    {foreach from=$TWOFA_BACKUP_CODES item=bcode}<li style="padding:4px 0">{$bcode}</li>{/foreach}
    </ul>
  </fieldset>
  {/if}

  {if $ADMIN_TWOFA.enabled}
  <fieldset><legend>{$LANG.admins.twofa_status}</legend>
    <div><label>{$LANG.admins.twofa_method}</label><span><strong>{$ADMIN_TWOFA.method|upper}</strong></span></div>
    <div><label>{$LANG.admins.twofa_backup_remaining}</label><span>{$ADMIN_TWOFA.backup_count}</span></div>
  </fieldset>
  <fieldset><legend>{$LANG.admins.twofa_actions}</legend>
    <div class="nostripe">
      <button type="submit" name="twofa_action" value="regenerate_backup" style="margin-right:10px">{$LANG.admins.twofa_regen_backup}</button>
      <button type="submit" name="twofa_action" value="disable" class="delete" onclick="return confirm('{$LANG.admins.twofa_disable_confirm}')">{$LANG.admins.twofa_disable}</button>
    </div>
  </fieldset>

  {else}

  <fieldset><legend>{$LANG.admins.twofa_enable_legend}</legend>
    <div><label>{$LANG.admins.twofa_status}</label><span>{$LANG.admins.twofa_disabled}</span></div>
    <div class="nostripe">
      <p>{$LANG.admins.twofa_choose_method}</p>
      <div style="margin:10px 0">
        <strong>{$LANG.admins.twofa_email_option}</strong>
        <p style="color:#555;margin:4px 0 8px">{$LANG.admins.twofa_email_desc}</p>
        <button type="submit" name="twofa_action" value="enable_email">{$LANG.admins.twofa_enable_email}</button>
      </div>
      <hr>
      <div style="margin:10px 0" id="totp-setup-section">
        <strong>{$LANG.admins.twofa_totp_option}</strong>
        <p style="color:#555;margin:4px 0 8px">{$LANG.admins.twofa_totp_desc}</p>
        <p>{$LANG.admins.twofa_totp_step1}</p>
        <div id="totp-qr" data-uri="{$TOTP_SETUP_URI|escape:'html'}" style="margin:10px 0;width:180px;height:180px"></div>
        <p>{$LANG.admins.twofa_manual_key} <code style="font-size:1em;letter-spacing:2px">{$TOTP_SETUP_SECRET}</code></p>
        <p>{$LANG.admins.twofa_totp_step2}</p>
        <div><label for="twofa_setup_code">{$LANG.account.twofa_code_label}</label><span><input type="text" name="twofa_setup_code" id="twofa_setup_code" class="textbox" inputmode="numeric" maxlength="6" placeholder="000000" autocomplete="one-time-code" style="width:120px"></span></div>
        <div class="nostripe" style="margin-top:8px">
          <button type="submit" name="twofa_action" value="enable_totp">{$LANG.admins.twofa_enable_totp}</button>
        </div>
      </div>
    </div>
  </fieldset>

  {/if}

  </div>
  {/if}

  <div id="permissions" class="tab_content">
	{if $IS_SUPER && isset($ADMIN.super_user)}
  <h3>{$LANG.admins.permission}</h3>
	<table>
	  <thead>
		<tr>
		  <th width="400">{$LANG.admins.permission_section}</th>
		  <th width="40" align="center">{$LANG.common.read}</th>
		  <th width="40" align="center">{$LANG.common.edit}</th>
		  <th width="40" align="center">{$LANG.common.delete}</th>
		</tr>
	  </thead>
	  <tbody>
	  {foreach from=$SECTIONS item=section}
		<tr>
		  <td><strong>{$section.name}</strong> - {$section.info}</td>
		  <td style="text-align:center"><input type="checkbox" class="read" name="permission[{$section.id}][]" value="1" {$section.read}></td>
		  <td style="text-align:center"><input type="checkbox" class="edit" name="permission[{$section.id}][]" value="2" {$section.edit}></td>
		  <td style="text-align:center"><input type="checkbox" class="delete" name="permission[{$section.id}][]" value="4" {$section.delete}></td>
		</tr>
	  {/foreach}
	  </tbody>
	  <tfoot>
		<tr>
		  <td style="text-align:right">{$LANG.admins.permission_all}</td>
		  <td style="text-align:center"><input type="checkbox" class="check-all" rel="read"></td>
		  <td style="text-align:center"><input type="checkbox" class="check-all" rel="edit"></td>
		  <td style="text-align:center"><input type="checkbox" class="check-all" rel="delete"></td>
		</tr>
	  </tfoot>
	</table>
	{else}

	{/if}
  </div>
  {else}
  <div id="admins" class="tab_content list">
	<h3>{$LANG.admins.title_administrators}</h3>
	<table width="300">
		<tbody>
		{foreach from=$ADMINS item=admin}
			<tr>
				<td><input type="hidden" name="status[{$admin.admin_id}]" id="status_{$admin.admin_id}" value="{$admin.status}" class="toggle">
		  <a href="{$admin.link_edit}" title="{$LANG.account.logins}: {$admin.logins}">{$admin.name}</a></td>
				<td>
		  			<span class="actions">
						<a href="{$admin.link_edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
						{if $admin.link_delete}<a href="{$admin.link_delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>{/if}
		  			</span>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
  </div>
{/if}

    {if isset($PLUGIN_TABS)}
  {foreach from=$PLUGIN_TABS item=tab}
  {$tab}
  {/foreach}
  {/if}
{include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input type="hidden" name="admin_id" value="{$ADMIN.admin_id|default:''}">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  
</form>