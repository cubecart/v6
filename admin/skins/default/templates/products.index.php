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
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   {if isset($DISPLAY_PRODUCT_LIST)}
   <div id="general" class="tab_content">
      <h3>{$LANG.catalogue.title_product_inventory}</h3>
      <table align="right">
         <tr>
         <td>&nbsp;</td>
            <td>{$LANG.common.category}</td>
            <td>{$LANG.common.status}</td>
         </tr>
         <tr>
            <td><strong>{$LANG.common.filter}:</strong></td>
            <td> 
               <select class="product_list_filter">
                  <option value="{$CAT_LIST_ANY}">{$LANG.common.any}</option>
                  {foreach from=$CAT_LIST item=cat_dropdown}
                  <option value="?_g=products&amp;cat_id={$cat_dropdown.cat_id}{if $STATUS_FILTER}&amp;status_filter={$STATUS_FILTER}{/if}" {if $cat_dropdown.cat_id == $CURRENT_CAT}selected="selected"{/if}>{$cat_dropdown.name}</option>
                  {/foreach}
               </select>
            </td>
            <td>
               <select class="product_list_filter">
                  <option value="?_g=products&amp;cat_id={$CURRENT_CAT}" {if !$STATUS_FILTER || $STATUS_FILTER==''}selected="selected"{/if}>{$LANG.common.any}</option>
                  <option value="?_g=products&amp;cat_id={$CURRENT_CAT}&amp;status_filter=1" {if $STATUS_FILTER=='1'}selected="selected"{/if}>{$LANG.common.enabled}</option>
                  <option value="?_g=products&amp;cat_id={$CURRENT_CAT}&amp;status_filter=0" {if $STATUS_FILTER=='0'}selected="selected"{/if}>{$LANG.common.disabled}</option>
               </select>
            </td>
         </tr>
      </table>
      {if isset($PRODUCTS)}
      <table width="100%">
         <tr>
            {foreach from=$SORT_CHARACTERS item=character}
            <td style="text-align:center"><a href="{$character.link}">{$character.char}</a></td>
            {/foreach}
            <td width="1%"><a href="{$SORT_CHARS_RESET_LINK}">{$LANG.common.any}</a></td>
         </tr>
      </table>
      <table width="100%">
         <thead>
            <tr>
               <th nowrap="nowrap">&nbsp;</th>
               <th nowrap="nowrap">{$THEAD.name}</th>
               <th nowrap="nowrap">{$THEAD.digital}</th>
               <th nowrap="nowrap">{$THEAD.image}</th>
               <th nowrap="nowrap">{$THEAD.product_code}</th>
               <th nowrap="nowrap">{$THEAD.price}</th>
               <th nowrap="nowrap">{$THEAD.stock_level}</th>
               <th nowrap="nowrap">{$THEAD.updated}</th>
               <th nowrap="nowrap">{$THEAD.translations}</th>
               <th nowrap="nowrap">{$THEAD.status}</th>
               <th nowrap="nowrap">&nbsp;</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$PRODUCTS item=product}
            <tr>
               <td style="text-align:center" width="10"><input type="checkbox" name="delete[]" id="{$product.product_id}" value="{$product.product_id}" class="table"></td>
               <td><a href="{$product.link_edit}">{$product.name}</a>
                  {if isset($product.category)}
                  <br>
                  <span class="light-category">{$product.category}</span>
                  {/if}
               </td>
               <td style="text-align:center">
                  {if $product.digital}
                  <i class="fa fa-download" title="{$product.type_alt}"></i>
                  {else}
                  <i class="fa fa-archive" title="{$product.type_alt}"></i>
                  {/if}
               </td>
               <td style="text-align:center">
                  {if !empty($product.image_path_tiny)}
                  <a href="{$product.image_path_large}" class="colorbox" title="{$product.name}" target="_blank"><img src="{$product.image_path_tiny}" style="max-width: 30px; max-height: 30px" alt="{$product.name}"></a>
                  {elseif !empty($product.image_path_large)}
                  <a href="{$product.image_path_large}" class="colorbox" title="{$product.name}" target="_blank"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/image.png" alt="{$product.name}"></a>
                  {/if}
               </td>
               <td>{$product.product_code}</td>
               <td>{$product.price}</td>
               <td style="text-align:center">{$product.stock_level}</td>
               <td>
                  {if $product.updated == '0000-00-00 00:00:00'}
                  {$LANG.common.unknown}
                  {else}
                  {formatTime(strtotime($product.updated))}
                  {/if}
               </td>
               <td style="text-align:center" class="language_list">
                  {foreach from=$product.translations item=translation}
                  <a href="{$translation.link}"><img src="language/flags/{$translation.language}.png" alt="{$translation.language}" class="flag"></a>
                  {/foreach}
               </td>
               <td style="text-align:center"><input type="hidden" name="status[{$product.product_id}]" id="status_{$product.product_id}" value="{$product.status}" class="toggle"></td>
               <td style="text-align:center">
                  {if isset($product.link_clone)}
                  <a href="{$product.link_clone}" title="{$LANG.common.clone}" alt="{$LANG.common.clone}"><i class="fa fa-files-o" title="{$LANG.common.clone}"></i></a>
                  {/if}
                  <a href="{$product.link_preview}" title="{$LANG.common.preview}" target="_blank" alt="{$LANG.common.preview}"><i class="fa fa-search" title="{$LANG.common.preview}"></i></a>
                  <a href="{$product.link_edit}" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
                  <a href="{$product.link_delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
               </td>
            </tr>
            {/foreach}
         </tbody>
         <tfoot>
            <tr>
               <td width="10">
                  <span><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></span>
               </td>
               <td colspan="11">
                  <a href="#" class="check-all" rel="table">{$LANG.form.check_uncheck}</a>
                  {$LANG.maintain.db_with_selected}
                  <select name="action" class="textbox">
                     <optgroup label="">
                        <option value="">{$LANG.form.please_select}</option>
                        <option value="delete">{$LANG.common.delete}</option>
                     </optgroup>
                  </select>
               </td>
            </tr>
            <tr>
               <td colspan="11">
                  <div class="pagination">
                     <span>{$LANG.common.total}: {$TOTAL_RESULTS}</span>
                     {$PAGINATION}&nbsp;
                  </div>
               </td>
            </tr>
         </tfoot>
      </table>
      {else}
      <p>{$LANG.form.none}</p>
      {/if}
   </div>
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="submit" value="{$LANG.common.save}">
   </div>
   {/if}
   {if isset($DISPLAY_PRODUCT_FORM)}
   <div id="general" class="tab_content">
      <h3>{$LANG.catalogue.title_information_general}</h3>
      <input type="hidden" name="product_id" value="{$PRODUCT.product_id}">
      <fieldset>
         <legend>{$LANG.catalogue.title_information_basic}</legend>
         <div><label for="">{$LANG.common.status}</label><span><input type="hidden" name="status" id="product_status" value="{$PRODUCT.status}" class="toggle"></span></div>
         <div><label for="name">{$LANG.catalogue.product_name}</label><span><input name="name" id="name" class="textbox required" type="text" value="{$PRODUCT.name}"></span></div>
         <div>
            <label for="manufacturer">{$LANG.catalogue.title_manufacturer}</label>
            <span>
               <select name="manufacturer" id="manufacturer" class="textbox" type="text">
                  <option value="">{$LANG.form.none}</option>
                  {foreach from=$MANUFACTURERS item=manufacturer}
                  <option value="{$manufacturer.id}"{$manufacturer.selected}>{$manufacturer.name}</option>
                  {/foreach}
               </select>
            </span>
         </div>
         <div><label for="condition">{$LANG.catalogue.condition}</label>
            <span>
            <select name="condition" id="condition" class="textbox" type="text">
            {foreach from=$CONDITIONS item=condition}
               <option value="{$condition@key}"{if $PRODUCT.condition == $condition@key} selected="selected"{/if}>{$condition}</option>
            {/foreach}
            </select>
            </span>
         </div>
         <div><label for="product_code">{$LANG.catalogue.product_code}</label><span><input name="product_code" id="product_code" class="textbox" type="text" value="{$PRODUCT.product_code}"></span></div>
         <div><label for="product_code_auto">{$LANG.catalogue.product_code_auto}</label><span><input name="product_code_auto" id="product_code_auto" type="hidden" class="toggle" value="{$PRODUCT.auto_code_checked}"> <input name="product_code_old" id="product_code_old" type="hidden" value=""></span></div>
         <div><label for="product_weight">{$LANG.catalogue.product_weight}</label><span><input name="product_weight" id="product_weight" class="textbox number" type="text" value="{$PRODUCT.product_weight}"></span></div>
         <div>
            <label for="dimension_unit">{$LANG.catalogue.dimension_unit}</label>
            <span>
                <select name="dimension_unit" id="dimension_unit">
                    <option{if $PRODUCT.dimension_unit=='cm'} selected='selected'{/if} value="cm">Centimeters (cm)</option>
                    <option{if $PRODUCT.dimension_unit=='in'} selected='selected'{/if} value="in">Inches (in)</option>
                </select>
            </span>
        </div>
         <div><label for="product_width">{$LANG.catalogue.product_width}</label><span><input name="product_width" id="product_width" class="textbox number" type="text" value="{$PRODUCT.product_width}"></span></div>
         <div><label for="product_height">{$LANG.catalogue.product_height}</label><span><input name="product_height" id="product_height" class="textbox number" type="text" value="{$PRODUCT.product_height}"></span></div>
         <div><label for="product_depth">{$LANG.catalogue.product_depth}</label><span><input name="product_depth" id="product_depth" class="textbox number" type="text" value="{$PRODUCT.product_depth}"></span></div>
         <div><label for="product_featured">{$LANG.catalogue.product_featured}</label><span><input type="hidden" name="featured" id="product_featured" class="toggle" value="{$PRODUCT.featured}"></span></div>
         <div><label for="product_latest">{$LANG.catalogue.product_latest}</label><span><input type="hidden" name="latest" id="product_latest" class="toggle" value="{$PRODUCT.latest}"></span></div>
         <div><label for="available">{$LANG.catalogue.available_for_purchase}</label><span><input type="hidden" name="available" id="available" class="toggle" value="{if isset($PRODUCT.available)}{$PRODUCT.available}{else}1{/if}"></span></div>
         <div><label for="live_from">{$LANG.catalogue.live_from}</label><span><input name="live_from" id="live_from" class="textbox" type="text" value="{if $PRODUCT.live_from>0}{$PRODUCT.live_from|date_format:"%d %B %Y %H:%M:%S"}{/if}" placeholder="{$LANG.catalogue.live_from_eg}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.catalogue.title_stock_control}</legend>
         <div><label for="use_stock_level">{$LANG.catalogue.stock_level_use}</label><span><input type="hidden" name="use_stock_level" id="use_stock_level" class="toggle" value="{$PRODUCT.use_stock_level}"></span></div>
         <div><label for="stock_level">{$LANG.catalogue.stock_level}</label><span><input name="stock_level" id="stock_level" class="textbox number" type="text" value="{$PRODUCT.stock_level}"></span>{if isset($DISPLAY_MATRIX_STOCK_NOTE)}&nbsp;{$LANG.catalogue.matrix_stock_level}{/if}</div>
         <div><label for="stock_warning">{$LANG.catalogue.stock_level_warn}</label><span><input name="stock_warning" id="stock_warning" class="textbox number" type="text" value="{$PRODUCT.stock_warning}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.catalogue.title_misc}</legend>
         <div><label for="upc_code">{$LANG.catalogue.product_upc}</label><span><input name="upc" id="upc" class="textbox" type="text" value="{$PRODUCT.upc}" maxlength="20"></span></div>
         <div><label for="ean_code">{$LANG.catalogue.product_ean}</label><span><input name="ean" id="ean" class="textbox" type="text" value="{$PRODUCT.ean}" maxlength="20"></span></div>
         <div><label for="jan_code">{$LANG.catalogue.product_jan}</label><span><input name="jan" id="jan" class="textbox" type="text" value="{$PRODUCT.jan}" maxlength="20"></span></div>
         <div><label for="isbn_code">{$LANG.catalogue.product_isbn}</label><span><input name="isbn" id="isbn" class="textbox" type="text" value="{$PRODUCT.isbn}" maxlength="20"></span></div>
         <div><label for="gtin_code">{$LANG.catalogue.product_gtin}</label><span><input name="gtin" id="gtin" class="textbox" type="text" value="{$PRODUCT.gtin}" maxlength="20"></span></div>
         <div><label for="mpn_code">{$LANG.catalogue.product_mpn}</label><span><input name="mpn" id="mpn" class="textbox" type="text" value="{$PRODUCT.mpn}" maxlength="70"></span></div>
         <div>
            <label for="google_cat_code">{$LANG.catalogue.product_google_category}</label>
            <span>
               {if $GOOGLE_CATS}
               <select name="google_category" id="google_category" class="chzn-select">
                  <option value="">{$LANG.common.please_select} &hellip;</option>
                  {foreach from=$GOOGLE_CATS item=cat}<option value="{$cat}" {if $cat == $PRODUCT.google_category && !empty($PRODUCT.google_category)}selected="selected"{/if}>{$cat}</option>{/foreach}
               </select>
               {else}
               <input name="google_category" id="google_category" class="textbox" type="text" value="{$PRODUCT.google_category}" maxlength="250">
               {/if}
            </span>
         </div>
         {if $EXTERNAL_CATS}
         {foreach from=$EXTERNAL_CATS key=k item=v}
         <div>
            <label for="category_{$k}">{$k|ucfirst}</label>
            <span>
               <select name="category_{$k}" id="category_{$k}" class="textbox" style="font-size: 10px;">
                  <option value="">{$LANG.common.please_select} &hellip;</option>
                  {foreach from=$v key=cat_key item=cat_path}<option value="{$cat_path}"{if $PRODUCT["category_$k"] == $cat_path} selected="selected"{/if}>{$cat_path}</option>{/foreach}
               </select>
            </span>
         </div>
         {/foreach}
         {/if}
      </fieldset>
   </div>
   <div id="description" class="tab_content">
      <h3>{$LANG.common.description}</h3>
      <textarea name="description" class="textbox fck">{$PRODUCT.description}</textarea>
      <br>
      <h3>{$LANG.common.description_short} {$LANG.common.optional}</h3>
      <textarea name="description_short" id="description_short" class="textbox fck" type="text">{$PRODUCT.description_short|escape:"html"}</textarea>
   </div>
   <div id="pricing" class="tab_content">
      <h3>{$LANG.catalogue.title_pricing}</h3>
      {if isset($CUSTOMER_GROUPS)}
      <div>
         <select class="field_select" rel="group_">
            <option value="0">{$LANG.catalogue.pricing_standard}</option>
            {foreach from=$CUSTOMER_GROUPS item=group}
            <option value="{$group.group_id}">{$group.group_name}</option>
            {/foreach}
         </select>
      </div>
      {/if}
      <div id="group_0" class="field_select_target">
         <p>{$LANG.catalogue.pricing_standard}</p>
         <fieldset>
            <legend>{$LANG.catalogue.title_pricing}</legend>
            <div><label for="price">{$LANG.common.price_standard}</label><span><input name="price" id="price" class="textbox number-right" type="text" value="{$PRODUCT.price}"></span></div>
            <div><label for="sale_price">{$LANG.common.price_sale}</label><span><input name="sale_price" id="sale_price" class="textbox number-right" type="text" value="{$PRODUCT.sale_price}"></span></div>
            <div><label for="cost_price">{$LANG.common.price_cost}</label><span><input name="cost_price" id="cost_price" class="textbox number-right" type="text" value="{$PRODUCT.cost_price}"></span></div>
            <div>
               <label for="tax_type">{$LANG.catalogue.tax_class}</label>
               <span>
                  {if isset($TAXES)}
                  <select name="tax_type" id="tax_type" class="textbox">
                     <option value="">{$LANG.common.please_select} &hellip;</option>
                     {foreach from=$TAXES item=tax}<option value="{$tax.id}"{$tax.selected}>{$tax.tax_name}</option>{/foreach}
                  </select>
                  {else}{$LANG.catalogue.no_taxes_setup}{/if}
               </span>
            </div>
            <div><label for="tax_inclusive">{$LANG.catalogue.tax_included}</label><span><input type="hidden" name="tax_inclusive" id="tax_inclusive" class="toggle" value="{$PRODUCT.tax_inclusive}"></span></div>
            <div><label for="minimum_quantity">{$LANG.catalogue.minimum_quantity}</label><span><input name="minimum_quantity" id="minimum_quantity" class="textbox number-right" type="text" value="{$PRODUCT.minimum_quantity|default:'1'}"></span></div>
            <div><label for="maximum_quantity">{$LANG.catalogue.maximum_quantity}</label><span><input name="maximum_quantity" id="maximum_quantity" class="textbox number-right" type="text" value="{$PRODUCT.maximum_quantity}">&nbsp;{$LANG.common.blank_to_disable}</span></div>
         </fieldset>
         <fieldset>
            <table>
               <thead>
                  <tr>
                     <th width="150">{$LANG.common.quantity}</th>
                     <th width="150">{$LANG.common.price}</th>
                     <th>&nbsp;</th>
                  </tr>
               </thead>
               <tbody>
                  {if isset($QUANTITY_DISCOUNTS)}
                  {foreach from=$QUANTITY_DISCOUNTS item=discount}
                  <tr>
                     <td width="150">
                        <label><span class="editable number-right" name="discount[{$discount.discount_id}][quantity]" title="Click to edit">{$discount.quantity}</span></label>
                     </td>
                     <td width="150">
                        <input type="text" name="discount[{$discount.discount_id}][price]" class="textbox number" value="{$discount.price}">
                     </td>
                     <td>
                        <a href="#" rel="{$discount.discount_id}" class="remove tr" name="discount_delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
                     </td>
                  </tr>
                  {/foreach}
                  {/if}
                  <tr class="inline-add" id="qty_discounts">
                     <td width="150"><input type="text" rel="quantity" class="editable textbox number not-empty"></td>
                     <td width="150">
                        <input type="text" rel="price" class="textbox number not-empty">
                     </td>
                     <td><span class="actions"><a href="#" class="add before" target="qty_discounts"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a></span></td>
                  </tr>
                  <tr class="inline-source" name="discount_add[0]">
                     <td width="150"><input type="text" class="textbox number" rel="quantity"></td>
                     <td width="150"><input type="text" class="textbox number" rel="price"></td>
                     <td><span class="actions"><a href="#" class="remove dynamic tr" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></span></td>
                  </tr>
               </tbody>
            </table>
         </fieldset>
      </div>
      {foreach from=$CUSTOMER_GROUPS item=group}
      <div id="group_{$group.group_id}" class="field_select_target">
         <p>{$group.group_description}</p>
         <fieldset>
            <legend>{$LANG.catalogue.title_pricing}</legend>
            <div><label for="price">{$LANG.common.price_standard}</label><span><input name="group[{$group.group_id}][price]" id="price" class="textbox number-right" type="text" value="{$group.price}"></span></div>
            <div><label for="sale_price">{$LANG.common.price_sale}</label><span><input name="group[{$group.group_id}][sale_price]" id="sale_price" class="textbox number-right" type="text" value="{$group.sale_price}"></span></div>
            <div>
               <label for="tax_type">{$LANG.catalogue.tax_class}</label>
               <span>
               <select name="group[{$group.group_id}][tax_type]" id="tax_type" class="textbox">
               {foreach from=$group.tax_types item=tax_type}<option value="{$tax_type.id}"{$tax_type.selected}>{$tax_type.tax_name}</option>{/foreach}
               </select>
               </span>
            </div>
            <div>
               <label for="tax_inclusive">{$LANG.catalogue.tax_included}</label>
               <span>
               <input type="hidden" name="group[{$group.group_id}][tax_inclusive]" id="tax_inclusive_{$group.group_id}" class="toggle" value="{$group.tax_inclusive}">
               </span>
            </div>
         </fieldset>
         <fieldset>
            <legend>{$LANG.catalogue.title_discount_quantity}</legend>
            <div><label>{$LANG.common.quantity}</label><span>{$LANG.common.price}</span></div>
            <div style="width: 500px;">
               <div id="discount_list_{$group.group_id}" >
                  {foreach from=$group.quantities item=quantity}
                  <div>
                     <span class="actions"><a href="#" rel="{$quantity.discount_id}" class="remove" name="discount_delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></span>
                     <label><span class="editable number-right" name="discount[{$quantity.discount_id}][quantity]" title="Click to edit">{$quantity.quantity}</span></label><input type="text" name="discount[{$quantity.discount_id}][price]" class="textbox number-right" value="{$quantity.price}">
                  </div>
                  {/foreach}
               </div>
               <div class="inline-add">
                  <span class="actions"><a href="#" class="add" target="discount_list_{$group.group_id}"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a></span>
                  <label><input type="text" rel="quantity" class="textbox number not-empty"></label>
                  <input type="text" rel="price" class="textbox number-right not-empty">
               </div>
               <!-- Source for inline adding -->
               <div class="inline-source" name="discount_add[{$group.group_id}]">
                  <span class="actions"><a href="#" class="remove dynamic" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></span>
                  <label rel="quantity"></label><input type="hidden" rel="quantity"><input type="text" class="textbox number-right" rel="price">
               </div>
            </div>
         </fieldset>
      </div>
      {/foreach}
   </div>
   <div id="category" class="tab_content">
      <h3>{$LANG.settings.title_categories}</h3>
      <table>
         <thead>
            <tr>
               <th>{$LANG.catalogue.category_primary}</th>
               <th>{$LANG.catalogue.category_additional}</th>
               <th width="90%">{$LANG.settings.category_name}</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$CATEGORIES item=category}
            <tr>
               <td style="text-align:center"><input type="radio" name="primary_cat" class="check-primary" value="{$category.id}" rel="cat_{$category.id}"{$category.primary}></td>
               <td style="text-align:center"><input type="checkbox" id="cat_{$category.id}" name="categories[{$category.id}]" value="{$category.id}" class="check_cat" {$category.selected}></td>
               <td>{$category.name}</td>
            </tr>
            {/foreach}
         </tbody>
         <tfoot>
            <tr>
               <td>&nbsp;</td>
               <td style="text-align:center"><input type="checkbox" class="check-all" rel="check_cat"></td>
               <td><strong>{$LANG.form.check_uncheck}</strong></td>
            </tr>
         </tfoot>
      </table>
   </div>
   <div id="Options" class="tab_content">
      <h3>{$LANG.catalogue.title_product_options}</h3>
      <fieldset>
         <table>
            <thead>
               <tr>
                  <td>{$LANG.common.status}</td>
                  <td>{$LANG.catalogue.title_product_options_matrix}</td>
                  <td>{$LANG.common.name}</td>
                  <td>{$LANG.catalogue.title_option_set}</td>
                  <td>{$LANG.common.default}</td>
                  <td>{$LANG.common.negative}</td>
                  <td>{$LANG.common.price}</td>
                  <td>{$LANG.catalogue.absolute_price} *</td>
                  <td>{$LANG.common.weight}</td>
                  <td width="20">&nbsp;</td>
               </tr>
            </thead>
            <tbody id="options_added">
               {foreach from=$PRODUCT_OPTIONS item=options}
               {foreach from=$options item=option}
               {if $option.from_assigned}
               <tr id="option_{$option.assign_id}">
                  <td style="text-align:center"><input type="hidden" id="enable_{$option.assign_id}" name="option_update[{$option.assign_id}][set_enabled]" value="{$option.set_enabled}" class="toggle"></td>
                  <td style="text-align:center"><input type="hidden" id="matrix_include_{$option.assign_id}" name="option_update[{$option.assign_id}][matrix_include]" value="{$option.matrix_include}" class="toggle"></td>
                  <td>{$option.display}</td>
                  <td>{$option.set_name}</td>
                  <td style="text-align:center"><input type="checkbox" name="option_update[{$option.assign_id}][option_default]" {if isset($option.option_default) && $option.option_default == 1}checked="checked"{/if} value="1"></td>
                  <td style="text-align:center"><input type="checkbox" name="option_update[{$option.assign_id}][option_negative]" {if isset($option.option_negative) && $option.option_negative == 1}checked="checked"{/if} value="1"></td>
                  <td><span class="editable number-right" name="option_update[{$option.assign_id}][option_price]" title="{$LANG.common.click_edit}">{$option.option_price}</span></td>
                  <td style="text-align:center"><input type="checkbox" name="option_update[{$option.assign_id}][absolute_price]" {if isset($option.absolute_price) && $option.absolute_price == 1}checked="checked"{/if} value="1"></td>
                  <td><span class="editable number" name="option_update[{$option.assign_id}][option_weight]" title="{$LANG.common.click_edit}">{$option.option_weight}</span></td>
                  <td style="text-align:center">
                     {if !$option.set_member_id}<a href="#" name="option_remove" class="remove" rel="{$option.assign_id}" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>{else}<i class="fa fa-trash disabled" title="{$LANG.catalogue.delete_option_disabled}"></i>{/if}
                     <input type="hidden" id="data_{$option.assign_id}" value="{$option.data}">
                  </td>
               </tr>
               {else}
               <tr id="option_member_{$option.set_member_id}">
                  <td style="text-align:center"><input type="hidden" id="enable_member_{$option.set_member_id}" name="option_create[{$option.set_member_id}][set_enabled]" value="{$option.set_enabled}" class="toggle"></td>
                  <td style="text-align:center"><input type="hidden" id="matrix_include_{$option.set_member_id}" name="option_create[{$option.set_member_id}][matrix_include]" value="0" class="toggle"></td>
                  <td>{$option.display}</td>
                  <td>{$option.set_name}</td>
                  <td style="text-align:center"><input type="checkbox" name="option_create[{$option.set_member_id}][option_default]" {if isset($option.option_default) && $option.option_default == 1}checked="checked"{/if}  value="1"></td>
                  <td style="text-align:center"><input type="checkbox" name="option_create[{$option.set_member_id}][option_negative]" {if isset($option.option_negative) && $option.option_negative == 1}checked="checked"{/if}  value="1"></td>
                  <td><span class="editable number-right" name="option_create[{$option.set_member_id}][option_price]" title="{$LANG.common.click_edit}">{$option.option_price}</span></td>
                  <td style="text-align:center"><input type="checkbox" name="option_create[{$option.set_member_id}][absolute_price]" {if isset($option.absolute_price) && $option.absolute_price == 1}checked="checked"{/if}  value="1"></td>
                  <td><span class="editable number" name="option_create[{$option.set_member_id}][option_weight]" title="{$LANG.common.click_edit}">{$option.option_weight}</span></td>
                  <td style="text-align:center"><i class="fa fa-trash disabled" title="{$LANG.catalogue.delete_option_disabled}"></i></td>
               </tr>
               {/if}
               {/foreach}
               {/foreach}
            </tbody>
            <tfoot>
               <tr>
                  <td colspan="2">
                  {$LANG.catalogue.title_option_add}:
                  <input type="hidden" id="opt_set_enabled" value="1" rel="set_enabled" class="data">
                  <input type="hidden" id="opt_matrix_include" value="0" rel="matrix_include" class="data">
                  </td>
                  <td>
                     <select id="opt_mid" class="textbox data">
                        <option value="">{$LANG.form.please_select}</option>
                        {if isset($OPTIONS_SELECT)}
                        {foreach from=$OPTIONS_SELECT item=group}
                        {if isset($group.members)}
                        <optgroup id="{$group.option_id}" label="{$group.option_name}">
                           {foreach from=$group.members item=member}
                           <option value="{$member.value_id}">{$member.value_name}</option>
                           {/foreach}
                        </optgroup>
                        {else}
                        <option value="{$group.option_id}">{$group.option_name}</option>
                        {/if}
                        {/foreach}
                        {/if}
                     </select>
                  </td>
                  <td></td>
                  <td style="text-align:center"><input type="checkbox" id="opt_default" rel="default" class="checkbox data"></td>
                  <td style="text-align:center"><input type="checkbox" id="opt_negative" rel="negative" class="checkbox data"></td>
                  <td><input type="text" id="opt_price" rel="price" class="textbox number data"></td>
                  <td style="text-align:center"><input type="checkbox" id="opt_absolute_price" rel="absolute_price" class="checkbox data"></td>
                  <td><input type="text" id="opt_weight" rel="weight" class="textbox number data"></td>
                  <td style="text-align:center"><a href="#" onclick="optionAdd('option_template', 'options_added'); return false;"><i class="fa fa-plus-circle" title="{$LANG.common.add}"></i></a></td>
               </tr>
               <tr class="inline-source">
                  <td class="set_enabled"><input type="hidden" rel=""></td>
                  <td class="matrix_include"><input type="hidden" rel=""></td>
                  <td class="name"><input type="hidden" rel=""></td>
                  <td class="default"><input type="hidden" rel=""></td>
                  <td class="negative"><input type="hidden" rel=""></td>
                  <td class="price"><input type="hidden" rel=""></td>
                  <td class="absolute_price"><input type="hidden" rel=""></td>
                  <td class="weight"><input type="hidden" rel=""></td>
                  <td style="text-align:center"><a href="#" class="remove dynamic"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
               </tr>
               <tr id="option_template" class="dynamic">
                  <td style="text-align:center" class="set_enabled"><input type="checkbox" class="set_enabled" name="option_add[set_enabled][]" value="1"></td>
                  <td style="text-align:center" class="matrix_include"><input type="checkbox" name="option_add[matrix_include][]" value="1"></td>
                  <td class="name"><input type="hidden" name="option_add[value][]" value="" disabled="disabled"></td>
                  <td class="set_name">{$LANG.common.none}</td>
                  <td class="default" align="center"><input type="checkbox" name="option_add[default][]" value="1" disabled="disabled"></td>
                  <td class="negative" align="center"><input type="checkbox" name="option_add[negative][]" value="1" disabled="disabled"></td>
                  <td class="price"><input type="hidden" name="option_add[price][]" value="" disabled="disabled"></td>
                  <td class="absolute_price" align="center"><input type="checkbox" name="option_add[absolute_price][]" value="1" disabled="disabled"></td>
                  <td class="weight"><input type="hidden" name="option_add[weight][]" value="" disabled="disabled"></td>
                  <td style="text-align:center"><a href="#" class="remove" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
               </tr>
            </tfoot>
         </table>
         <script language="text/javascript">
            var optionJSON = {$OPTIONS_JSON};
         </script>
         <div>* {$LANG.catalogue.absolute_price_explained}</div>
      </fieldset>
      {if isset($OPTION_SETS)}
      <fieldset>
         <legend>{$LANG.catalogue.title_option_sets}</legend>
         {foreach from=$OPTION_SETS_ENABLED item=set}
         <div>
            <span class="actions">
            <a href="#" name="set_remove" class="remove" rel="{$set.set_product_id}" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
            </span>
            {$set.set_name}
         </div>
         {foreachelse}
         {$LANG.catalogue.no_option_sets_assigned}
         {/foreach}
         <div class="list-footer">
            <label for="">{$LANG.catalogue.set_assign}:</label>
            <span>
               <select id="" name="set_assign">
                  <option value="">{$LANG.form.please_select}</option>
                  {foreach from=$OPTION_SETS item=option_set}
                  <option value="{$option_set.set_id}">{$option_set.set_name}</option>
                  {/foreach}
               </select>
               <input type="submit" class="tiny" value="{$LANG.common.add}">
            </span>
         </div>
      </fieldset>
      {/if}
      {if $OPTIONS_MATRIX}
      <h3>{$LANG.catalogue.title_product_options_matrix}</h3>
      <table>
         <thead>
            <tr>
               <th>{$LANG.common.combination}</th>
               <th>{$LANG.catalogue.stock_level_use}</th>
               <th>{$LANG.catalogue.stock_level}</th>
               <th>{$LANG.catalogue.product_code}</th>
               <th>{$LANG.catalogue.product_upc}</th>
               <th>{$LANG.catalogue.product_ean}</th>
               <th>{$LANG.catalogue.product_jan}</th>
               <th>{$LANG.catalogue.product_isbn}</th>
               <th>{$LANG.catalogue.product_gtin}</th>
               <th>{$LANG.catalogue.restock_note}</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$OPTIONS_MATRIX.all_possible item=row}
            <tr>
               <td>{$row.options_values}</td>
               <td style="text-align:center"><input type="hidden" id="use_stock_{$row.options_identifier}" name="option_matrix[{$row.options_identifier}][use_stock]" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.use_stock}" class="toggle"></td>
               <td><input type="text" name="option_matrix[{$row.options_identifier}][stock_level]" class="textbox number" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.stock_level}"></td>
               <td><input type="text" name="option_matrix[{$row.options_identifier}][product_code]" class="textbox number" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.product_code}"></td>
               <td><input type="text" name="option_matrix[{$row.options_identifier}][upc]" class="textbox number" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.upc}"></td>
               <td><input type="text" name="option_matrix[{$row.options_identifier}][ean]" class="textbox number" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.ean}"></td>
               <td><input type="text" name="option_matrix[{$row.options_identifier}][jan]" class="textbox number" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.jan}"></td>
               <td><input type="text" name="option_matrix[{$row.options_identifier}][isbn]" class="textbox number" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.isbn}"></td>
               <td><input type="text" name="option_matrix[{$row.options_identifier}][gtin]" class="textbox number" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.gtin}"></td>
               <td><input type="text" name="option_matrix[{$row.options_identifier}][restock_note]" class="textbox number" value="{$OPTIONS_MATRIX.existing.{$row.options_identifier}.restock_note}" maxlength="255" ></td>
            </tr>
            {foreachelse} 
            <tr>
               <td colspan="9" align="center">{$LANG.form.none}</td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      {/if}
   </div>
   <div id="image" class="tab_content">
      <h3>{$LANG.settings.title_images}</h3>
      <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/star_fmcheckbox.png" alt="{$LANG.catalogue.image_main}"> - {$LANG.catalogue.image_main}
      <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/1_fmcheckbox.png" alt="{$LANG.catalogue.image_included}"> - {$LANG.catalogue.image_included}
      <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/0_fmcheckbox.png" alt="{$LANG.catalogue.image_excluded}"> - {$LANG.catalogue.image_excluded}
      <p><input type="text" name="fm-search-term" id="fm-search-term" placeholder="{$LANG.filemanager.search_location}..."><button type="button" class="button tiny" id="fm-search-button" data-mode="images" data-action="location">{$LANG.common.go}</button></p>
      <div class="fm-container">
         <div class="loading">{$LANG.common.loading} <i class="fa fa-spinner fa-spin fa-fw"></i></div>
         <div id="imageset" rel="1" class="fm-filelist"></div>
         <div class="master_image">
            <span>{$LANG.catalogue.image_main}</span><br><br>
            <div id="master_image_block">
            <img src="{$PRODUCT.master_image}" id="master_image_preview"><div id="preview_image"><img src="{$PRODUCT.master_image}"></div>
            </div>
            {if $GALLERY_JSON}
            <div id="gallery_json">
               <p>{$LANG.catalogue.other_inc_images}</p>
               <ul>{foreach $GALLERY_JSON|json_decode:true as $gallery_image}
                  {if $gallery_image@index > 0}
                  <li id="gallery_imageset_{$gallery_image.file_id}">
                     <img src="images/source/{$gallery_image.filepath}{$gallery_image.filename}" title="{$gallery_image.filepath}{$gallery_image.filename}" />
                  </li>
                  {/if}
                  {/foreach}
               </ul>
               </div>
            {/if}
         </div>
         
      </div>
      <div class="dropzone">
         <div class="dz-default dz-message"><span>{$LANG.filemanager.file_upload_note}</span></div>
      </div>
      <div id="dropzone_url" style="display: none;">?_g=filemanager&amp;product_id={$PRODUCT.product_id}</div>
      <div id="val_product_id" style="display: none;">{$PRODUCT.product_id}</div>

      <div id="val_lang_go" style="display: none;">{$LANG.common.go}</div>
      <div id="val_lang_preview" style="display: none;">{$LANG.common.preview}</div>
      <div id="val_lang_main_image" style="display: none;">{$LANG.catalogue.image_main}</div>
      <div id="val_lang_show_assigned" style="display: none;">{$LANG.filemanager.show_assigned}</div>
      <div id="val_lang_show_all" style="display: none;">{$LANG.filemanager.show_all}</div>
      <div id="val_lang_folder_create" style="display: none;">{$LANG.filemanager.folder_create}:</div>
      <div id="val_lang_refresh_files" style="display: none;">{$LANG.filemanager.refresh_files}</div>
      <div id="val_lang_upload_destination" style="display: none;">{$LANG.filemanager.upload_destination}:</div>
      <div id="val_lang_enable" style="display: none;">{$LANG.common.enable}</div>
      <div id="val_lang_disable" style="display: none;">{$LANG.common.disable}</div>
        
   </div>
   <div id="digital" class="tab_content">
      <h3>{$LANG.catalogue.title_digital_options}</h3>
      <div class="fm-container">
         <div class="loading">{$LANG.common.loading} <i class="fa fa-spinner fa-spin fa-fw"></i></div>
         <div id="download" rel="2" class="fm-filelist unique"></div>
      </div>
      <fieldset>
         <legend>{$LANG.catalogue.title_file_path_custom}</legend>
         <div id="digital_freetype"><label for="digital_path">{$LANG.catalogue.file_path}</label><span> <input name="digital_path" id="digital_path" class="textbox" type="text" value="{$PRODUCT.digital_path}" {$VAL_DIGITALDIR_EMPTY}></span></div>
         <div>{$LANG.catalogue.file_path_help}</div>
      </fieldset>
   </div>
   <div id="seo" class="tab_content">
      <h3>{$LANG.settings.tab_seo}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_seo_meta_data}</legend>
         <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input name="seo_meta_title" id="seo_meta_title" class="textbox" type="text" value="{$PRODUCT.seo_meta_title}"></span></div>
         <div><label for="seo_path">{$LANG.settings.seo_path} *</label><span><input name="seo_path" id="seo_path" class="textbox" type="text" value="{$PRODUCT.seo_path}"></span></div>
         <div><label for="seo_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="seo_meta_description" id="seo_meta_description" class="textbox">{$PRODUCT.seo_meta_description}</textarea></span></div>
      </fieldset>
      <p>* {$LANG.settings.seo_path_auto}</p>
      {include file='templates/element.redirects.php'}
   </div>
   <div id="reviews" class="tab_content">
      <h3>{$LANG.catalogue.customer_reviews}</h3>
      {if isset($CUSTOMER_REVIEWS)}
      {foreach from=$CUSTOMER_REVIEWS item=review}
      <div class="note">
         <span class="actions">
         <input type="hidden" name="review[{$review.id}]" id="review_{$review.id}" value="{$review.approved}" class="toggle">
         <a href="?_g=products&node=reviews&edit={$review.id}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
         <a href="{$review.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
         </span>
         <strong>{$review.title}</strong>
         <div>{$review.review}</div>
         <div class="details">
            <span style="float: right;">
            {section name=i start=1 loop=6 step=1}
            <input type="radio" class="rating" name="rating_{$review.id}" value="{$smarty.section.i.index}" disabled="disabled" {if $review.rating == $smarty.section.i.index}checked="checked"{/if}>
            {/section}
            </span>
            {$review.date} :: <a href="mailto:<{$review.email}">{$review.name}</a> (<a href="http://whois.domaintools.com/{$review.ip_address}" target="_blank">{$review.ip_address}</a>)
         </div>
      </div>
      {/foreach}
      {/if}
   </div>
   {if isset($DISPLAY_TRANSLATE)}
   <div id="translate" class="tab_content">
      <h3>{$LANG.translate.title_translations}</h3>
      <fieldset>
         {if isset($TRANSLATIONS)}
         {foreach from=$TRANSLATIONS item=translation}
         <div>
            <span class="actions">
            <a href="{$translation.edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
            <a href="{$translation.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
            </span>
            <input type="hidden" name="" id="">
            <a href="{$translation.edit}" title="{$translation.name}"><img src="language/flags/{$translation.language}.png" alt="{$translation.name}"></a>
            &nbsp; <a href="{$translation.edit}" title="{$translation.name}">{$translation.name}</a>
         </div>
         {/foreach}
         {else}
         <div>{$LANG.translate.trans_none}</div>
         {/if}
      </fieldset>
      <div><a href="{$TRANSLATE}">{$LANG.translate.trans_add}</a></div>
   </div>
   {/if}
   {if isset($PLUGIN_TABS)}
      {foreach from=$PLUGIN_TABS item=tab}
		{$tab}
      {/foreach}
   {/if}   
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="hidden" name="save" value="{$FORM_HASH}">
      <input type="hidden" name="previous-tab" id="previous-tab" value="">
      <input type="submit" value="{$LANG.common.save}"> <input type="submit" name="submit_cont" value="{$LANG.common.save_reload}">
   </div>
   {/if}
   {if isset($DISPLAY_TRANSLATE_FORM)}
   <div id="general" class="tab_content">
      <h3>{$LANG.common.general}</h3>
      <fieldset>
         <div><label for="trans_name">{$LANG.catalogue.product_name}</label><span><input type="text" name="translate[name]" id="trans_name" value="{$TRANS.name}" class="textbox"></span></div>
         <div><label for="trans_lang">{$LANG.common.language}</label><span><select name="translate[language]" id="trans_lang" class="textbox">
            {if isset($LANGUAGES)} {foreach from=$LANGUAGES item=language}<option value="{$language.code}"{$language.selected}>{$language.title}</option>{/foreach} {/if}
            </select></span>
         </div>
      </fieldset>
   </div>
   <div id="description" class="tab_content">
      <h3>{$LANG.translate.title_translate}</h3>
      <textarea name="translate[description]" class="textbox fck">{$TRANS.description|escape:"html"}</textarea>
      <br>
      <h3>{$LANG.translate.title_translate_short}</h3>
      <textarea name="translate[description_short]" class="textbox fck">{$TRANS.description_short|escape:"html"}</textarea>
   </div>
   <div id="seo" class="tab_content">
      <h3>{$LANG.settings.title_seo}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_seo_meta_data}</legend>
         <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input name="translate[seo_meta_title]" id="seo_meta_title" class="textbox" type="text" value="{$TRANS.seo_meta_title}"></span></div>
         <div><label for="seo_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="translate[seo_meta_description]" id="prod_seo_description" class="textbox">{$TRANS.seo_meta_description}</textarea></span></div>
      </fieldset>
   </div>
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="hidden" name="product_id" value="{$TRANS.product_id}">
      <input type="hidden" name="translation_id" value="{$TRANS.translation_id}">
      <input type="hidden" name="previous-tab" id="previous-tab" value="">
      <input type="submit" value="{$LANG.common.save}">
   </div>
   {/if}
   
</form>
