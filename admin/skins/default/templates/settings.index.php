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
<form id="form-settings" action="{$PHP_SELF}" method="post" enctype="multipart/form-data">
   <div id="General" class="tab_content">
      <h3>{$LANG.common.general}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_geographical}</legend>
         <div><label for="store_name">{$LANG.settings.store_name}</label><span><input name="config[store_name]" id="store_name" type="text" class="textbox" value="{$CONFIG.store_name}"></span></div>
         <div><label for="store_address">{$LANG.address.line1}</label><span><textarea name="config[store_address]" id="store_address" class="textbox">{$CONFIG.store_address}</textarea></span></div>
         <div><label for="country-list">{$LANG.address.country}</label><span><select name="config[store_country]" id="country-list" class="textbox">
            {foreach from=$COUNTRIES item=country}<option value="{$country.numcode}"{$country.selected}>{$country.name}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="state-list">{$LANG.address.state}</label><span><input type="text" name="config[store_zone]" id="state-list" class="textbox" value="{$CONFIG.store_zone}"></span></div>
         <div><label for="store_postcode">{$LANG.address.postcode}</label><span><input name="config[store_postcode]" id="store_postcode" type="text" class="textbox" value="{$CONFIG.store_postcode}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_tax_lang}</legend>
         <div><label for="default_language">{$LANG.settings.default_language}</label><span><select name="config[default_language]" id="default_language" class="textbox">
            {foreach from=$LANGUAGES item=language}<option value="{$language.code}"{$language.selected}>{$language.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="default_currency">{$LANG.settings.default_currency}</label><span><select name="config[default_currency]" id="default_currency" class="textbox">
            {foreach from=$CURRENCIES item=currency}<option value="{$currency.code}"{$currency.selected}>{$currency.code} - {$currency.name}</option>{/foreach}
            </select></span>
         </div>
         {if !in_array($CONFIG.store_country, array(840, 124, 036))}
         <div><label for="tax_number">{$LANG.settings.tax_number}</label><span><input name="config[tax_number]" id="tax_number" type="text" class="textbox" value="{$CONFIG.tax_number}" placeholder="{$LANG.settings.tax_number_placeholder}"></span></div>
         {/if}
         <div><label for="basket_tax_by_delivery">{$LANG.settings.tax_customer_by}</label><span><select name="config[basket_tax_by_delivery]" id="basket_tax_by_delivery" class="textbox">
            {foreach from=$OPT_BASKET_TAX_BY_DELIVERY item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.social_accounts}</legend>
         <div><label for="facebook">Facebook</label><span><input name="config[facebook]" id="facebook" type="text" class="textbox" value="{$CONFIG.facebook}"></span></div>
         <div><label for="flickr">Flickr</label><span><input name="config[flickr]" id="flickr" type="text" class="textbox" value="{$CONFIG.flickr}"></span></div>
         <div><label for="google_plus">Google+</label><span><input name="config[google_plus]" id="google_plus" type="text" class="textbox" value="{$CONFIG.google_plus}"></span></div>
         <div><label for="instagram">Instagram</label><span><input name="config[instagram]" id="instagram" type="text" class="textbox" value="{$CONFIG.instagram}"></span></div>
         <div><label for="linkedin">LinkedIn</label><span><input name="config[linkedin]" id="linkedin" type="text" class="textbox" value="{$CONFIG.linkedin}"></span></div>
         <div><label for="pinterest">Pinterest</label><span><input name="config[pinterest]" id="pinterest" type="text" class="textbox" value="{$CONFIG.pinterest}"></span></div>
         <div><label for="twitter">Twitter</label><span><input name="config[twitter]" id="twitter" type="text" class="textbox" value="{$CONFIG.twitter}"></span></div>
         <div><label for="vimeo">Vimeo</label><span><input name="config[vimeo]" id="vimeo" type="text" class="textbox" value="{$CONFIG.vimeo}"></span></div>
         <div><label for="wordpress">WordPress</label><span><input name="config[wordpress]" id="wordpress" type="text" class="textbox" value="{$CONFIG.wordpress}"></span></div>
         <div><label for="youtube">YouTube</label><span><input name="config[youtube]" id="youtube" type="text" class="textbox" value="{$CONFIG.youtube}"></span></div>
      </fieldset>
   </div>
   <div id="Features" class="tab_content">
      <h3>{$LANG.settings.title_features}</h3>
      <fieldset>
         <legend>{$LANG.settings.google_analytics}</legend>
         <div><label for="google_analytics">{$LANG.settings.google_analytics_id}</label><span><input name="config[google_analytics]" id="google_analytics" type="text" class="textbox" value="{$CONFIG.google_analytics}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.navigation.nav_prod_reviews}</legend>
         <div><label for="enable_reviews">{$LANG.settings.enable_reviews}</label><span>
         <select name="config[enable_reviews]">
         <option value="0"{if $CONFIG.enable_reviews=='0'} selected="selected"{/if}>{$LANG.common.disabled}</option>
         <option value="1"{if $CONFIG.enable_reviews=='1'} selected="selected"{/if}>{$LANG.common.enabled}</option>
         <option value="2"{if $CONFIG.enable_reviews=='2'} selected="selected"{/if}>{$LANG.catalogue.reviews_no_gravatar}</option>
         </select>
         </span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_orders}</legend>
         <div><label for="basket_order_expire">{$LANG.settings.expire_pending}</label><span><input name="config[basket_order_expire]" id="basket_order_expire" class="textbox number" value="{$CONFIG.basket_order_expire}"> {$LANG.common.blank_to_disable}</span></div>
         <div><label for="oid_mode">{$LANG.orders.id_mode}</label><span><select name="config[oid_mode]" id="oid_mode" class="textbox preview_order" onchange="this.value == 'i' ? document.getElementById('i_options').style.display = 'block' :  document.getElementById('i_options').style.display = 'none';">
         {foreach from=$OPT_OID_MODE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span></div>
         <div{if $CONFIG.oid_mode!=="i"} style="display: none"{/if} id="i_options" class="stripe">
            <div><label for="oid_prefix">{$LANG.orders.oid_prefix}</label>
                  <span>
                        <input name="oid_prefix" id="oid_prefix" class="textbox number preview_order" value="{$CONFIG.oid_prefix}">
                  </span>
                  <br><small>{$LANG.orders.oid_prefix_desc} {$LANG.orders.date_specifiers}</small>
            </div>
            <div><label for="oid_postfix">{$LANG.orders.oid_postfix}</label>
                  <span>
                        <input name="oid_postfix" id="oid_postfix" class="textbox number preview_order" value="{$CONFIG.oid_postfix}">
                  </span>
                  <br><small>{$LANG.orders.oid_postfix_desc} {$LANG.orders.date_specifiers}</small>
            </div>
            <div><label for="oid_zeros">{$LANG.orders.oid_zeros}{if $LOCK_ORDER_NUMBER && $CONFIG.oid_zeros>0} ({$LANG.common.min}: {$CONFIG.oid_zeros}){/if}</label>
                  <span>
                        <input  type="number" name="oid_zeros" id="oid_zeros" class="textbox number preview_order" value="{$CONFIG.oid_zeros}"{if $LOCK_ORDER_NUMBER && $CONFIG.oid_zeros>0} min="{$CONFIG.oid_zeros}"{/if}>
                  </span>
                  <br><small>{$LANG.orders.oid_zeros_desc}</small>
            </div>
            <div><label for="oid_start">{$LANG.orders.oid_start}{if $LOCK_ORDER_NUMBER && $CONFIG.oid_start>0} ({$LANG.common.min}: {$CONFIG.oid_start}){/if}</label>
                  <span>
                        <input  type="number" name="oid_start" id="oid_start" class="textbox number preview_order" value="{$CONFIG.oid_start}"{if $LOCK_ORDER_NUMBER && $CONFIG.oid_start>0} min="{$CONFIG.oid_start}"{/if}>
                  </span>
                  <br><small>{$LANG.orders.oid_start_desc}</small>
            </div>
            <div><label for="oid_force">{$LANG.orders.oid_force}</label>
                  <span>
                  <input name="oid_force" id="oid_force" type="hidden" class="toggle" value="0">
                  </span>
                  <br><small>{$LANG.orders.oid_force_desc}</small>
            </div>
        </div>
      <div><label for="order_format_preview">&nbsp;</label><span>
            <button type="button" class="button tiny" id="order_format_preview" onclick="previewOrderFormat()">{$LANG.common.preview}</button>
            <script>
            function previewOrderFormat() {
                  var qstring = '';
                  $(".preview_order").each(function() {
                        qstring += '&'+this.id+'='+encodeURI(this.value);
                  });
                  $.colorbox({ href:'{$STORE_URL}/{$SKIN_VARS.admin_file}?_g=xml&function=previewOrderFormat'+qstring})
            }
            </script>
      </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_sales}</legend>
         <div><label for="catalogue_sale_mode">{$LANG.settings.sales_mode}</label><span><select name="config[catalogue_sale_mode]" id="catalogue_sale_mode" class="textbox">
            {foreach from=$OPT_CATALOGUE_SALE_MODE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="catalogue_sale_percentage">{$LANG.settings.sales_percentage}</label><span><input name="config[catalogue_sale_percentage]" id="catalogue_sale_percentage" type="text" class="textbox number" value="{$CONFIG.catalogue_sale_percentage}">%</span></div>
         <div><label for="catalogue_sale_items">{$LANG.settings.sales_items_count}</label><span><input name="config[catalogue_sale_items]" id="catalogue_sale_items" type="text" class="textbox number" value="{$CONFIG.catalogue_sale_items}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_flood}</legend>
         <div><label for="recaptcha">{$LANG.settings.recaptcha_enable}</label><span>
            <select name="config[recaptcha]" id="recaptcha" class="textbox">
            {foreach from=$OPT_RECAPTCHA item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select>
         </span></div>
         <div><label for="recaptcha_public_key">{$LANG.settings.recaptcha_public_key}</label><span><input name="config[recaptcha_public_key]" id="recaptcha_public_key" class="textbox" value="{$CONFIG.recaptcha_public_key}"></span></div>
         <div><label for="recaptcha_secret_key">{$LANG.settings.recaptcha_secret_key}</label><span><input name="config[recaptcha_secret_key]" id="recaptcha_secret_key" class="textbox" value="{$CONFIG.recaptcha_secret_key}"></span></div>
         <div class="clear important"><strong>{$LANG.settings.new_recaptcha_note}</strong>
            {if !$gr_compatibility.v2}
            <br><strong>{$LANG.settings.reCAPTCHA_v2_na}</strong>
            {/if}
            {if !$gr_compatibility.invisible}
            <br><strong>{$LANG.settings.reCAPTCHA_invisible_na}</strong>
            {/if}
         </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.navigation.nav_subscribers}</legend>
         <div><label for="exit_modal">{$LANG.settings.enable_exit_modal}</label><span><input name="config[exit_modal]" id="exit_modal" type="hidden" class="toggle" value="{$CONFIG.exit_modal}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.common.misc}</legend>
         <div><label for="admin_notify_status">{$LANG.settings.admin_order_status_notification}</label><span><select name="config[admin_notify_status]" id="admin_notify_status" class="textbox">
            {foreach from=$OPT_ADMIN_NOTIFY_STATUS item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="no_skip_processing_check">{$LANG.settings.no_skip_processing_check}</label><span><input name="config[no_skip_processing_check]" id="no_skip_processing_check" type="hidden" class="toggle" value="{$CONFIG.no_skip_processing_check}"></span></div>
         <div><label for="catalogue_hide_prices">{$LANG.settings.hide_prices}</label><span><input name="config[catalogue_hide_prices]" id="catalogue_hide_prices" type="hidden" class="toggle" value="{$CONFIG.catalogue_hide_prices}"></span>&nbsp;{$LANG.settings.no_admin_affect}</div>
         <div><label for="catalogue_mode">{$LANG.settings.catalogue_mode}</label><span><input name="config[catalogue_mode]" id="catalogue_mode" type="hidden" class="toggle" value="{$CONFIG.catalogue_mode}"></span></div>
         <div><label for="allow_no_shipping">{$LANG.settings.allow_no_shipping}</label><span><input name="config[allow_no_shipping]" id="allow_no_shipping" type="hidden" class="toggle" value="{$CONFIG.allow_no_shipping}"></span></div>
         <div><label for="disable_shipping_groups">{$LANG.settings.disable_shipping_groups}</label><span><input name="config[disable_shipping_groups]" id="disable_shipping_groups" type="hidden" class="toggle" value="{$CONFIG.disable_shipping_groups}"></span></div>
         <div><label for="shipping_defaults">{$LANG.settings.shipping_defaults}</label><span>
            <select name="config[shipping_defaults]">
            {foreach from=$OPT_SHIPPING_DEFAULTS item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select>
         </span></div>
         <div><label for="force_completed">{$LANG.settings.force_completed}</label><span><input name="config[force_completed]" id="force_completed" type="hidden" class="toggle" value="{$CONFIG.force_completed}"></span></div>
         <div><label for="disable_estimates">{$LANG.settings.disable_estimates}</label><span><input name="config[disable_estimates]" id="disable_estimates" type="hidden" class="toggle" value="{$CONFIG.disable_estimates}"></span></div>
      </fieldset>
   </div>
   <div id="Layout" class="tab_content">
      <h3>{$LANG.settings.title_layout}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_display}</legend>
          <div><label for="catalogue_products_per_page">{$LANG.settings.product_per_page} (<a href="#" onclick="$('#per_page_note').slideToggle()">Deprecated</a>)</label><span><input name="config[catalogue_products_per_page]" id="catalogue_products_per_page" class="textbox number" value="{$CONFIG.catalogue_products_per_page}"></span></div>
         <div><label for="default_product_sort">{$LANG.settings.default_product_sort}</label>
            <span>
            <select name="config[product_sort_column]" id="product_sort_column" class="textbox">
            {foreach from=$OPT_PRODUCT_SORT_COLUMN item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select>
            <select name="config[product_sort_direction]" id="product_sort_direction" class="textbox">
            {foreach from=$OPT_PRODUCT_SORT_DIRECTION item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select>
            </span>
         </div>
         <div><label for="catalogue_show_empty">{$LANG.settings.category_display_empty}</label><span><select name="config[catalogue_show_empty]" id="catalogue_show_empty" class="textbox">
            {foreach from=$OPT_CATALOGUE_SHOW_EMPTY item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="product_precis">{$LANG.settings.product_precis}</label><span><input name="config[product_precis]" id="product_precis" class="textbox number" value="{$CONFIG.product_precis}"></span></div>
         <div><label for="catalogue_expand_tree">{$LANG.settings.category_expand_tree}</label><span><select name="config[catalogue_expand_tree]" id="catalogue_expand_tree" class="textbox">
         {foreach from=$OPT_CATALOGUE_EXPAND_TREE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="basket_jump_to">{$LANG.settings.basket_jump_to}</label><span><select name="config[basket_jump_to]" id="basket_jump_to" class="textbox">
            {foreach from=$OPT_BASKET_JUMP_TO item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="disable_checkout_terms">{$LANG.settings.disable_checkout_terms}</label><span><input name="config[disable_checkout_terms]" id="disable_checkout_terms" type="hidden" class="toggle" value="{$CONFIG.disable_checkout_terms}"></span></div>
         <div><label for="default_rss_feed">{$LANG.settings.default_rss}</label><span><input name="config[default_rss_feed]" id="default_rss_feed" class="textbox" value="{$CONFIG.default_rss_feed}"></span></div>
      </fieldset>
      <div style="display:none" id="per_page_note">
      <h3>{$LANG.settings.product_per_page}</h3>
      <p>This setting has been replaced with the layout > products > perpage section of the skins config.xml file wich includes page splits. This setting is ignored for skins that have this block of XML. Please edit the skins config.xml file instead.</p> 
      <p>Example:</p>
<pre>&lt;layout&gt;
   &lt;products&gt;
      &lt;perpage amount="6" /&gt;
         &lt;perpage default="true" amount="12" /&gt;
         &lt;perpage amount="24" /&gt;
         &lt;perpage amount="48" /&gt;
      &lt;perpage amount="96" /&gt;
   &lt;/products&gt;
&lt;/layout&gt;</pre>
</div>
      <fieldset>
         <legend>{$LANG.settings.title_popular_latest}</legend>
         <div><label for="catalogue_latest_products">{$LANG.settings.product_latest}</label><span><select name="config[catalogue_latest_products]" id="catalogue_latest_products" class="textbox">
            {foreach from=$OPT_CATALOGUE_LATEST_PRODUCTS item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="catalogue_latest_products_count">{$LANG.settings.product_latest_number}</label><span><input name="config[catalogue_latest_products_count]" id="catalogue_latest_products_count" type="text" class="textbox number" value="{$CONFIG.catalogue_latest_products_count}"></span></div>
         <div><label for="catalogue_popular_products_count">{$LANG.settings.product_popular}</label><span><input name="config[catalogue_popular_products_count]" id="catalogue_popular_products_count" class="textbox number" value="{$CONFIG.catalogue_popular_products_count}"></span></div>
         <div><label for="catalogue_popular_products_source">{$LANG.settings.product_popular_source}</label><span><select name="config[catalogue_popular_products_source]" id="catalogue_popular_products_source" class="textbox">
            {foreach from=$OPT_CATALOGUE_POPULAR_PRODUCTS_SOURCE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_skins}</legend>
         <div><label for="skin_folder">{$LANG.settings.skins_default_front}</label><span>
            <input type="hidden" class="default-style" value="{$CONFIG.skin_style}">
            <select name="config[skin_folder]" id="skin_folder" class="textbox select-skin no-drop">
            {foreach from=$SKINS item=skin}<option value="{$skin.name}" title="{$skin.description}"{$skin.selected}>{$skin.display}</option>{/foreach}
            </select>
            <select name="config[skin_style]" id="skin_style" class="textbox select-style"></select>
            </span>
         </div>
         <div><label for="admin_skin">{$LANG.settings.skins_default_admin}</label><span>
            <select name="config[admin_skin]" id="admin_skin" class="textbox">
            {foreach from=$SKINS_ADMIN item=skin}<option value="{$skin.name}" {$skin.selected}>{$skin.name}</option>{/foreach}
            </select>
            </span>
         </div>
         <div><label for="skin_change">{$LANG.settings.skins_allow_change}</label><span><select name="config[skin_change]" id="skin_change" class="textbox">
            {foreach from=$OPT_SKIN_CHANGE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         {if $SKINS_MOBILE}
         <div><label for="skin_folder_mobile">{$LANG.settings.skins_mobile_default_front}</label><span>
            <input type="hidden" class="default-style-mobile" value="{$CONFIG.skin_style_mobile}">
            <select name="config[skin_folder_mobile]" id="skin_folder_mobile" class="textbox select-skin-mobile no-drop">
            {foreach from=$SKINS_MOBILE item=skin}<option value="{$skin.name}" title="{$skin.description}"{$skin.selected}>{$skin.display}</option>{/foreach}
            </select>
            <select name="config[skin_style_mobile]" id="skin_style_mobile" class="textbox select-style-mobile"{$MOBILE_DISABLED}></select> 
            </span>
         </div>
         <div><label for="disable_mobile_skin">{$LANG.settings.disable_mobile_skin}</label><span><input name="config[disable_mobile_skin]" id="disable_mobile_skin" type="hidden" class="toggle" value="{$CONFIG.disable_mobile_skin}"></span></div>
         {else}
         	<input name="config[disable_mobile_skin]" id="disable_mobile_skin" type="hidden" value="1">
         {/if}
      </fieldset>
   </div>
   <div id="Stock" class="tab_content">
      <h3>{$LANG.settings.title_stock}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_digital}</legend>
         <div><label for="download_expire">{$LANG.settings.digital_expiry}</label><span><input name="config[download_expire]" id="download_expire" type="text" class="textbox number" value="{$CONFIG.download_expire}"> {$LANG.common.blank_to_disable}</span></div>
         <div><label for="download_update_existing">{$LANG.settings.download_update_existing}</label><span><input name="download_update_existing" id="download_update_existing" type="hidden" class="toggle" value="0"><input name="download_expire_old" type="hidden" value="{$CONFIG.download_expire}"></span></div>
         <div><label for="download_count">{$LANG.settings.digital_attempts}</label><span><input name="config[download_count]" id="download_count" type="text" class="textbox number" value="{$CONFIG.download_count}"> {$LANG.common.blank_to_disable}</span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_stock_general}</legend>
         <div><label for="stock_level">{$LANG.settings.stock_show}</label><span><input name="config[stock_level]" id="stock_level" type="hidden" class="toggle" value="{$CONFIG.stock_level}"></span></div>
         <div><label for="basket_out_of_stock_purchase">{$LANG.settings.stock_allow_oos}</label><span><input name="config[basket_out_of_stock_purchase]" id="basket_out_of_stock_purchase" type="hidden" class="toggle" value="{$CONFIG.basket_out_of_stock_purchase}"></span></div>
         <div><label for="stock_warn_type">{$LANG.settings.stock_warning_method}</label><span><select name="config[stock_warn_type]" id="stock_warn_type" class="textbox">
            {foreach from=$OPT_STOCK_WARN_TYPE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="stock_warn_level">{$LANG.settings.stock_warning_level}</label><span><input name="config[stock_warn_level]" id="stock_warn_level" type="text" class="textbox number" value="{$CONFIG.stock_warn_level}"></span></div>
         <div><label for="product_weight_unit">{$LANG.settings.weight_unit}</label><span><select name="config[product_weight_unit]" id="product_weight_unit" class="textbox">
            {foreach from=$OPT_PRODUCT_WEIGHT_UNIT item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="show_basket_weight">{$LANG.settings.show_basket_weight}</label><span><input name="config[show_basket_weight]" id="show_basket_weight" type="hidden" class="toggle" value="{$CONFIG.show_basket_weight}"></span></div>
         <div><label for="basket_allow_non_invoice_address">{$LANG.settings.dispatch_to_non_invoice}</label><span><input name="config[basket_allow_non_invoice_address]" id="basket_allow_non_invoice_address" type="hidden" class="toggle" value="{$CONFIG.basket_allow_non_invoice_address}"></span></div>
         <div><label for="stock_change_time">{$LANG.settings.stock_reduce}</label><span><select name="config[stock_change_time]" id="stock_change_time" class="textbox">
            {foreach from=$OPT_STOCK_CHANGE_TIME item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="hide_out_of_stock">{$LANG.settings.title_hide_out_of_stock}</label><span><input name="config[hide_out_of_stock]" id="hide_out_of_stock" type="hidden" class="toggle" value="{$CONFIG.hide_out_of_stock}"></span>&nbsp;{$LANG.settings.no_admin_affect}</div>
         <div><label for="update_main_stock">{$LANG.settings.update_main_stock}</label><span><input name="config[update_main_stock]" id="update_main_stock" type="hidden" class="toggle" value="{$CONFIG.update_main_stock}"></span>&nbsp;{$LANG.settings.matrix_in_use}</div>
      </fieldset>
   </div>
   <div id="Search_Engines" class="tab_content">
      <h3>{$LANG.settings.title_seo}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_seo_global_meta_data}</legend>
         <div><label for="store_title">{$LANG.settings.seo_browser_title}</label><span><input name="config[store_title]" id="store_title" type="text" class="textbox" value="{$CONFIG.store_title}"></span></div>
         <div><label for="store_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="config[store_meta_description]" id="store_meta_description" class="textbox">{$CONFIG.store_meta_description}</textarea></span></div>
         <div><label for="seo_add_cats">{$LANG.settings.seo_add_cats}</label><span>
         <select name="config[seo_add_cats]" id="seo_add_cats" class="textbox">
            {foreach from=$OPT_SEO_ADD_CATS item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span></div>
         <div><label for="seo_cat_add_cats">{$LANG.settings.seo_cat_add_cats}</label><span>
         <select name="config[seo_cat_add_cats]" id="seo_cat_add_cats" class="textbox">
            {foreach from=$OPT_SEO_CAT_ADD_CATS item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_seo_meta_behaviour}</legend>
         <div><label for="seo_metadata">{$LANG.settings.seo_meta_behaviour}</label><span><select name="config[seo_metadata]" id="seo_metadata" class="textbox">
            {foreach from=$OPT_SEO_METADATA item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
      </fieldset>
   </div>
   <div id="SSL" class="tab_content">
      <h3>{$LANG.settings.title_ssl}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_ssl}</legend>
         <div><label for="ssl">{$LANG.settings.ssl_enable}</label><span><select name="config[ssl]" id="ssl" class="textbox">
            {foreach from=$OPT_SSL item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="standard_url">{$LANG.settings.standard_url}</label><span><input name="config[standard_url]" id="standard_url" type="text" class="textbox" value="{$CONFIG.standard_url}"> {$LANG.common.eg} http://www.example.com/store</span></div>
         <div><label for="cookie_domain">{$LANG.settings.cookie_domain}</label><span><input name="config[cookie_domain]" id="cookie_domain" type="text" class="textbox" value="{$CONFIG.cookie_domain}"> {$LANG.common.eg} .example.com</span></div>
      </fieldset>
   </div>
   <div id="Offline" class="tab_content">
      <h3>{$LANG.settings.title_offline}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_offline}</legend>
         <div><label for="offline">{$LANG.settings.offline_enable}</label><span><select name="config[offline]" id="offline" class="textbox">
            {foreach from=$OPT_OFFLINE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.offline_message}</legend>
         <textarea name="config[offline_content]" id="offline_content" class="textbox fck fck-full">{$CONFIG.offline_content|escape:"html"}</textarea>
      </fieldset>
   </div>
   <div id="Logos" class="tab_content">
      <h3>{$LANG.settings.title_logo}</h3>
      {if isset($LOGOS)}
      <fieldset>
         <table width="100%">
         <thead>
         <tr>
         <td>
         {$LANG.common.status}
         </td>
         <td>{$LANG.common.logo}</td>
         <td>{$LANG.module.scope}</td>
         <td>{$LANG.form.action}</td>
         </tr>
         </thead>
         <tbody>
         {foreach from=$LOGOS item=logo}
         <tr>
            <td>
            <input type="hidden" name="logo[{$logo.logo_id}][status]" id="logo_{$logo.logo_id}_status" value="{$logo.status}" class="toggle">
            </td>
            <td>
           
            <a href="images/logos/{$logo.filename}" target="_blank" class="colorbox"><img src="images/logos/{$logo.filename}" alt="{$logo.filename}" height="50"></a>
            </td>
            <td>
               <input type="hidden" class="default-style" value="{$logo.style}">
               <select id="logo_{$logo.logo_id}_skin" name="logo[{$logo.logo_id}][skin]" class="textbox select-skin">
                  <optgroup label="Skins">
                     <option value="">{$LANG.settings.logo_all_skins}</option>
                     {foreach from=$SKINS_ALL item=skin}
                     {if isset($skin.other_optgroup) && $skin.other_optgroup}
                  </optgroup>
                  <optgroup label="{$LANG.common.other}">
                     {/if}
                     <option value="{$skin.name}" {if ($skin.name == $logo.skin)} selected="selected"{/if}>{$skin.display}</option>
                     {/foreach}
                  </optgroup>
               </select>
               <select id="logo_{$logo.logo_id}_style" name="logo[{$logo.logo_id}][style]" class="textbox select-style">
                  <option value="">{$LANG.settings.logo_all_styles}</option>
               </select>
               
            </td>
            <td>
<a href="{$logo.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
            </td>
         </tr>
         {/foreach}
         </tbody>
         </table>
      </fieldset>
      {/if}
      <fieldset>
         <legend>{$LANG.settings.title_logo_upload}</legend>
         <div><input type="file" name="logo" class="multiple"></div>
      </fieldset>
   </div>
   <div id="Advanced_Settings" class="tab_content">
      <h3>{$LANG.settings.title_advanced}</h3>
      <fieldset>
         <legend>{$LANG.common.email}</legend>
         <div><label for="email_method">{$LANG.settings.email_method}</label><span><select name="config[email_method]" id="email_method" class="textbox preview_email">
            {foreach from=$OPT_EMAIL_METHOD item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="email_name">{$LANG.settings.email_sender_name}</label><span><input name="config[email_name]" id="email_name" type="text" class="textbox preview_email" value="{$CONFIG.email_name}"></span></div>
         <div><label for="email_address">{$LANG.settings.email_sender_address}</label><span><input name="config[email_address]" id="email_address" type="text" class="textbox preview_email" value="{$CONFIG.email_address}"></span></div>
         <div id="smtp_settings" class="stripe stripe_reverse" {if $CONFIG.email_method=='mail' || $CONFIG.email_method==''} style="display: none"{/if}>
            <div><label for="email_smtp_host">{$LANG.settings.smtp_host}</label><span><input name="config[email_smtp_host]" id="email_smtp_host" type="text" class="textbox preview_email" value="{$CONFIG.email_smtp_host}"></span></div>
            <div><label for="email_smtp_port">{$LANG.settings.smtp_port}</label><span><input name="config[email_smtp_port]" id="email_smtp_port" type="text" class="textbox number preview_email" value="{$CONFIG.email_smtp_port}"></span></div>
            <div><label for="email_smtp">{$LANG.settings.smtp_auth}</label><span><select name="config[email_smtp]" id="email_smtp" class="textbox preview_email" autocomplete="off">
                  {foreach from=$OPT_EMAIL_SMTP item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
                  </select></span>
            </div>
            <div><label for="email_smtp_user">{$LANG.settings.smtp_user}</label><span><input name="config[email_smtp_user]" id="email_smtp_user" type="text" class="textbox preview_email" value="{$CONFIG.email_smtp_user}" autocomplete="off"></span></div>
            <div><label for="email_smtp_password">{$LANG.settings.smtp_pass}</label><span><input name="config[email_smtp_password]" id="email_smtp_password" type="password" class="textbox preview_email" value="{$CONFIG.email_smtp_password|escape:'html'}" autocomplete="off"></span></div>
         </div>
         <div class="nostripe"><label for="smtp_test_url">&nbsp;</label><span>
         <button type="button" class="button tiny" id="smtp_test" onclick="previewEmailSettings()">{$LANG.common.test}</button></span></div>
         {literal}
         <script>
            function previewEmailSettings() {
               var requestData = {};
               $(".preview_email").each(function() {
                  requestData[this.id] = this.value;
               });
               requestData['token'] = {/literal}'{$SESSION_TOKEN}'{literal};
               $.ajax({
                  type: 'post',
                  url: "?_g=xml&function=SMTPTest",
                  data: requestData,
                  dataType: "text",
                  success: function(responseData) {
                     $.colorbox({html:responseData})
                  }
               });
            }
         </script>
         {/literal}
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_performance}</legend>
         <div><label for="debug">{$LANG.settings.debug_enable}</label><span><select name="config[debug]" id="debug" class="textbox">
            {foreach from=$OPT_DEBUG item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="debug">{$LANG.settings.debug_ip_addresses}</label><span><input name="config[debug_ip_addresses]" id="debug_ip_addresses" type="text" class="textbox" value="{$CONFIG.debug_ip_addresses}"></span></div>
         <div><label for="cache">{$LANG.settings.cache_enable} (<a href="https://support.cubecart.com/hc/en-gb/articles/360003831737-How-do-I-enable-APC-Memcached-Redis-or-xCache-" target="_blank">{$CACHE_METHOD}</a>)</label><span><select name="config[cache]" id="cache" class="textbox">
     {foreach from=$OPT_CACHE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
   </select></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_proxy}</legend>
         <div><label for="proxy">{$LANG.settings.proxy_enable}</label><span><select name="config[proxy]" id="proxy" class="textbox">
            {foreach from=$OPT_PROXY item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="proxy_host">{$LANG.settings.proxy_host}</label><span><input name="config[proxy_host]" id="proxy_host" type="text" class="textbox" value="{$CONFIG.proxy_host}"></span></div>
         <div><label for="proxy_port">{$LANG.settings.proxy_port}</label><span><input name="config[proxy_port]" id="proxy_port" type="text" class="textbox number" value="{$CONFIG.proxy_port}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_time_date}</legend>
         <div><label for="fuzzy_time_format">{$LANG.settings.fuzzy_time_format}</label><span><input name="config[fuzzy_time_format]" id="fuzzy_time_format" type="text" class="textbox" value="{$CONFIG.fuzzy_time_format}"> PHP <a href="http://www.php.net/strftime" target="_blank">strftime</a></span></div>
         <div><label for="time_format">{$LANG.settings.time_format}</label><span><input name="config[time_format]" id="time_format" type="text" class="textbox" value="{$CONFIG.time_format}"> PHP <a href="http://www.php.net/strftime" target="_blank">strftime</a></span></div>
         <div><label for="dispatch_date_format">{$LANG.settings.dispatch_date_format}</label><span><input name="config[dispatch_date_format]" id="dispatch_date_format" type="text" class="textbox" value="{if ($CONFIG.dispatch_date_format)}{$CONFIG.dispatch_date_format}{else}%b %d %Y{/if}"> PHP <a href="http://www.php.net/strftime" target="_blank">strftime</a></span></div>
         <div><label for="time_offset">{$LANG.settings.time_utc_offset}</label><span><input name="config[time_offset]" id="time_offset" type="text" class="textbox number" value="{$CONFIG.time_offset}"></span></div>
         {if isset($TIMEZONES)}
         <div><label for="time_zone">{$LANG.settings.time_zone}</label><span><select name="config[time_zone]" id="time_zone" type="text" class="textbox">
            {foreach from=$TIMEZONES item=timezone}<option value="{$timezone.zone}"{$timezone.selected}>{$timezone.zone}</option>{/foreach}
            </select></span>
         </div>
         {/if}
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.log_retention} ({$LANG.settings.log_retention_desc})</legend>
         <div><label for="r_admin_activity">{$LANG.settings.admin_activity_log}</label><span><input name="config[r_admin_activity]" id="r_admin_activity" type="text" class="textbox number" value="{$CONFIG.r_admin_activity}"> {$LANG.common.days}</span></div>
         <div><label for="r_admin_error">{$LANG.settings.title_admin_error_log}</label><span><input name="config[r_admin_error]" id="r_admin_error" type="text" class="textbox number" value="{$CONFIG.r_admin_error}"> {$LANG.common.days}</span></div>
         <div><label for="r_email">{$LANG.settings.title_email_log}</label><span><input name="config[r_email]" id="r_email" type="text" class="textbox number" value="{$CONFIG.r_email}"> {$LANG.common.days}</span></div>
         <div><label for="r_request">{$LANG.navigation.nav_request_log}</label><span><input name="config[r_request]" id="r_request" type="text" class="textbox number" value="{$CONFIG.r_request}"> {$LANG.common.days}</span></div>
         <div><label for="r_staff">{$LANG.navigation.nav_access_log}</label><span><input name="config[r_staff]" id="r_staff" type="text" class="textbox number" value="{$CONFIG.r_staff}"> {$LANG.common.days}</span></div>
         <div><label for="r_system_error">{$LANG.settings.title_system_error_log}</label><span><input name="config[r_system_error]" id="r_system_error" type="text" class="textbox number" value="{$CONFIG.r_system_error}"> {$LANG.common.days}</span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.common.other}</legend>
         <div><label for="feed_access_key">{$LANG.settings.feed_access_key}</label><span><input name="config[feed_access_key]" id="feed_access_key" type="text" class="textbox" value="{$CONFIG.feed_access_key}"></span></div>
         <div><label for="hide_chat">{$LANG.settings.hide_chat}</label><span><input name="config[hide_chat]" id="chat" type="hidden" class="toggle" value="{$CONFIG.hide_chat}"></span></div>
      </fieldset>
   </div>
   <div id="Copyright" class="tab_content">
      <h3>{$LANG.settings.title_copyright}</h3>
      <fieldset>
         <div><span><textarea name="config[store_copyright]" id="copyright_content" class="textbox fck">{$CONFIG.store_copyright|escape:"html"}</textarea></span></div>
      </fieldset>
   </div>
   <div id="Extra" class="tab_content">
      <h3>{$LANG.settings.title_extra}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_product_clone}</legend>
         <div><label for="product_clone">{$LANG.common.status}</label><span><select name="config[product_clone]" id="product_clone" class="textbox">
            {foreach from=$OPT_PRODUCT_CLONE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="product_clone_images">{$LANG.settings.product_clone_images}</label><span><input name="config[product_clone_images]" id="product_clone_images" type="hidden" class="toggle" value="{$CONFIG.product_clone_images}"></span></div>
         <div><label for="product_clone_options">{$LANG.settings.product_clone_options}</label><span><input name="config[product_clone_options]" id="product_clone_options" type="hidden" class="toggle" value="{$CONFIG.product_clone_options}"></span></div>
         <div><label for="product_clone_options_matrix">{$LANG.settings.product_clone_options_matrix}</label><span><input name="config[product_clone_options_matrix]" id="product_clone_options_matrix" type="hidden" class="toggle" value="{$CONFIG.product_clone_options_matrix}"></span></div>
         <div><label for="product_clone_acats">{$LANG.settings.product_clone_acats}</label><span><input name="config[product_clone_acats]" id="product_clone_acats" type="hidden" class="toggle" value="{$CONFIG.product_clone_acats}"></span></div>
         <div><label for="product_clone_main_stock">{$LANG.settings.product_clone_stock}</label><span><input name="config[product_clone_main_stock]" id="product_clone_main_stock" type="hidden" class="toggle" value="{$CONFIG.product_clone_main_stock}"></span></div>
         <div><label for="product_clone_code">{$LANG.settings.product_clone_code}</label><span><select name="config[product_clone_code]" id="product_clone_code" class="textbox">
            {foreach from=$OPT_PRODUCT_CLONE_CODE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="product_clone_translations">{$LANG.settings.product_clone_translations}</label><span><input name="config[product_clone_translations]" id="product_clone_translations" type="hidden" class="toggle" value="{$CONFIG.product_clone_translations}"></span></div>
         <div><label for="product_clone_redirect">{$LANG.settings.product_clone_redirect}</label><span><input name="config[product_clone_redirect]" id="product_clone_redirect" type="hidden" class="toggle" value="{$CONFIG.product_clone_redirect}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.gdpr}</legend>
         <div><label for="dbl_opt">{$LANG.settings.dbl_opt}</label><span><input name="config[dbl_opt]" id="dbl_opt" type="hidden" class="toggle" value="{$CONFIG.dbl_opt}"></span></div>
         <div><label for="cookie_dialogue">{$LANG.settings.cookie_dialogue}</label><span><input name="config[cookie_dialogue]" id="cookie_dialogue" type="hidden" class="toggle" value="{$CONFIG.cookie_dialogue}"></span></div>
      </fieldset>
   </div>
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="hidden" name="config[bftime]" value="600">
      <input type="hidden" name="config[bfattempts]" value="5">
      <input id="submit" type="submit" class="button" value="{$LANG.common.save}">
      <input type="hidden" name="previous-tab" id="previous-tab" value="">
   </div>
   
</form>
<script type="text/javascript">
   var county_list = {if !empty($VAL_JSON_COUNTY)}{$VAL_JSON_COUNTY}{else}false{/if};
   {if $JSON_STYLES}var json_skins	= {$JSON_STYLES};{/if}
</script>
