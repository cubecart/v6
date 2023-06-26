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
<form action="{$VAL_SELF}" id="hook_form" method="post" enctype="multipart/form-data">
   <div id="redirects" class="tab_content">
      <h3>{$LANG.settings.redirects}</h3>
      <table>
         <thead>
            <tr>
               <th>{$LANG.form.action}</th>
               <th>{$LANG.common.status_code}</th>
               <th>{$LANG.common.page}</th>
               <th>{$LANG.settings.redirect_from}</th>
               <th>{$LANG.common.item_id}</th>
               <th>{$LANG.settings.redirect_to}</th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <td style="text-align:center"><input id="submit" type="submit" class="tiny button" value="{$LANG.common.add}"></td>
               <td>
               <select name="redirect">
                     <option value="301">301 - {$LANG.common.permanent}</option>
                     <option value="302">302 - {$LANG.common.temporary}</option>
                  </select>
               </td>
               <td>
                  <select name="type" id="redirect_type">
                     <optgroup label="Dynamic Pages">
                        <option value="cat" data-static="false">{$LANG.common.category}</option>
                        <option value="doc" data-static="false">{$LANG.common.document}</option>
                        <option value="prod" data-static="false">{$LANG.common.product}</option>
                     </optgroup>
                     <optgroup label="Static Pages">
                        <option value="certificates" data-static="true">{$LANG.catalogue.gift_certificates}</option>
                        <option value="contact" data-static="true">{$LANG.documents.document_contact}</option>
                        <option value="login" data-static="true">{$LANG.account.login}</option>
                        <option value="register" data-static="true">{$LANG.account.register}</option>
                        <option value="saleitems" data-static="true">{$LANG.navigation.saleitems}</option>
                        <option value="search" data-static="true">{$LANG.common.search}</option>
                     </optgroup>
                  </select>
               </td>
               <td><input type="text" name="path" class="textbox required"></td>
               <td width="110">
                  <input type="number" name="item_id" id="item_id"  min="1" class="textbox number required" onkeyup="getSEODestination()">
               </td> 
               <td id="destination"></td>  
            </tr>
            {foreach $REDIRECTS item=redirect}
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
               <td>{$redirect.path}</td>
               <td style="text-align:center">
               {if empty($redirect.item_id)}
                  -
               {else}
                  {$redirect.item_id}
               {/if}</td>
               <td>{$redirect.destination}</td>
            </tr>
            {foreachelse}
            <tr>
               <td colspan="6">{$LANG.common.none}</td>
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
               <th>ID</th>
               <th>URI</th>
               <th>{$LANG.statistics.product_hits}</th>
               <th>{$LANG.common.created}</th>
               <th>{$LANG.common.done}</th>
               <th>{$LANG.common.ignore}</th>
               <th>&nbsp;</th>
            </tr>
         </thead>
         <tbody>
            {foreach $MISSING item=m}
            <tr>
               <td>{$m.id}</td>
               <td>{$m.uri}</td>
               <td style="text-align: center">{$m.hits}</td>
               <td>{$m.updated}</td>
               <td style="text-align: center">{if $m.done == '1'}<i class="fa fa-check-circle done_toggle" aria-hidden="true" data-id="{$m.id}" data-status="1" data-table="404_log"></i>{else}<i class="fa fa-times-circle done_toggle" aria-hidden="true" data-id="{$m.id}" data-status="0" data-table="404_log"></i>{/if}</td>
               <td style="text-align: center"><a href="?_g=settings&node=redirects&ignore={$m.id}#missing_uris"><i class="fa fa-ban" aria-hidden="true" title="{$LANG.common.ignore}"></i></a></td>
               <td style="text-align: center">{if $m.warn == '1' && $m.done == '1'}<i class="fa fa-exclamation-triangle done_toggle" id="warn_{$m.id}" data-id="{$m.id}" data-status="warn" data-table="404_log" aria-hidden="true" title="{$LANG.common.remove}"></i>{/if}</td>
            </tr>
            {foreachelse}
            <tr>
               <td colspan="4">{$LANG.common.none}</td>
            </tr>
            {/foreach}
         </tbody>
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
               <td>{$i.uri}</td>
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
   </div>
   {include file='templates/element.hook_form_content.php'}
</form>