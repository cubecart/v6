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
<form id="form-settings" action="{$PHP_SELF}" method="post" enctype="multipart/form-data">
   <div id="taxclasses" class="tab_content">
      <h3>{$LANG.settings.title_tax_class}</h3>
      <table>
         <thead>
            <tr>
               <td>{$LANG.common.name}</td>
               <td width="200">{$LANG.form.action}</td>
               <td width="20">&nbsp;</td>
            </tr>
         </thead>
         <tbody>
            {foreach from=$TAX_CLASSES item=class}
            <tr>
               <td><input type="text" name="class[{$class.id}][tax_name]" value="{$class.tax_name}" class="textbox"></td>
               <td><a href="?_g=settings&node=tax&assign_class={$class.id}&token={$SESSION_TOKEN}#taxclasses" class="delete" title="{$LANG.notification.confirm_update}">{$LANG.settings.assign_all_products}</a></td>
               <td style="text-align:center"><a href="{$VAL_SELF}&delete_class={$class.id}&token={$SESSION_TOKEN}" class="delete" title="{$LANG.settings.tax_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
            </tr>
            {foreachelse}
            <tr>
               <td colspan="3" style="text-align:center">&mdash; {$LANG.common.none} &mdash;</td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      <fieldset>
         <legend>{$LANG.settings.title_tax_class_add}</legend>
         <div><label for="addclass">{$LANG.settings.tax_class_name}</label><span><input name="addclass[tax_name]" id="addclass" type="text" class="textbox" value=""></span></div>
      </fieldset>
   </div>
   <div id="taxdetails" class="tab_content">
      <h3>{$LANG.settings.title_tax_detail}</h3>
      <table>
         <thead>
            <tr>
               <td width="50">{$LANG.common.status}</td>
               <td width="302">{$LANG.settings.tax_name}</td>
               <td width="302">{$LANG.settings.tax_name_display}</td>
               <td width="20">&nbsp;</td>
            </tr>
         </thead>
         <tbody>
            {foreach from=$TAX_DETAILS item=detail}
            <tr>
               <td style="text-align:center"><input type="hidden" name="detail[{$detail.id}][status]" id="detail_{$detail.id}" value="{$detail.status}" class="toggle"></td>
               <td><span class="editable" name="detail[{$detail.id}][name]">{$detail.name}</span></td>
               <td><span class="editable" name="detail[{$detail.id}][display]">{$detail.display}</span></td>
               <td style="text-align:center"><a href="{$VAL_SELF}&delete_detail={$detail.id}&token={$SESSION_TOKEN}" class="delete" title="{$LANG.settings.tax_delete}" ><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      <fieldset>
         <legend>{$LANG.settings.title_tax_detail_add}</legend>
         <div><label for="detail-name">{$LANG.settings.tax_name}</label><span><input name="adddetail[name]" id="detail-name" type="text" class="textbox" value=""></span></div>
         <div><label for="detail-display">{$LANG.settings.tax_name_display}</label><span><input name="adddetail[display]" id="detail-display" type="text" class="textbox" value=""></span></div>
         <div>
            <label for="detail-status">{$LANG.common.status}</label>
            <span>
               <select name="adddetail[status]" id="detail-status" class="textbox">
                  <option value="0">{$LANG.common.disabled}</option>
                  <option value="1">{$LANG.common.enabled}</option>
               </select>
            </span>
         </div>
      </fieldset>
   </div>
   <div id="taxrules" class="tab_content">
      <h3>{$LANG.settings.title_tax_rule}</h3>
      <fieldset>
         <table>
            <thead>
               <tr>
                  <td width="50">{$LANG.common.status}</td>
                  <td width="100">{$LANG.settings.title_tax_class}</td>
                  <td>{$LANG.settings.title_tax_detail}</td>
                  <td width="150">{$LANG.address.country}</td>
                  <td width="150">{$LANG.address.state}</td>
                  <td width="50">{$LANG.basket.total_sub}</td>
                  <td width="50">{$LANG.basket.shipping}</td>
                  <td width="125">{$LANG.settings.tax_rate}</td>
                  <td width="20">&nbsp;</td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$TAX_RULES item=rule}
               <tr>
                  <td style="text-align:center"><input type="hidden" name="rule[{$rule.id}][active]" id="rule_{$rule.id}" value="{$rule.active}" class="toggle"></td>
                  <td>{$rule.class}</td>
                  <td>{$rule.detail}</td>
                  <td>{$rule.country}</td>
                  <td>{$rule.state}</td>
                  <td style="text-align:center"><input type="hidden" name="rule[{$rule.id}][goods]" id="goods_{$rule.id}" value="{$rule.goods}" class="toggle"></td>
                  <td style="text-align:center"><input type="hidden" name="rule[{$rule.id}][shipping]" id="shipping_{$rule.id}" value="{$rule.shipping}" class="toggle"></td>
                  <td nowrap="nowrap"><input type="text" name="rule[{$rule.id}][tax_percent]" class="textbox number" style="text-align: right;" value="{$rule.tax_percent}"> %</td>
                  <td style="text-align:center"><a href="{$VAL_SELF}&delete_rule={$rule.id}&token={$SESSION_TOKEN}" class="delete" title="{$LANG.notification.confirm_delete}" ><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
               </tr>
               {foreachelse}
               <tr>
                  <td style="text-align:center" colspan="9">&mdash; {$LANG.common.none} &mdash;</td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_tax_rule_add}</legend>
         <div>
            <label for="rule-class">{$LANG.settings.tax_class}</label>
            <span>
               <select name="addrule[type_id]" class="textbox" id="rule-class">
                  {foreach from=$TAX_CLASSES item=class}
                  <option value="{$class.id}">{$class.tax_name}</option>
                  {/foreach}
               </select>
            </span>
         </div>
         <div>
            <label for="rule-detail">{$LANG.settings.tax_detail}</label>
            <span>
               <select name="addrule[details_id]" class="textbox" id="rule-detail">
                  {foreach from=$TAX_DETAILS item=detail}
                  <option value="{$detail.id}">{$detail.display}</option>
                  {/foreach}
               </select>
            </span>
         </div>
         <div><label for="rule-eu">{$LANG.country.assign_to_eu}</label><span>
            <input type="checkbox" name="addrule[eu]" id="rule-eu" value="1" />
            </span>
         </div>
         <div><label for="rule-eu">{$LANG.country.assign_to_rest}</label><span>
            <input type="checkbox" name="addrule[rest]" id="rule-rest" value="1" />
            </span>
         </div>
         <div id="country-region">
            <div><label for="country-list">{$LANG.address.country}</label><span><select name="addrule[country_id]" id="country-list" class="textbox no-custom-zone" title="{$LANG.common.regions_all}">
               {foreach from=$COUNTRIES item=country}<option value="{$country.numcode}" {if $country.numcode == $CONFIG.store_country}selected="selected"{/if}>{$country.name}</option>{/foreach}
               </select></span>
            </div>
            <div><label for="state-list">{$LANG.address.state}</label><span><input name="addrule[county_id]" type="text" id="state-list" class="textbox" value="{$VAL_TAX_STATE}"></span></div>
         </div>
         <div><label for="rule-taxrate">{$LANG.settings.tax_rate}</label><span><input name="addrule[tax_percent]" id="rule-taxrate" type="text" class="textbox number"> %</span></div>
         <div><label for="rule-applyto">{$LANG.settings.tax_apply_to}</label><span>
            <input type="hidden" name="addrule[goods]" id="rule-goods" value="0" class="toggle"> {$LANG.settings.tax_on_goods} 
            <input type="hidden" name="addrule[shipping]" id="rule-shipping" value="0" class="toggle"> {$LANG.settings.tax_on_shipping} 
            </span>
         </div>
         <div>
            <label for="rule-status">{$LANG.common.status}</label>
            <span>
               <select name="addrule[active]" id="rule-status" class="textbox">
                  <option value="0">{$LANG.common.disabled}</option>
                  <option value="1">{$LANG.common.enabled}</option>
               </select>
            </span>
         </div>
      </fieldset>
   </div>
   <div id="tariff" class="tab_content">
      <h3>{$LANG.settings.title_tariffs}</h3>
      <fieldset>
         <table>
            <thead>
               <tr>
                  <td>{$LANG.settings.source_country}</td>
                  <td>{$LANG.settings.destination_country}</td>
                  <td>{$LANG.common.tariff}</td>
                  <td>{$LANG.common.percentage}</td>
                  <td>{$LANG.common.display} {$LANG.common.optional}</td>
                  <td>&nbsp;</td>
               </tr>
            </thead>
            <tbody>
               {foreach from=$TARIFFS item=tariff}
               <tr>
                  <td>{$tariff.source}</td>
                  <td>{$tariff.destination}</td>
                  <td>{$tariff.tariff}</td>
                  <td><input type="number" value="{$tariff.percent}" name="edittariff[{$tariff.id}][percent]" class="textbox number text-right no-arrows" step="0.01" min="0" max="999.99" /> &#37;</td>
                  <td><input type="text" value="{$tariff.display}" name="edittariff[{$tariff.id}][display]" class="textbox" /></td>
                  <td><a href="?_g=settings&node=tax&delete_tariff={$tariff.id}&token={$SESSION_TOKEN}#tariff" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
               </tr>
               {foreachelse}
               <tr>
                  <td colspan="6" class="text-center">&mdash; {$LANG.common.none} &mdash;</td>
               </tr>
               {/foreach}
               <tr>
                  <td>
                     <select name="addtariff[source]" style="max-width: 180px" class="textbox">
                     {foreach from=$COUNTRIES item=country}
                     <option value="{$country.iso}">{$country.name}</option>
                     {/foreach}
                     </select>
                  </td>
                  <td>
                     <select name="addtariff[destination]" style="max-width: 180px" class="textbox">
                     {foreach from=$COUNTRIES item=country}
                     <option value="{$country.iso}">{$country.name}</option>
                     {/foreach}
                     </select>
                  </td>
                  <td>
                     <select name="addtariff[tariff]" class="textbox">
                        <option value="M">{$LANG.settings.country_of_manufacture}</option>
                        <option value="D">{$LANG.settings.country_of_dispatch}</option>
                     </select>
                  </td>
                  <td><input type="number" value="" name="addtariff[percent]" class="textbox number text-right no-arrows" step="0.01" min="0" max="999.99" /> &#37;</td>
                  <td><input type="text" value="" name="addtariff[display]" class="textbox" placeholder="{$LANG.settings.tariff_display_eg}" /> *</td>
                  <td><button type="submit" class="button tiny">{$LANG.common.add}</button></td>
               </tr>
            </tbody>
         </table>
         <p>* {$LANG.settings.note_tariff_grouping}</p>
      </fieldset>
      <p>{$LANG.settings.tariff_import_notice}</p>
   </div>
      {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
   {$tab}
   {/foreach}
   {/if}
{include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="submit" id="submit" class="button" value="{$LANG.common.save}">
      <input type="hidden" name="previous-tab" id="previous-tab" value="">
   </div>
</form>
<script type="text/javascript">
   var county_list = {if !empty($VAL_JSON_COUNTY)}{$VAL_JSON_COUNTY}{else}false{/if};
</script>