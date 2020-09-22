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
               <th>{$LANG.common.object}</th>
               <th>{$LANG.common.object_id}</th>
               <th>{$LANG.common.status_code}</th>
               <th>{$LANG.form.action}</th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <td><input type="text" name="path" class="textbox required"></td>
               <td id="destination"></td>
               <td>
                  <select name="type" id="type">
                     <option value="prod">{$LANG.common.product}</option>
                     <option value="cat">{$LANG.common.category}</option>
                     <option value="doc">{$LANG.common.document}</option>
                  </select>
               </td>
               <td>
                  <input type="number" name="item_id" id="item_id" class="textbox number required" onkeyup="getSEODestination()">
               </td>
               <td>
               <select name="redirect">
                     <option value="301">301</option>
                     <option value="302">302</option>
                  </select>
               </td>
               <td align="center"><input id="submit" type="submit" class="tiny button" value="{$LANG.common.add}"></td>
            </tr>
            {foreach $REDIRECTS item=redirect}
            <tr>
               <td>{$redirect.path}</td>
               <td>{$redirect.destination}</td>
               <td align="center">
               {if $redirect.type=='prod'}
               {$LANG.common.product}
               {elseif $redirect.type=='cat'}
               {$LANG.common.category}
               {elseif $redirect.type=='doc'}
               {$LANG.common.document}
               {/if}
               </td>
               <td align="center">
               {if empty($redirect.item_id)}
               {$lang.common.na}
               {else}
               {$redirect.item_id}
               {/if}</td>
               <td align="center">{$redirect.redirect}</td>
               <td align="center"><a href="?_g=settings&node=redirects&delete={$redirect.id}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></td>
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