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

{if isset($NEW_API_KEY)}
<div class="alert alert-success" style="background:#d4edda;border:1px solid #c3e6cb;padding:15px;margin-bottom:20px;border-radius:4px;">
  <h4 style="margin-top:0;color:#155724;">New API Key Created</h4>
  <p>Copy these credentials now. The secret will not be shown again.</p>
  <div style="background:#fff;padding:10px;border:1px solid #ddd;border-radius:3px;margin:10px 0;font-family:monospace;">
    <strong>API Key:</strong> {$NEW_API_KEY} <span class="copy_text" data-copied="{$LANG.common.copied}" data-copy="{$LANG.common.click_copy}" data-value="{$NEW_API_KEY}"></span>
  </div>
  <div style="background:#fff;padding:10px;border:1px solid #ddd;border-radius:3px;margin:10px 0;font-family:monospace;">
    <strong>API Secret:</strong> {$NEW_API_SECRET} <span class="copy_text" data-copied="{$LANG.common.copied}" data-copy="{$LANG.common.click_copy}" data-value="{$NEW_API_SECRET}"></span>
  </div>
  <div style="background:#fff;padding:10px;border:1px solid #ddd;border-radius:3px;margin:10px 0;font-family:monospace;">
    <strong>Authorization Header:</strong><br>
    <code>Authorization: Bearer {$NEW_API_KEY}:{$NEW_API_SECRET}</code> <span class="copy_text" style="float:none;opacity:1" data-copied="{$LANG.common.copied}" data-copy="{$LANG.common.click_copy}" data-value="Bearer {$NEW_API_KEY}:{$NEW_API_SECRET}"></span>
  </div>
  <p style="margin-bottom:0;"><strong>Base URL:</strong> <code>{$API_URL}</code></p>
</div>
{/if}
<h3>API Keys (Beta)</h3>
{if $DISPLAY_FORM}
<form action="{$VAL_SELF}" method="post">
  <div id="apikeys" class="tab_content">
  
  {if isset($EDIT_KEY)}
    {* ===== Edit Key Form ===== *}
    <input type="hidden" name="key_id" value="{$EDIT_KEY.key_id}">
    <fieldset><legend>Key Details</legend>
      <div><label for="label">Label</label><span><input type="text" name="label" id="label" value="{$EDIT_KEY.label}" class="textbox"></span></div>
      <div><label for="rate_limit">Rate Limit (per minute)</label><span><input type="number" name="rate_limit" id="rate_limit" value="{$EDIT_KEY.rate_limit}" class="textbox" min="1" max="1000"></span></div>
      <div><label for="expires">Expires</label><span><input type="date" name="expires" id="expires" value="{if isset($EDIT_KEY.expires_date)}{$EDIT_KEY.expires_date}{/if}" class="textbox"></span></div>
      <div><label for="status">Enabled</label><span><input type="hidden" name="status" id="status" class="toggle" value="{$EDIT_KEY.status}"></span></div>
      <div><label for="ip_whitelist">IP Whitelist (one per line, blank = any)</label><span><textarea name="ip_whitelist" id="ip_whitelist" class="textbox" rows="4">{$EDIT_KEY.ip_whitelist_text}</textarea></span></div>
    </fieldset>
    <fieldset><legend>Permissions</legend>
      <table class="list">
        <thead><tr><th>Resource</th><th>Read</th><th>Read/Write</th><th>Read/Write/Delete</th><th>None</th></tr></thead>
        <tbody>
        {foreach from=$API_RESOURCES key=res_key item=res_name}
        <tr>
          <td><strong>{$res_name}</strong></td>
          <td><input type="radio" name="perms[{$res_key}]" value="r" {if isset($EDIT_KEY.permissions_array.$res_key) && $EDIT_KEY.permissions_array.$res_key == 'r'}checked{/if}></td>
          <td><input type="radio" name="perms[{$res_key}]" value="rw" {if isset($EDIT_KEY.permissions_array.$res_key) && $EDIT_KEY.permissions_array.$res_key == 'rw'}checked{/if}></td>
          <td><input type="radio" name="perms[{$res_key}]" value="rwd" {if isset($EDIT_KEY.permissions_array.$res_key) && $EDIT_KEY.permissions_array.$res_key == 'rwd'}checked{/if}></td>
          <td><input type="radio" name="perms[{$res_key}]" value="none" {if !isset($EDIT_KEY.permissions_array.$res_key) || $EDIT_KEY.permissions_array.$res_key == 'none'}checked{/if}></td>
        </tr>
        {/foreach}
        </tbody>
      </table>
    </fieldset>
    <div><input type="submit" name="update_key" value="Save Changes" class="btn btn-primary"></div>

  {else}
    {* ===== Create Key Form ===== *}
    <fieldset><legend>Key Details</legend>
      <div><label for="new-label">Label</label><span><input type="text" name="label" id="new-label" class="textbox" placeholder="e.g. ERP Integration, Mobile App"></span></div>
      <div><label for="new-admin">Owner</label><span><select name="admin_id" id="new-admin" class="textbox">
        {foreach from=$ADMIN_LIST item=admin}
        <option value="{$admin.admin_id}">{$admin.name} ({$admin.username})</option>
        {/foreach}
      </select></span></div>
      <div><label for="new-rate">Rate Limit (per minute)</label><span><input type="number" name="rate_limit" id="new-rate" value="60" class="textbox" min="1" max="1000"></span></div>
      <div><label for="new-expires">Expires (blank = never)</label><span><input type="date" name="expires" id="new-expires" class="textbox"></span></div>
      <div><label for="new-ips">IP Whitelist (one per line, blank = any)</label><span><textarea name="ip_whitelist" id="new-ips" class="textbox" rows="3" placeholder="Leave blank to allow all IPs"></textarea></span></div>
    </fieldset>
    <fieldset><legend>Permissions</legend>
      <table class="list">
        <thead><tr><th>Resource</th><th>Read</th><th>Read/Write</th><th>Read/Write/Delete</th><th>None</th></tr></thead>
        <tbody>
        {foreach from=$API_RESOURCES key=res_key item=res_name}
        <tr>
          <td><strong>{$res_name}</strong></td>
          <td><input type="radio" name="perms[{$res_key}]" value="r"></td>
          <td><input type="radio" name="perms[{$res_key}]" value="rw"></td>
          <td><input type="radio" name="perms[{$res_key}]" value="rwd"></td>
          <td><input type="radio" name="perms[{$res_key}]" value="none" checked></td>
        </tr>
        {/foreach}
        </tbody>
      </table>
    </fieldset>
    <div><input type="submit" name="create_key" value="Create API Key" class="btn btn-success"></div>
  {/if}

  </div>
</form>

{else}
  {* ===== Key List ===== *}
  <div id="apikeys" class="tab_content">
  <p>Base URL: <code>{$API_URL}</code></p>

  {if $API_KEYS}
  <table class="list">
    <thead>
      <tr>
        <th>Label</th>
        <th>Key</th>
        <th>Owner</th>
        <th>Status</th>
        <th>Last Used</th>
        <th>Created</th>
        <th>Expires</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$API_KEYS item=key}
      <tr>
        <td>{$key.label|default:'(no label)'}</td>
        <td><code>{$key.masked_key}</code></td>
        <td>{$key.admin_name}</td>
        <td>{if $key.status}<span style="color:green;">Active</span>{else}<span style="color:red;">Disabled</span>{/if}</td>
        <td>{$key.last_used_date}</td>
        <td>{$key.created_date}</td>
        <td>{$key.expires_date}</td>
        <td>
          <a href="?_g=settings&node=apikeys&action=edit&key_id={$key.key_id}" class="edit" title="Edit"><i class="fa fa-pencil-square-o" title="Edit"></i></a>
          <a href="?_g=settings&node=apikeys&regenerate_key={$key.key_id}" title="Regenerate" onclick="return confirm('Regenerate this API key? The old key will stop working immediately.');"><i class="fa fa-refresh" title="Regenerate"></i></a>
          <a href="?_g=settings&node=apikeys&delete_key={$key.key_id}" class="delete" title="Delete"><i class="fa fa-trash" title="Delete"></i></a>
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  {else}
  <p>No API keys have been created yet.</p>
  {/if}
  </div>

  <div id="apilog" class="tab_content">
    <form method="get" action="{$VAL_SELF}">
      <input type="hidden" name="_g" value="settings">
      <input type="hidden" name="node" value="apikeys">
      <fieldset><legend>Filter</legend>
        <div>
          <label for="log-key-filter">API Key</label>
          <span>
            <select name="log_key_id" id="log-key-filter" class="textbox">
              <option value="">All Keys</option>
              {if $API_KEYS}{foreach from=$API_KEYS item=key}
              <option value="{$key.key_id}" {if $LOG_KEY_FILTER == $key.key_id}selected{/if}>{$key.label|default:$key.masked_key}</option>
              {/foreach}{/if}
            </select>
            <input type="submit" value="Filter" class="tiny">
          </span>
        </div>
      </fieldset>
    </form>
    {if isset($API_LOG)}
    <div class="right"><a href="?_g=maintenance&clearApiLog=true&redir=apikeys" class="button delete" title="{$LANG.notification.confirm_continue}">{$LANG.maintain.clear_log}</a></div>
    <table>
      <thead>
        <tr>
          <th>{$LOG_THEAD.date}</th>
          <th>{$LOG_THEAD.method}</th>
          <th>{$LOG_THEAD.endpoint}</th>
          <th>{$LOG_THEAD.status_code}</th>
          <th>Key</th>
          <th>{$LOG_THEAD.ip_address}</th>
          <th>{$LOG_THEAD.response_ms}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$API_LOG item=entry}
        <tr>
          <td>{$entry.date}</td>
          <td><code>{$entry.method}</code></td>
          <td><code>{$entry.endpoint}</code></td>
          <td>{if $entry.status_code < 400}<span style="color:green;">{$entry.status_code}</span>{elseif $entry.status_code < 500}<span style="color:orange;">{$entry.status_code}</span>{else}<span style="color:red;">{$entry.status_code}</span>{/if}</td>
          <td>{$entry.key_label}</td>
          <td>{$entry.ip_address}</td>
          <td>{$entry.response_ms}</td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    <div class="pagination">
      <span><strong>{$LANG.common.total}:</strong> {number_format($API_LOG_TOTAL)}</span>{$PAGINATION_API_LOG}
    </div>
    {else}
    <p>No API requests logged yet.</p>
    {/if}
  </div>

{/if}
