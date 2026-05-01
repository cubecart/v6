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
<style>
   .hooks-status { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; }
   .hooks-status--ok      { background: rgba(60,160,80,0.15);  color:#2c8a40; }
   .hooks-status--orphan  { background: rgba(200,60,60,0.15);  color:#b03030; }
   .hooks-status--issue   { background: rgba(220,190,30,0.20); color:#856900; }
   .hooks-col-status { width:120px; }
   .hooks-col-count  { width:90px; }
   .hooks-col-action { width:80px; }
   .hooks-row-icon, .hooks-row-name { vertical-align: middle; }
   .hooks-count-warn { color:#b56a13; }
   .hooks-badge-warning {
      display:inline-block; margin-left:6px; padding:1px 8px;
      font-size:11px; font-weight:600;
      background: rgba(220,190,30,0.20); color:#856900;
      border-radius:10px;
   }
   .hooks-pathbar {
      background:#5a6168; color:#fff;
      font-family:Menlo,Consolas,monospace; font-size:12px;
      padding:10px 22px;
      border:1px solid #4a5058; border-bottom:none;
      border-radius:6px 6px 0 0;
   }
   .hooks-pathbar .stat-chart-title { color:#fff; text-transform:none; letter-spacing:0; font-family:inherit; }
   .hooks-pathbar-warning { color:#ffd28a !important; }
   .hooks-code-editor {
      height:500px;
      background:none !important;
      border:1px solid var(--border); border-top:none;
      border-radius:0 0 6px 6px;
      text-indent:0 !important;
   }
</style>
<form action="{$VAL_SELF}" id="hook_form" method="post" enctype="multipart/form-data">
   {if $DISPLAY_PLUGINS}
   <div id="plugins" class="tab_content">
      <h3>{$LANG.hooks.title_plugins_installed}</h3>
      {if $PLUGINS}
      <table>
         <thead>
            <tr>
               <th>{$LANG.hooks.title_plugin}</th>
               <th class="text-center hooks-col-status">{$LANG.common.status}</th>
               <th class="text-center hooks-col-count">{$LANG.hooks.title_hook}</th>
               <th class="text-center hooks-col-count">{$LANG.hooks.missing_files|capitalize}</th>
               <th class="text-center hooks-col-action">{$LANG.form.action}</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$PLUGINS item=plugin}
            <tr>
               <td>
                  <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/plugin.png" alt="" class="hooks-row-icon">
                  <a href="{$plugin.edit}" class="hooks-row-name">{$plugin.name}</a>
               </td>
               <td class="text-center">
                  {if $plugin.orphaned}
                     <span class="hooks-status hooks-status--orphan" title="{$LANG.hooks.orphaned_plugin_help}">{$LANG.hooks.orphaned}</span>
                  {elseif $plugin.hooks_missing > 0}
                     <span class="hooks-status hooks-status--issue" title="{$LANG.hooks.hooks_missing_help}">{$LANG.common.warning}</span>
                  {else}
                     <span class="hooks-status hooks-status--ok">{$LANG.common.installed}</span>
                  {/if}
               </td>
               <td class="text-center">{if $plugin.hooks_total > 0}{$plugin.hooks_total}{else}&mdash;{/if}</td>
               <td class="text-center">{if $plugin.hooks_missing > 0}<strong class="hooks-count-warn">{$plugin.hooks_missing}</strong>{else}&mdash;{/if}</td>
               <td class="text-center">
                  <span class="actions">
                     <a href="{$plugin.edit}" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o"></i></a>
                     {if $plugin.orphaned}<a href="{$plugin.delete}" class="delete" title="{$LANG.hooks.confirm_delete_orphans}"><i class="fa fa-trash"></i></a>{/if}
                  </span>
               </td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      {else}
      <p>{$LANG.hooks.error_plugin_none}</p>
      {/if}
   </div>
   <div id="snippets" class="tab_content">
      <h3>{$LANG.hooks.title_code_snippets}</h3>
      <p><a href="?_g=settings&node=hooks&add_snippet=1#snippets" class="delete">{$LANG.hooks.add_snippet}</a></p>
      <table width="70%">
         <thead>
            <tr>
               <th width="10">{$LANG.common.status}</th>
               <th>{$LANG.common.description}</th>
               <th>{$LANG.hooks.trigger}</th>
               <th>{$LANG.hooks.priority}</th>
               <th width="50">{$LANG.form.action}</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$SNIPPETS item=snippet}
            <tr>
               <td  align="center" width="10"><span class="toggle"><input type="hidden" id="snippet_status_{$snippet.snippet_id}" name="snippet_status[{$snippet.snippet_id}]" value="{$snippet.enabled}" class="toggle"></span></td>
               <td><a href="?_g=settings&node=hooks&snippet={$snippet.snippet_id}#snippets">{$snippet.description}</a></td>
               <td class="courier">{$snippet.hook_trigger}</td>
               <td>{$snippet.priority}</td>
               <td width="50"><span class="actions"><a href="?_g=settings&node=hooks&snippet={$snippet.snippet_id}#snippets"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a> <a href="?_g=settings&node=hooks&delete_snippet={$snippet.snippet_id}&token={$SESSION_TOKEN}#snippets" class="delete" title="{$LANG.notification.confirm_continue}"><i class="fa fa-trash"></i></a></span></td>
            </tr>
            {foreachelse}
            <tr>
               <td colspan="3">{$LANG.hooks.error_snippet_none}</td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      {if $DISPLAY_SNIPPET_FORM}
      {if $SNIPPET}
      <h3>{$LANG.hooks.title_code_snippet_edit}</h3>
      {else}
      <h3>{$LANG.hooks.title_code_snippet_add}</h3>
      {/if}
      <p><a href="?_g=settings&node=hooks#snippets" class="delete">{$LANG.common.cancel}</a></p>
      <fieldset>
         <legend>{$LANG.hooks.title_code_snippet}</legend>
         <div><label for="enabled">{$LANG.common.enabled}</label><span><input name="snippet[enabled]" id="enabled" type="hidden" class="toggle" value="{$SNIPPET.enabled}"></span></div>
         <div>
            <label for="unique_id">{$LANG.hooks.unique_id}</label>
            <span>
            <input type="text" name="snippet[unique_id]" id="unique_id" class="textbox required" value="{$SNIPPET.unique_id}">
            </span>
         </div>
         <div>
            <label for="priority">{$LANG.hooks.priority}</label>
            <span>
            <input type="text" name="snippet[priority]" id="priority" class="textbox required number" value="{$SNIPPET.priority}">
            </span>
         </div>
         <div>
            <label for="description">{$LANG.common.description}</label>
            <span>
            <input type="text" name="snippet[description]" id="description" class="textbox required" value="{$SNIPPET.description}">
            </span>
         </div>
         <div>
            <label for="trigger">{$LANG.hooks.trigger}</label>
            <span>
               <select name="snippet[hook_trigger]" id="hook_trigger" class="required textbox">
                  <option value="">{$LANG.form.please_select}</option>
                  {foreach from=$TRIGGERS item=trigger}<option value="{$trigger.trigger}"{$trigger.selected}>{$trigger.trigger}</option>{/foreach}
               </select>
            </span>
         </div>
         <div>
            <label for="version">{$LANG.hooks.version}</label>
            <span>
            <input type="text" name="snippet[version]" id="version" class="textbox" value="{$SNIPPET.version}">
            </span>
         </div>
         <div>
            <label for="author">{$LANG.hooks.author}</label>
            <span>
            <input type="text" name="snippet[author]" id="author" class="textbox" value="{$SNIPPET.author}">
            </span>
         </div>
         </fieldset>
         <fieldset>
         <legend>{$LANG.hooks.php_code}</legend>
         <div id="php_code"></div>
         <input type="hidden" name="snippet[php_code]" id="php_code_base64" class="textbox" value="{$SNIPPET.php_code_base64}">
         <script src="includes/ace/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
         <script>
            var editor = ace.edit("php_code");
            var input = document.getElementById('php_code_base64');
            editor.session.setUseWrapMode(true);
            editor.setOptions({
                highlightActiveLine: true,
                showPrintMargin: false,
                theme: 'ace/theme/github',
                mode: 'ace/mode/php'
            });
            document.addEventListener('DOMContentLoaded', function() { editor.setValue(b64DecodeUnicode(input.value), 1); });
            editor.getSession().on("change", function () {
               input.value = b64EncodeUnicode(editor.getSession().getValue());
            });
         </script>
         <input type="hidden" name="snippet[snippet_id]" id="snippet_id" class="textbox" value="{$SNIPPET.snippet_id}">
      </fieldset>
      {/if}
   </div>
   <div id="snippets_import" class="tab_content">
      <h3>{$LANG.hooks.title_import_code_snippets}</h3>
      <p>{$LANG.hooks.example_code_snippet_xml}</p>
      <fieldset>
         <legend>{$LANG.hooks.browse_for_file}</legend>
         <input type="file" name="code_snippet_import" id="code_snippet_import">
      </fieldset>
   </div>
   {/if}
   {if $DISPLAY_HOOKS}
   <div id="hooks" class="tab_content">
      <h3>{ucwords($PLUGIN)}</h3>
      {if $HOOKS}
      <table width="70%">
         <thead>
            <tr>
               <th>{$LANG.common.status}</th>
               <th>{$LANG.hooks.title_hook_available}</th>
               <th>{$LANG.hooks.trigger}</th>
               <th>{$LANG.hooks.priority}</th>
               <th width="50" align="center">{$LANG.form.action}</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$HOOKS item=hook}
            <tr>
                <td  align="center" width="10"><input type="hidden" name="status[{$hook.hook_id}]" value="{$hook.enabled}" id="status_{$hook.hook_id}" class="toggle"></td>
                <td>
                  <a href="{$hook.edit}">{$hook.hook_name}</a>
                  {if $hook.file_missing}<span class="hooks-badge-warning" title="{$LANG.hooks.hook_file_missing_help}"><i class="fa fa-exclamation-triangle"></i> {$LANG.hooks.file_missing}</span>{/if}
                </td>
                <td class="courier">{$hook.trigger}</td>
                <td class="text-center">{$hook.priority}</td>
                <td class="text-center">
                  <a href="{$hook.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
                  {if $hook.file_missing}<a href="{$hook.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>{/if}
                </td>
            </tr>
            {/foreach}
         </tbody>
        </table>
        {else}
        <p>{$LANG.hooks.error_hook_none}</p>
        {/if}
      <p>{$LANG.hooks.notify_hook_magic}</p>
      {if $CAN_REVERT}<p><a href="?_g=settings&node=hooks&plugin={$HOOKS.0.plugin}&revert=1" title="{$LANG.notification.confirm_revert}" class="button small delete">{$LANG.hooks.revert_default}</a></p>{/if}
   </div>
   {/if}
   {if $DISPLAY_FORM}
   <div id="hook_edit" class="tab_content">
      <h3>{if $ADD_HOOK}{$LANG.hooks.title_hook_add}{else}{$LANG.hooks.title_hook_configure}{/if}</h3>
      <fieldset>
         <legend>{$LANG.hooks.title_hook_required}</legend>
         <div><label for="hook_enabled">{$LANG.common.status}</label><span><input type="hidden" name="hook[enabled]" id="hook_enabled" class="toggle" value="{$HOOK.enabled}"></span></div>
         <div><label for="hook_name">{$LANG.hooks.name}</label><span><input type="text" name="hook[hook_name]" id="hook_name" class="textbox required" value="{$HOOK.hook_name}"></span></div>
         {if isset($PLUGINS)}
         <div>
            <label for="plugin">{$LANG.hooks.title_plugin}</label>
            <span>
               <select name="hook[plugin]" id="plugin" class="required textbox">
                  <option value="">{$LANG.form.please_select}</option>
                  {foreach from=$PLUGINS item=plugin}<option value="{$plugin.plugin}"{$plugin.selected}>{$plugin.name}</option>{/foreach}
               </select>
            </span>
         </div>
         {/if}
         <div>
            <label for="trigger">{$LANG.hooks.trigger}</label>
            <span>
               <select name="hook[trigger]" class="textbox">
               {foreach from=$TRIGGERS item=t}
                  <option value="{$t.trigger}"{if (isset($HOOK.trigger) && !empty($HOOK.trigger) && $HOOK.trigger==$t.trigger)} selected="selected"{/if}>{$t.trigger}</option>
               {/foreach}
               </select>
            </span>
         </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.hooks.title_hook_optional}</legend>
         <div><label for="hook_priority">{$LANG.hooks.priority}</label><span><input type="text" name="hook[priority]" id="hook_priority" class="textbox number" value="{$HOOK.priority}"></span></div>
         <div>
            <label for="filepath">{$LANG.hooks.path_to_file}</label><span><input type="text" name="hook[filepath]" id="filepath" class="textbox" value="{$HOOK.filepath}"></span>
            <br><small>{sprintf($LANG.hooks.path_to_file_desc,$HOOK.trigger)}.php</small>
         </div>
         <input type="hidden" name="hook[hook_id]" value="{$HOOK.hook_id}">
      </fieldset>
   </div>
   {if isset($HOOK_FILE)}
   <div id="hook_code" class="tab_content">
      <h3>{$LANG.hooks.title_hook_code}</h3>
      {if $HOOK_FILE.missing}<p class="notice"><i class="fa fa-exclamation-triangle"></i> {$LANG.hooks.hook_file_missing_create}</p>{/if}
      <fieldset>
         <div class="hooks-pathbar stat-chart-header">
            <span class="stat-chart-title">{$HOOK_FILE.path}</span>
            {if !$HOOK_FILE.writable}<span class="stat-chart-title hooks-pathbar-warning">{$LANG.hooks.hook_file_readonly}</span>{/if}
         </div>
         <div id="hook_code_editor" class="hooks-code-editor"></div>
         <input type="hidden" id="hook_file_code_b64" value="{$HOOK_FILE.code}">
         {if $HOOK_FILE.writable}<input type="hidden" name="hook_file_code" id="hook_file_code_input" value="">{/if}
      </fieldset>
      <script src="includes/ace/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
      {literal}
      <script>
         var hookEditor = ace.edit("hook_code_editor");
         var hookInput = document.getElementById('hook_file_code_input');
         hookEditor.session.setUseWrapMode(true);
         hookEditor.setOptions({
            highlightActiveLine: true,
            showPrintMargin: false,
            theme: 'ace/theme/github',
            mode: 'ace/mode/php',
            readOnly: !hookInput
         });
         document.addEventListener('DOMContentLoaded', function() {
            var hookCode = b64DecodeUnicode(document.getElementById('hook_file_code_b64').value);
            hookEditor.setValue(hookCode, 1);
            if (hookInput) {
               hookInput.value = hookCode;
               hookEditor.getSession().on("change", function () {
                  hookInput.value = hookEditor.getSession().getValue();
               });
            }
         });
      </script>
      {/literal}
      {if $HOOK_FILE.backups}
      <script>var hookBackupId = {$HOOK.hook_id};</script>
      {literal}
      <script>
      function viewBackup(timestamp) {
         $.get('?_g=xml&function=viewHookBackup&hook_id=' + hookBackupId + '&backup=' + timestamp, function(data) {
            $.colorbox({html: data, width: '80%', height: '80%', title: timestamp});
         });
      }
      </script>
      {/literal}
      <h3>{$LANG.hooks.title_hook_backups}</h3>
      <p><small>{$LANG.hooks.max_backups_note}</small></p>
      <table width="70%">
         <thead>
            <tr>
               <th>{$LANG.common.date}</th>
               <th>{$LANG.hooks.file_size}</th>
               <th width="80" class="text-center">{$LANG.form.action}</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$HOOK_FILE.backups item=backup}
            <tr>
               <td>{$backup.date}</td>
               <td>{($backup.size/1024)|string_format:"%.1f"} KB</td>
               <td class="text-center">
                  <a href="#" title="{$LANG.common.view}" onclick="viewBackup('{$backup.timestamp}'); return false;"><i class="fa fa-eye"></i></a>
                  &nbsp;
                  <a href="{$backup.restore_url}#hook_code" title="{$LANG.hooks.restore_backup}" onclick="return confirm('{$LANG.notification.confirm_continue}');"><i class="fa fa-undo"></i></a>
                  {if !$backup.is_default}&nbsp;
                  <a href="{$backup.delete_url}&amp;token={$SESSION_TOKEN}#hook_code" title="{$LANG.notification.confirm_continue}" class="delete"><i class="fa fa-trash"></i></a>{/if}
               </td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      {/if}
   </div>
   {/if}
   {/if}
      {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
   {$tab}
   {/foreach}
   {/if}
{include file='templates/element.hook_form_content.php'}
   <input type="hidden" name="previous-tab" id="previous-tab" value="">
   <div class="form_control"><input type="submit" value="{$LANG.common.save}">{if isset($HOOK_FILE)} <input type="submit" name="submit_cont" value="{$LANG.common.save} &amp; {$LANG.common.continue}">{/if}</div>
</form>