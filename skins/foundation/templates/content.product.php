{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
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
         <div class="small-7 columns">
            {if $PRODUCT.magnify}
            <a href="{$PRODUCT.source}" class="magnify" title="{$PRODUCT.name}" rel="">
            <img src="{$PRODUCT.medium}" alt="{$PRODUCT.name}" id="preview">
            </a>
            {else}
            <img src="{$PRODUCT.medium}" alt="{$PRODUCT.name}" id="preview">
            {/if}
            {if $GALLERY}
            <ul class="small-block-grid-5 marg-top" data-clearing>
               {foreach from=$GALLERY item=image}
               <li><a href="{$image.large}" id="image_{$image.id}" class="colorbox gallery" rel="gallery"><img class="th" src="{$image.gallery}" alt="{$LANG.catalogue.click_enlarge}"></a></li>
               {/foreach}
            </ul>
            <script type="text/javascript">
               var gallery_json  = {$GALLERY_JSON}
            </script>
            {/if}
         </div>
         <div class="small-5 columns">
            {if is_array($OPTIONS)}
            {foreach from=$OPTIONS item=option}
            {if $option.type == Catalogue::OPTION_SELECT}
            <div class="row">
               <div class="small-12 columns">
                  <label for="option_{$option.option_id}" class="return">{$option.option_name}{if $option.price} ({$option.symbol}{$option.price}){/if}{if $option.required} *{/if}</label>
                  {* If we only have one required option replace with hidden field *}
                  {if $option.required && count($option.values)==1}
                  {$option.values.0.value_name}{if $option.values.0.price} ({$option.values.0.symbol}{$option.values.0.price}){/if}
                  <input type="hidden" name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" value="{$option.values.0.assign_id}">
                  {else}
                  <select name="productOptions[{$option.option_id}]" id="option_{$option.option_id}" class="nomarg" {if $option.required}required{/if}>
                  <option value="">{$LANG.form.please_select}</option>
                  {foreach from=$option.values item=value}
                  <option value="{$value.assign_id}">{$value.value_name}{if $value.price} ({$value.symbol}{$value.price}){/if}</option>
                  {/foreach}
                  </select>
                  {/if}
               </div>
            </div>
            {else}
            <div class="row">
               <div class="small-12 columns">
                  <label for="option_{$option.option_id}" class="return">{$option.option_name}{if $option.price} ({$option.symbol}{$option.price}){/if}{if $option.required} *{/if}</label>
                  {if $option.type == Catalogue::OPTION_TEXTBOX}
                  <input type="text" name="productOptions[{$option.option_id}][{$OPT.assign_id}]" id="option_{$option.option_id}" {if $option.required}required{/if} >
                  {elseif $option.type == Catalogue::OPTION_TEXTAREA}
                  <textarea name="productOptions[{$option.option_id}][{$OPT.assign_id}]" id="option_{$option.option_id}" {if $option.required}required{/if}></textarea>
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
            {if ($CTRL_ALLOW_PURCHASE) && (!$CATALOGUE_MODE)}
            <h3>
               {if $PRODUCT.ctrl_sale}
               <span class="old_price">{$PRODUCT.price}</span> <span class="sale_price">{$PRODUCT.sale_price}</span>
               {else}
               {$PRODUCT.price}
               {/if}
            </h3>
            {if isset($PRODUCT.discounts)}
            <p>(<a href="#quantity_discounts">{$LANG.catalogue.bulk_discount}</a>)</p>
            {/if}
            <div class="row collapse">
               <div class="small-2 columns">
                  <input type="text" name="quantity" value="1" class="quantity required text-center">
                  <input type="hidden" name="add" value="{$PRODUCT.product_id}">
               </div>
               <div  class="small-10 columns">
                  <button type="submit" value="{$LANG.catalogue.add_to_basket}" class="button postfix">{$LANG.catalogue.add_to_basket}</button>
               </div>
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
      <dl class="tabs" data-tab data-options="deep_linking:true; scroll_to_content:false">
         {if !empty($PRODUCT.description)}
         <dd class="active"><a href="#product_info">{$LANG.catalogue.product_info}</a></dd>
         {/if}
         <dd><a href="#product_spec">{$LANG.common.specification}</a></dd>
         {if isset($PRODUCT.discounts)}
         <dd><a href="#quantity_discounts">{$LANG.catalogue.quantity_discounts}</a></dd>
         {/if}
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
      </div>
   </form>
   {if $SHARE}
   {foreach from=$SHARE item=html}
   {$html}
   {/foreach}
   {/if}
   <hr>
   {include file='templates/element.product_reviews.php'}
   {foreach from=$COMMENTS item=html}
   {$html}
   {/foreach}
   {if isset($TALKBACKS) && $TALKBACKS}
   <div>
      <h2>{$LANG.catalogue.trackbacks}</h2>
      {foreach from=$TRACKBACKS item=track}
      <p>
         <a href="{$track.url}" target="_blank">{$track.title}</a><br>
      <blockquote cite="{$track.url}">&quot;{$track.excerpt}&quot;</blockquote>
      </p>
      {/foreach}
      <h3>{$LANG.catalogue.trackback_url}</h3>
      <p>{$TRACKBACK_URL}</p>
   </div>
   {/if}
</div>
{else}
<p>{$LANG.catalogue.product_doesnt_exist}</p>
{/if}