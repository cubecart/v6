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
{if isset($PRODUCT) && $PRODUCT}
<div itemscope itemtype="http://schema.org/Product">
   <form action="{$VAL_SELF}" method="post" class="add_to_basket">
      <div class="row">
         <div class="small-12 columns">
            <h1 itemprop="name">{$PRODUCT.name}</h1>
         </div>
      </div>
      <div class="row">
         <div  class="small-1 medium-1 columns thinpad-right">
            {if $GALLERY}
            <ul class="clearing-thumbs small-block-grid-1" data-clearing>
               {foreach from=$GALLERY item=image}
               <li{if $image@total lt 2} style="display:none"{/if}><a href="{$image.source}" class="th"><img src="{$image.small}" data-image-swap="{$image.medium}" data-caption="{$PRODUCT.name}{if !empty($image.description)}: {/if}{$image.description}" class="image-gallery" alt="{$LANG.catalogue.click_enlarge}"></a></li>
               {/foreach}
            </ul>
            {/if}
         </div>
         <div class="small-4 medium-6 columns text-center nopad">            
            <a href="#" class="open-clearing" data-thumb-index="0"><img src="{$PRODUCT.medium}" alt="{$PRODUCT.name}" id="img-preview"></a>
         </div>
         <div class="small-7 medium-5 columns">
         {if is_array($OPTIONS)}
         {foreach from=$OPTIONS item=option}
            {if $option.type == Catalogue::OPTION_RADIO}
            <div class="row">
               <div class="small-12 columns">
                  {* If we only have one required option replace with hidden field *}
                  {if $option.required && count($option.values)===1}
                  <label for="option_{$option.option_id}" class="return">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}</label>
                  {$option.values.0.value_name}{if $option.values.0.price} {$option.values.0.symbol}{$option.values.0.price}{/if}
                  <input type="hidden" name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" value="{$option.values.0.assign_id}"{if !$CTRL_HIDE_PRICES} data-price="{$option.values.0.decimal_price}"{/if}>
                  {else}
                  <div class="pseudo-label">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}{if $option.required} ({$LANG.common.required}){/if}</div>
                  <span id="error_option_{$option.option_id}">
                     {foreach from=$option.values item=value name=options}
                     <div><input type="radio" name="productOptions[{$option.option_id}]" id="rad_option_{$value.assign_id}" value="{$value.assign_id}" class="nomarg{if $value.absolute_price == '1'} absolute{/if}"{if !$CTRL_HIDE_PRICES} data-price="{$value.decimal_price}"{/if}{if $smarty.foreach.options.first} rel="error_option_{$option.option_id}" {if $option.required}required{/if}{/if}>
                        <label for="rad_option_{$value.assign_id}" class="return">{$value.value_name}{if $value.price} {$value.symbol}{$value.price}{/if}</label>
                     </div>
                     {/foreach}
                  </span>
                  {/if}
               </div>
            </div>
            {elseif $option.type == Catalogue::OPTION_SELECT}
            <div class="row">
               <div class="small-12 columns">
                  {* If we only have one required option replace with hidden field *}
                  {if $option.required && count($option.values)===1}
                  <label for="option_{$option.option_id}" class="return">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}</label>
                  {$option.values.0.value_name}{if $option.values.0.price} {$option.values.0.symbol}{$option.values.0.price}{/if}
                  <input type="hidden" name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" value="{$option.values.0.assign_id}"{if !$CTRL_HIDE_PRICES} data-price="{$option.values.0.decimal_price}"{/if}>
                  {else}
                  <label for="option_{$option.option_id}" class="return">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}{if $option.required} ({$LANG.common.required}){/if}</label>
                  <select name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" class="nomarg" {if $option.required}required{/if}>
                  <option value="">{$LANG.form.please_select}</option>
                  {foreach from=$option.values item=value}
                  <option value="{$value.assign_id}"{if $value.absolute_price == '1'}class="absolute"{/if}{if !$CTRL_HIDE_PRICES} data-price="{$value.decimal_price}"{/if}>{$value.value_name}{if $value.price} {$value.symbol}{$value.price}{/if}</option>
                  {/foreach}
                  </select>
                  {/if}
               </div>
            </div>
            {else}
            <div class="row">
               <div class="small-12 columns">
                  <label for="option_{$option.option_id}" class="return">{if empty($option.option_description)}{$option.option_name}{else}{$option.option_description}{/if}{if $option.price} {$option.symbol}{$option.price}{/if}{if $option.required} ({$LANG.common.required}){/if}</label>
                  {if $option.type == Catalogue::OPTION_TEXTBOX}
                  <input type="text" name="productOptions[{$option.option_id}][{$option.assign_id}]" id="option_{$option.option_id}"{if $option.absolute_price == '1'} class="absolute"{/if}{if !$CTRL_HIDE_PRICES} data-price="{$option.decimal_price}"{/if} {if $option.required}required{/if}>
                  {elseif $option.type == Catalogue::OPTION_TEXTAREA}
                  <textarea name="productOptions[{$option.option_id}][{$option.assign_id}]" id="option_{$option.option_id}"{if $option.absolute_price == '1'} class="absolute"{/if}{if !$CTRL_HIDE_PRICES} data-price="{$option.decimal_price}"{/if} {if $option.required}required{/if}></textarea>
                  {/if}
               </div>
            </div>
            {/if}
         {/foreach}
         {/if}
            {if $PRODUCT.review_score && $CTRL_REVIEW}
            <p itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
               <meta itemprop="ratingValue" content="{$REVIEW_AVERAGE}">
               <meta itemprop="reviewCount" content="{$REVIEW_COUNT}">
               {for $i = 1; $i <= 5; $i++}
               {if $PRODUCT.review_score >= $i}
               <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star.png" alt="">
               {elseif $PRODUCT.review_score > ($i - 1) && $PRODUCT.review_score < $i}
               <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_half.png" alt="">
               {else}
               <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_off.png" alt="">
               {/if}
               {/for}
            <div>{$LANG_REVIEW_INFO}</div>
            </p>
            {/if}
            <h3>
               {if $PRODUCT.ctrl_sale}
               <span class="old_price" id="fbp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.full_base_price}"{/if}>{$PRODUCT.price}</span>
               <span class="sale_price" id="ptp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.price_to_pay}"{/if}>{$PRODUCT.sale_price}</span>
               {else}
               <span id="ptp"{if !$CTRL_HIDE_PRICES} data-price="{$PRODUCT.price_to_pay}"{/if}>{$PRODUCT.price}</span>
               {/if}
            </h3>
            {if isset($PRODUCT.discounts)}
            <p>(<a href="#quantity_discounts">{$LANG.catalogue.bulk_discount}</a>)</p>
            {/if}
            {if ($CTRL_ALLOW_PURCHASE) && (!$CATALOGUE_MODE)}
            <div class="row collapse">
               {if $PRODUCT.available <= 0}
               <div class="small-12 columns">
                  <input type="submit" value="{$LANG.common.unavailable}" class="button small postfix disabled expand marg-top" disabled>
               </div>
               {else}
               <div class="medium-2 columns show-for-medium-up">
                  <input type="text" name="quantity" value="1" maxlength="6" class="quantity required text-center">
                  <input type="hidden" name="add" value="{$PRODUCT.product_id}">
               </div>
               <div  class="small-12 medium-10 columns">
                  <button type="submit" value="{$LANG.catalogue.add_to_basket}" class="button postfix">{$LANG.catalogue.add_to_basket}</button>
               </div>
               {/if}
            </div>
            {else}
            {if $CTRL_HIDE_PRICES}
            <p class="buy_button"><strong>{$LANG.catalogue.login_to_view}</strong></p>
            {else if $CTRL_OUT_OF_STOCK}
            <p class="buy_button"><strong>{$LANG.catalogue.out_of_stock}</strong></p>
            {/if}
            {/if}
         </div>
      </div>
      <hr>
      <dl class="tabs" data-tab data-options="scroll_to_content:false">
         {if !empty($PRODUCT.description)}
         <dd class="active"><a href="#product_info">{$LANG.catalogue.product_info}</a></dd>
         {/if}
         <dd><a href="#product_spec">{$LANG.common.specification}</a></dd>
         {if isset($PRODUCT.discounts)}
         <dd><a href="#quantity_discounts">{$LANG.catalogue.quantity_discounts}</a></dd>
         {/if}
         {foreach from=$PRODUCT_TABS_TITLES item=product_tab_title}
            {$product_tab_title}
         {/foreach}
      </dl>
      <div class="tabs-content">
         {if !empty($PRODUCT.description)}
         <div class="content active" id="product_info" itemprop="description">
            {$PRODUCT.description}
         </div>
         {/if}
         <div class="content{if empty($PRODUCT.description)} active{/if}" id="product_spec">
            <table>
               <tbody>
                  <tr>
                     <td>{$LANG.catalogue.product_code}</td>
                     <td>{$PRODUCT.product_code}</td>
                  </tr>
                  {if $PRODUCT.manufacturer}
                  <tr>
                     <td>{$LANG.catalogue.manufacturer}</td>
                     <td>{$MANUFACTURER}</td>
                  </tr>
                  {/if}
                  {if $PRODUCT.stock_level}
                  <tr>
                     <td>{$LANG.catalogue.stock_level}</td>
                     <td>{$PRODUCT.stock_level}</td>
                  </tr>
                  {/if}
                  <tr>
                     <td>{$LANG.common.condition}</td>
                     <td>{$PRODUCT.condition}</td>
                  </tr>
                  {if $PRODUCT.product_weight > 0}
                  <tr>
                     <td>{$LANG.common.weight}</td>
                     <td>{$PRODUCT.product_weight}{$CONFIG.product_weight_unit|lower}</td>
                  </tr>
                  {/if}
               </tbody>
            </table>
         </div>
         {if isset($PRODUCT.discounts)}
         <div class="content" id="quantity_discounts">
            <p>{$LANG.catalogue.quantity_discounts_explained}</p>
            <br>
            <table>
               <thead>
                  <tr>
                     <th>{$LANG.common.quantity}</th>
                     <th>{$LANG.catalogue.price_per_unit}</th>
                  </tr>
               </thead>
               <tbody>
                  {foreach from=$PRODUCT.discounts item=discount}
                  <tr>
                     <td>{$discount.quantity}+</td>
                     <td>{$discount.price}</td>
                  </tr>
                  {/foreach}
               </tbody>
            </table>
         </div>
         {/if}
         {if isset($PRODUCT_TABS_CONTENTS)}
            {foreach from=$PRODUCT_TABS_CONTENTS item=product_tab_content}
               {$product_tab_content}
            {/foreach}
         {/if}
      </div>
   </form>
   {if $SHARE}
   <hr>
   {foreach from=$SHARE item=html}
   {$html}
   {/foreach}
   {/if}
   <hr>
   {include file='templates/element.product_reviews.php'}
   {foreach from=$COMMENTS item=html}
   {$html}
   {/foreach}
</div>
<div class="hide" id="validate_field_required">{$LANG.form.field_required}</div>
{else}
<p>{$LANG.catalogue.product_doesnt_exist}</p>
{/if}