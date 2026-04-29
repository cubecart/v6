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
<form action="{$VAL_SELF}" id="hook_form" method="post" enctype="multipart/form-data">
   <div id="redirects" class="tab_content">
      <h3>{$LANG.settings.redirects}</h3>

      <fieldset class="redirect-add">
         <legend>{$LANG.settings.add_redirect}</legend>
         <div>
            <label for="redirect">{$LANG.common.status_code}</label>
            <span>
               <select name="redirect" id="redirect" class="textbox">
                  <option value="301">301 - {$LANG.common.permanent}</option>
                  <option value="302">302 - {$LANG.common.temporary}</option>
               </select>
            </span>
         </div>
         <div>
            <label for="redirect_type">{$LANG.common.page}</label>
            <span>
               <select name="type" id="redirect_type" class="textbox">
                  <optgroup label="{$LANG.settings.dynamic_pages}">
                  {foreach from=$REDIRECT_TYPES.dynamic key=type item=name}
                     <option value="{$type}" data-static="false">{$name}</option>
                  {/foreach}
                  </optgroup>
                  <optgroup label="{$LANG.settings.static_pages}">
                  {foreach from=$REDIRECT_TYPES.static key=type item=name}
                     <option value="{$type}" data-static="true">{$name}</option>
                  {/foreach}
                  </optgroup>
               </select>
            </span>
         </div>
         <div>
            <label for="redir_path">{$LANG.settings.redirect_from}</label>
            <span><input type="text" name="path" id="redir_path" class="textbox required" value="{$PREFILL.path|default:''}"></span>
         </div>
         <div>
            <label for="destination_search">{$LANG.settings.redirect_to}</label>
            <span id="destination_picker">
               <input type="text" id="destination_search" class="textbox" rel="prod" placeholder="{$LANG.common.type_to_search}" autocomplete="off">
               <input type="hidden" name="item_id" id="item_id" value="0">
               <span id="destination_label" class="destination-label" style="cursor:pointer" title="{$LANG.common.edit}"></span>
            </span>
         </div>
         <div>
            <label>&nbsp;</label>
            <span><input id="redir_submit" type="submit" class="tiny button" value="{$LANG.common.add}"></span>
         </div>
      </fieldset>

      <fieldset class="redirect-filter">
         <legend>{$LANG.common.filter}</legend>
         <input type="text" id="filter_path" placeholder="{$LANG.settings.redirect_from}" value="{$FILTER.path}" class="textbox">
         <select id="filter_redirect" class="textbox">
            <option value="">{$LANG.common.any}</option>
            <option value="301"{if $FILTER.redirect=='301'} selected{/if}>301</option>
            <option value="302"{if $FILTER.redirect=='302'} selected{/if}>302</option>
         </select>
         <select id="filter_type" class="textbox">
            <option value="">{$LANG.common.any}</option>
            {foreach from=$REDIRECT_TYPES.dynamic key=type item=name}
               <option value="{$type}"{if $FILTER.type==$type} selected{/if}>{$name}</option>
            {/foreach}
            {foreach from=$REDIRECT_TYPES.static key=type item=name}
               <option value="{$type}"{if $FILTER.type==$type} selected{/if}>{$name}</option>
            {/foreach}
         </select>
         <button type="button" id="filter_go" class="tiny">{$LANG.common.go}</button>
         {if $FILTER.path || $FILTER.redirect || $FILTER.type}
         <button type="button" id="filter_clear" class="tiny">{$LANG.common.reset}</button>
         {/if}
      </fieldset>

      <table class="redirect-list">
         <thead>
            <tr>
               <th>{$LANG.form.action}</th>
               <th>{$LANG.common.status_code}</th>
               <th>{$LANG.common.page}</th>
               <th>{$LANG.settings.redirect_from}</th>
               <th>{$LANG.common.item_id}</th>
               <th>{$LANG.settings.redirect_to}</th>
               <th class="text-center" nowrap="nowrap">{$LANG.statistics.product_hits}</th>
               <th nowrap="nowrap">{$LANG.statistics.online_last_seen}</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$REDIRECTS item=redirect}
            <tr>
               <td style="text-align:center"><a href="?_g=settings&node=redirects&delete={$redirect.id}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></td>
               <td style="text-align:center">{$redirect.redirect}</td>
               <td>
               {if $redirect.type=='prod'}
                  {$LANG.common.product}
               {elseif $redirect.type=='cat'}
                  {$LANG.common.category}
               {elseif $redirect.type=='doc'}
                  {$LANG.common.document}
               {elseif $redirect.type=='saleitems'}
                  {$LANG.navigation.saleitems}
               {elseif $redirect.type=='certificates'}
                  {$LANG.catalogue.gift_certificates}
               {elseif $redirect.type=='contact'}
                  {$LANG.documents.document_contact}
               {elseif $redirect.type=='search'}
                  {$LANG.common.search}
               {elseif $redirect.type=='login'}
                  {$LANG.account.login}
               {elseif $redirect.type=='register'}
                  {$LANG.account.register}
               {/if}
               </td>
               <td>{$redirect.display_path}</td>
               <td style="text-align:center">
               {if empty($redirect.item_id)}
                  -
               {else}
                  {$redirect.item_id}
               {/if}</td>
               <td>{$redirect.display_destination}</td>
               <td class="text-center">{if isset($redirect.hit_count)}{$redirect.hit_count|number_format}{else}0{/if}</td>
               <td nowrap="nowrap">{if !empty($redirect.last_hit) && $redirect.last_hit != '0000-00-00 00:00:00'}{formatTime(strtotime($redirect.last_hit))}{else}&mdash;{/if}</td>
            </tr>
            {foreachelse}
            <tr>
               <td colspan="8">{$LANG.common.none}</td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      {if !empty($PAGINATION)}
      <div class="pagination">{$PAGINATION}</div>
      {/if}
   </div>

   <div id="missing_uris" class="tab_content">
      <h3>{$LANG.settings.missing_uris}</h3>
      <p>{$LANG.settings.404_desc}</p>
      <table>
         <thead>
            <tr>
               <th width="10">&nbsp;</th>
               <th>ID</th>
               <th>URI</th>
               <th>{$LANG.statistics.product_hits}</th>
               <th>{$LANG.common.created}</th>
               <th>{$LANG.common.done}</th>
               <th>{$LANG.common.ignore}</th>
               <th>{$LANG.common.add}</th>
            </tr>
         </thead>
         <tbody>
            {foreach $MISSING item=m}
            <tr>
               <td><input type="checkbox" name="missing_id[]" class="missing" value="{$m.id}"></td>
               <td>{$m.id}</td>
               <td>{$m.display_uri}</td>
               <td style="text-align: center">{$m.hits}</td>
               <td>{$m.updated}</td>
               <td style="text-align: center" nowrap="nowrap">{if $m.done == '1'}<i class="fa fa-check-circle done_toggle" aria-hidden="true" data-id="{$m.id}" data-status="1" data-table="404_log"></i>{else}<i class="fa fa-times-circle done_toggle" aria-hidden="true" data-id="{$m.id}" data-status="0" data-table="404_log"></i>{/if}{if $m.warn == '1' && $m.done == '1'} <i class="fa fa-exclamation-triangle done_toggle" id="warn_{$m.id}" data-id="{$m.id}" data-status="warn" data-table="404_log" aria-hidden="true" title="{$LANG.common.remove}"></i>{/if}</td>
               <td style="text-align: center"><a href="?_g=settings&node=redirects&ignore={$m.id}#missing_uris" class="confirm" title="{$LANG.notification.confirm_continue}"><i class="fa fa-ban" aria-hidden="true" title="{$LANG.common.ignore}"></i></a></td>
               <td style="text-align: center"><a href="?_g=settings&node=redirects&from_uri={$m.display_uri|urlencode}#redirects" title="{$LANG.settings.add_redirect}"><i class="fa fa-plus-circle" aria-hidden="true"></i></a></td>
            </tr>
            {foreachelse}
            <tr>
               <td colspan="8">{$LANG.common.none}</td>
            </tr>
            {/foreach}
         </tbody>
         <tfoot>
            <tr>
               <td colspan="8">
                  <a href="#" class="check-all" rel="missing">{$LANG.form.check_uncheck}</a>
                  &bull;
                  {$LANG.maintain.db_with_selected}
                  <select name="action" class="textbox">
                     <option value="">{$LANG.form.please_select}</option>
                     <option value="ignore">{$LANG.common.ignore}</option>
                     <option value="delete_log">{$LANG.common.delete}</option>
                  </select>
                  <input type="submit" class="tiny" value="{$LANG.common.go}">
               </td>
            </tr>
         </tfoot>
      </table>
      {if !empty($PAGINATION_404)}
      <div class="pagination">{$PAGINATION_404}</div>
      {/if}
   </div>

   <div id="ignored_uris" class="tab_content">
      <h3>{$LANG.settings.ignored_uris}</h3>
      <table>
         <thead>
            <tr>
               <th>ID</th>
               <th>URI</th>
               <th>{$LANG.statistics.product_hits}</th>
               <th>{$LANG.common.remove}</th>
            </tr>
         </thead>
         <tbody>
            {foreach $IGNORED item=i}
            <tr>
               <td>{$i.id}</td>
               <td>{$i.display_uri}</td>
               <td style="text-align: center">{$i.hits}</td>
               <td style="text-align: center"><a href="?_g=settings&node=redirects&remove_ignore={$i.id}#ignored_uris"><i class="fa fa-trash" aria-hidden="true" title="{$LANG.common.remove}"></i></a></td>
            </tr>
            {foreachelse}
            <tr>
               <td colspan="4">{$LANG.common.none}</td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      {if !empty($PAGINATION_IGNORED)}
      <div class="pagination">{$PAGINATION_IGNORED}</div>
      {/if}
      {if $IGNORED}
      <p><a href="?_g=settings&amp;node=redirects&amp;clear_ignored=1#ignored_uris" class="button tiny delete" title="{$LANG.notification.confirm_continue}"><i class="fa fa-trash"></i> {$LANG.settings.clear_ignored}</a></p>
      {/if}
   </div>
      {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
   {$tab}
   {/foreach}
   {/if}
{include file='templates/element.hook_form_content.php'}
</form>

<div id="val_error_not_found" style="display:none">{$LANG.settings.redirect_error_not_found}</div>

<script>
{literal}
(function(){
   function init(){
      if (typeof jQuery === 'undefined' || !jQuery.fn.autocomplete) { setTimeout(init, 50); return; }
      var $    = jQuery;
      var $sel = $('#redirect_type');
      var $in  = $('#destination_search');
      var $hid = $('#item_id');
      var $lab = $('#destination_label');
      var $picker = $('#destination_picker');

      function showSearch(){
         $in.show().val('').focus();
         $lab.hide().text('');
      }
      function showLabel(text){
         $in.hide().val('');
         $lab.text(text).show();
      }
      function refresh(){
         var $opt    = $('option:selected', $sel);
         var isStatic = $opt.data('static') === true || $opt.data('static') === 'true';
         if (isStatic) {
            $picker.hide();
            $hid.val(0);
            $in.val('').show();
            $lab.text('').hide();
         } else {
            $picker.show();
            $in.attr('rel', $opt.val());   // 'prod' | 'cat' | 'doc'
            $hid.val(0);
            showSearch();
         }
      }
      $lab.on('click', showSearch);

      // Filter form: navigate directly so we sidestep the global submit
      // validator that scans every .required input in the visible tab.
      $('#filter_go').on('click', function(){
         var params = { _g: 'settings', node: 'redirects' };
         var p = $.trim($('#filter_path').val());
         var r = $('#filter_redirect').val();
         var t = $('#filter_type').val();
         if (p) params.filter_path = p;
         if (r) params.filter_redirect = r;
         if (t) params.filter_type = t;
         window.location = '?' + $.param(params) + '#redirects';
      });
      $('#filter_path, #filter_redirect, #filter_type').on('keydown', function(e){
         if (e.which === 13) { e.preventDefault(); $('#filter_go').click(); }
      });
      $('#filter_clear').on('click', function(){
         window.location = '?_g=settings&node=redirects#redirects';
      });
      refresh();
      $sel.on('change', refresh);

      // Custom suggest reads the type from the dropdown each search rather
      // than relying on the rel attribute, which the plugin captures at init.
      function dynamicSuggest(term, cb){
         var type = $('option:selected', $sel).val();
         var $opt = $('option:selected', $sel);
         if ($opt.data('static') === true || $opt.data('static') === 'true') { cb([]); return; }
         $.get('./'+ADMIN_FILE, {_g:'xml', type:type, q:term, 'function':'search'}, function(rows){
            var out = [];
            for (var a = 0; a < rows.length; a++) {
               out.push({ id: rows[a].value, value: rows[a].display, info: rows[a].info, data: rows[a].data });
            }
            cb(out);
         }, 'json');
      }

      $in.autocomplete({
         timeout: 5e3,
         minchars: 2,
         ajax_get: dynamicSuggest,
         callback: function(item){
            if (!item || !item.id) return;
            $hid.val(item.id);
            var d = item.data || {};
            showLabel((d.name || d.cat_name || d.doc_name || item.value || '') + ' (#'+item.id+')');
         }
      });
   }
   if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
   } else {
      init();
   }
})();
{/literal}
</script>
