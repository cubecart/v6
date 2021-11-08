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
<div>
   <form action="{$VAL_SELF}" method="post">
      <div id="groups" class="tab_content">
         <h3>{$LANG.catalogue.title_option_groups}</h3>
         <table>
            <thead>
               <tr>
                  <td width="20">{$LANG.common.arrange}</td>
                  <td width="70" align="center">{$LANG.common.required}</td>
                  <td width="300">{$LANG.common.name}</td>
                  <td width="150">{$LANG.catalogue.option_group_type}</td>
                  <td width="300">{$LANG.common.description}</td>
                  <td width="50">&nbsp;</td>
               </tr>
            </thead>
            <tbody class="reorder-list">
               {foreach from=$GROUPS item=group}
               <tr>
                  <td style="text-align:center"><a href="#" class="handle"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/updown.gif" title="{$LANG.ui.drag_reorder}"></a>
                     <input type="hidden" name="group_priority[]" value="{$group.id}">
                  </td>
                  <td style="text-align:center"><input type="hidden" name="edit_group[{$group.id}][option_required]" id="status_{$group.id}" value="{$group.required}" class="toggle"></td>
                  <td><span class="editable" name="edit_group[{$group.id}][option_name]">{$group.name}</span></td>
                  <td><span class="editable select" name="edit_group[{$group.id}][option_type]">{$group.type_name}</span></td>
                  <td><span class="editable" name="edit_group[{$group.id}][option_description]">{$group.description}</span>&nbsp;</td>
                  <td style="text-align:center"><a href="{$group.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
               </tr>
               {foreachelse}
               <tr>
                  <td style="text-align:center" colspan="6">{$LANG.catalogue.no_option_groups}</td>
               </tr>
               {/foreach}
            </tbody>
         </table>
         <fieldset>
            <legend>{$LANG.catalogue.title_option_group_add}</legend>
            <div><label for="new-group-name">{$LANG.catalogue.option_group_name}</label><span><input type="text" name="add-group[option_name]" id="new-group-name" class="textbox"></span></div>
            <div><label for="new-group-description">{$LANG.common.description}</label><span><input type="text" name="add-group[option_description]" id="new-group-description" class="textbox"></span></div>
            <div>
               <label for="new-group-type">{$LANG.catalogue.option_group_type}</label>
               <span>
                  <select name="add-group[option_type]" id="new-group-type" class="textbox">
                     {foreach from=$OPTION_TYPES item=type key=key}
                     <option value="{$key}">{$type}</option>
                     {/foreach}
                  </select>
               </span>
            </div>
            <div><label for="new-group-required">{$LANG.common.required}</label><span><input type="hidden" name="add-group[option_required]" id="new-group-required" class="toggle" value="0"></span></div>
         </fieldset>
         <script type="text/javascript">
            {if isset($OPTION_TYPE_JSON)}var select_data = {$OPTION_TYPE_JSON}{/if}
             
         </script>
      </div>
      <div id="attributes" class="tab_content">
         <h3>{$LANG.catalogue.title_option_attributes}</h3>
         <div>
            <select name="add-value[option_id]" id="select_group_id" rel="group_" class="field_select">
               {foreach from=$GROUPS item=group}{if $group.type==0 || $group.type==4}
               <option value="{$group.id}">{$group.name}</option>
               {/if}{/foreach}
            </select>
         </div>
         <fieldset>
            <legend>{$LANG.catalogue.title_option_attribute_add}</legend>
            <div class="inline-add">
               <label for="new-value-name">{$LANG.common.name}</label>
               <span>
               <input type="text" name="add-value[value_name]" id="new-value-name" rel="attr_name" class="textbox">
               <a href="#" id="group_target" class="add"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a>
               </span>
            </div>
            <div id="attr_source" class="inline-source">
               <table width="100%">
                  <tbody>
                     <tr>
                        <td width="10">
                        </td>
                        <td name="edit_attribute[{$key}][value_name]"><span rel="attr_name"></span><input type="hidden" rel="attr_name"></td>
                        <td width="10" class="actions">
                           <a href="#" class="remove" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </fieldset>
         {foreach from=$GROUPS item=group}
         {if $group.type==0 || $group.type==4}
         <fieldset id="group_{$group.id}" class="field_select_target">
            <legend>{$group.name}</legend>
            <table width="100%">
               <tbody class="reorder-list">
                  {foreach from=$group.options key=key item=option}
                  <tr>
                     <td width="10"><a href="#" class="handle"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/updown.gif" title="{$LANG.ui.drag_reorder}"></a>
                        <input type="hidden" name="attr_priority[]" value="{$key}">
                     </td>
                     <td><span class="editable" name="edit_attribute[{$key}][value_name]">{$option}</span></td>
                     <td width="10" class="actions">
                        <a href="?_g=products&node=options&delete=attribute&id={$key}&token={$SESSION_TOKEN}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
                     </td>
                  </tr>
                  {foreachelse}
                  <tr class="dymanic_none">
                     <td colspan="3">{$LANG.catalogue.option_attributes_none}</td>
                  </tr>
                  {/foreach}
               </tbody>
            </table>
         </fieldset>
         {/if}
         {/foreach}
      </div>
      <div id="sets" class="tab_content">
         <h3>{$LANG.catalogue.title_option_sets}</h3>
         {if $SETS}
         <div>
            <select name="set_id" id="" rel="set_" class="field_select">
               <option value="0">{$LANG.form.please_select}</option>
               {foreach from=$SETS item=set}
               <option value="{$set.set_id}">{$set.set_name}</option>
               {/foreach}
            </select>
         </div>
         {foreach from=$SETS item=set}
         <fieldset class="field_select_target" style="display:none" id="set_{$set.set_id}" rel="add_options">
            <legend>{$set.set_name} <a href="{$set.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></legend>
               {foreach from=$set.members key=set_id item=members}
               {foreach from=$members item=member}
               <div>
                  <span class="actions">
                  <a href="#" class="remove" name="member_delete" rel="{$member.set_member_id}" title=""><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
                  </span>
                  {$member.display}
               </div>
               {/foreach}
               {foreachelse}
               <div>{$LANG.catalogue.no_option_set_attributes}</div>
               {/foreach}
         </fieldset>
         {/foreach}
         <fieldset id="add_options" class="field_select_target">
            <legend>{$LANG.catalogue.title_option_set_append}</legend>
            <div>
               <select name="add_to_set[]" class="multi" multiple="multiple" style="width: 200px; height:200px">
                  <option value="">{$LANG.form.please_select}</option>
                  {foreach from=$GROUPS item=group}
                  {if $group.type == 0}
                  <optgroup label="{$group.name}">
                     {foreach from=$group.options key=value_id item=value_name}
                     <option value="g{$group.id}-{$value_id}">{$value_name}</option>
                     {/foreach}
                  </optgroup>
                  {else if $group.type == 4}
                  <optgroup label="{$group.name}">
                     {foreach from=$group.options key=value_id item=value_name}
                     <option value="g{$group.id}-{$value_id}">{$value_name}</option>
                     {/foreach}
                  </optgroup>
                  {else}
                  <option value="{$group.id}">{$group.name}</option>
                  {/if}
                  {/foreach}
               </select>
            </div>
         </fieldset>
         {/if}
         <fieldset>
            <legend>{$LANG.catalogue.title_option_set_add}</legend>
            <div><label for="new-set-name">{$LANG.common.name}</label><span><input type="text" name="set_create[set_name]" id="new-set-name" class="textbox"></span></div>
            <div><label for="new-set-desc">{$LANG.common.description}</label><span><input type="text" name="set_create[set_description]" id="new-set-desc" class="textbox"></span></div>
         </fieldset>
      </div>
      {include file='templates/element.hook_form_content.php'}
      <div class="form_control">
         <input type="hidden" name="previous-tab" id="previous-tab" value="">
         <input type="submit" class="button" value="{$LANG.common.save}">
      </div>
      
   </form>
</div>
