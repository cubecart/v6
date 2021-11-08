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
               <th>{$LANG.common.path}</th>
               <th>{$LANG.common.destination}</th>
               <th>{$LANG.common.page}</th>
               <th>{$LANG.common.item_id}</th>
               <th>{$LANG.common.status_code}</th>
               <th>{$LANG.form.action}</th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <td><input type="text" name="path" class="textbox required"></td>
               <td id="destination"></td>
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
               <td width="110">
                  <input type="number" name="item_id" id="item_id" class="textbox number required" onkeyup="getSEODestination()">
               </td>
               <td>
               <select name="redirect">
                     <option value="301">301 - {$LANG.common.permanent}</option>
                     <option value="302">302 - {$LANG.common.temporary}</option>
                  </select>
               </td>
               <td style="text-align:center"><input id="submit" type="submit" class="tiny button" value="{$LANG.common.add}"></td>
            </tr>
            {foreach $REDIRECTS item=redirect}
            <tr>
               <td>{$redirect.path}</td>
               <td>{$redirect.destination}</td>
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
               <td style="text-align:center">
               {if empty($redirect.item_id)}
                  -
               {else}
                  {$redirect.item_id}
               {/if}</td>
               <td style="text-align:center">{$redirect.redirect}</td>
               <td style="text-align:center"><a href="?_g=settings&node=redirects&delete={$redirect.id}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></td>
            </tr>
            {foreachelse}
            <tr>
               <td colspan="6">{$LANG.common.none}</td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      {$PAGINATION}
   </div>
   {include file='templates/element.hook_form_content.php'}
</form>