{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
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
         <legend>Social Accounts</legend>
         <div><label for="twitter">Twitter</label><span><input name="config[twitter]" id="twitter" type="text" class="textbox" value="{$CONFIG.twitter}"></span></div>
         <div><label for="facebook">Facebook</label><span><input name="config[facebook]" id="facebook" type="text" class="textbox" value="{$CONFIG.facebook}"></span></div>
         <div><label for="google_plus">Google+</label><span><input name="config[google_plus]" id="google_plus" type="text" class="textbox" value="{$CONFIG.google_plus}"></span></div>
         <div><label for="pinterest">Pinterest</label><span><input name="config[pinterest]" id="pinterest" type="text" class="textbox" value="{$CONFIG.pinterest}"></span></div>
         <div><label for="youtube">YouTube</label><span><input name="config[youtube]" id="youtube" type="text" class="textbox" value="{$CONFIG.youtube}"></span></div>
         <div><label for="instagram">Instagram</label><span><input name="config[instagram]" id="instagram" type="text" class="textbox" value="{$CONFIG.instagram}"></span></div>
         <div><label for="flickr">Flickr</label><span><input name="config[flickr]" id="flickr" type="text" class="textbox" value="{$CONFIG.flickr}"></span></div>
         <div><label for="linkedin">LinkedIn</label><span><input name="config[linkedin]" id="linkedin" type="text" class="textbox" value="{$CONFIG.linkedin}"></span></div>
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
   </div>
   <div id="Features" class="tab_content">
      <h3>{$LANG.settings.title_features}</h3>
      <fieldset>
         <legend>{$LANG.settings.google_analytics}</legend>
         <div><label for="google_analytics">{$LANG.settings.google_analytics_id}</label><span><input name="config[google_analytics]" id="google_analytics" type="text" class="textbox" value="{$CONFIG.google_analytics}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.navigation.nav_prod_reviews}</legend>
         <div><label for="enable_reviews">{$LANG.settings.enable_reviews}</label><span><input name="config[enable_reviews]" id="enable_reviews" type="hidden" class="toggle" value="{$CONFIG.enable_reviews}"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_orders}</legend>
         {*
            <div><label for="email_disable_alert">{$LANG.settings.email_disable_alert}</label><span><select name="config[email_disable_alert]" id="email_disable_alert" class="textbox">
         {foreach from=$OPT_EMAIL_DISABLE_ALERT item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span></div>
         *}
         <div><label for="basket_order_expire">{$LANG.settings.expire_pending}</label><span><input name="config[basket_order_expire]" id="basket_order_expire" class="textbox number" value="{$CONFIG.basket_order_expire}"> {$LANG.common.blank_to_disable}</span></div>
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
         <div class="clear important"><strong>{$LANG.settings.new_recaptcha_note}</strong></div>
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
         <div><label for="cookie_dialogue">{$LANG.settings.cookie_dialogue}</label><span><input name="config[cookie_dialogue]" id="cookie_dialogue" type="hidden" class="toggle" value="{$CONFIG.cookie_dialogue}"></span></div>
         <div><label for="force_completed">{$LANG.settings.force_completed}</label><span><input name="config[force_completed]" id="force_completed" type="hidden" class="toggle" value="{$CONFIG.force_completed}"></span></div>
      </fieldset>
   </div>
   <div id="Layout" class="tab_content">
      <h3>{$LANG.settings.title_layout}</h3>
      <fieldset>
         <legend>{$LANG.settings.title_display}</legend>
         <div><label for="catalogue_products_per_page">{$LANG.settings.product_per_page}</label><span><input name="config[catalogue_products_per_page]" id="catalogue_products_per_page" class="textbox number" value="{$CONFIG.catalogue_products_per_page}"></span></div>
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
         <div><label for="store_meta_keywords">{$LANG.settings.seo_meta_keywords}</label><span><textarea name="config[store_meta_keywords]" id="store_meta_keywords" class="textbox">{$CONFIG.store_meta_keywords}</textarea></span></div>
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
         <div><label for="enable_ssl">{$LANG.settings.ssl_enable}</label><span><select name="config[ssl]" id="enable_ssl" class="textbox">
            {foreach from=$OPT_SSL item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="ssl_force">{$LANG.settings.ssl_force}</label><span><select name="config[ssl_force]" id="ssl_force" class="textbox">
            {foreach from=$OPT_SSL_FORCE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="ssl_path">{$LANG.settings.ssl_root_path}</label><span><input name="config[ssl_path]" id="ssl_path" type="text" class="textbox" value="{$CONFIG.ssl_path}"> {$LANG.common.eg} /store/</span></div>
         <div><label for="ssl_url">{$LANG.settings.ssl_url}</label><span><input name="config[ssl_url]" id="ssl_url" type="text" class="textbox" value="{$CONFIG.ssl_url}"> {$LANG.common.eg} https://www.example.com/store</span></div>
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
         <textarea name="config[offline_content]" id="offline_content" class="textbox fck">{$CONFIG.offline_content}</textarea>
      </fieldset>
   </div>
   <div id="Logos" class="tab_content">
      <h3>{$LANG.settings.title_logo}</h3>
      {if isset($LOGOS)}
      <fieldset>
         <legend>{$LANG.settings.title_logo_current}</legend>
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
               <select id="" name="logo[{$logo.logo_id}][skin]" class="textbox select-skin">
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
               <select id="" name="logo[{$logo.logo_id}][style]" class="textbox select-style">
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
         <div><label for="email_method">{$LANG.settings.email_method}</label><span><select name="config[email_method]" id="email_method" class="textbox">
            {foreach from=$OPT_EMAIL_METHOD item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="email_name">{$LANG.settings.email_sender_name}</label><span><input name="config[email_name]" id="email_name" type="text" class="textbox" value="{$CONFIG.email_name}"></span></div>
         <div><label for="email_address">{$LANG.settings.email_sender_address}</label><span><input name="config[email_address]" id="email_address" type="text" class="textbox" value="{$CONFIG.email_address}"></span></div>
         <div><label for="email_smtp_host">{$LANG.settings.smtp_host}</label><span><input name="config[email_smtp_host]" id="email_smtp_host" type="text" class="textbox" value="{$CONFIG.email_smtp_host}"></span></div>
         <div><label for="email_smtp_port">{$LANG.settings.smtp_port}</label><span><input name="config[email_smtp_port]" id="email_smtp_port" type="text" class="textbox number" value="{$CONFIG.email_smtp_port}"></span></div>
         <div><label for="email_smtp">{$LANG.settings.smtp_auth}</label><span><select name="config[email_smtp]" id="email_smtp" class="textbox" autocomplete="off">
            {foreach from=$OPT_EMAIL_SMTP item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="email_smtp_user">{$LANG.settings.smtp_user}</label><span><input name="config[email_smtp_user]" id="email_smtp_user" type="text" class="textbox" value="{$CONFIG.email_smtp_user}" autocomplete="off"></span></div>
         <div><label for="email_smtp_password">{$LANG.settings.smtp_pass}</label><span><input name="config[email_smtp_password]" id="email_smtp_password" type="password" class="textbox" value="{$CONFIG.email_smtp_password}" autocomplete="off"></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_performance}</legend>
         <div><label for="debug">{$LANG.settings.debug_enable}</label><span><select name="config[debug]" id="debug" class="textbox">
            {foreach from=$OPT_DEBUG item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="debug">{$LANG.settings.debug_ip_addresses}</label><span><input name="config[debug_ip_addresses]" id="debug_ip_addresses" type="text" class="textbox" value="{$CONFIG.debug_ip_addresses}"></span></div>
         <div><label for="cache">{$LANG.settings.cache_enable}</label><span><select name="config[cache]" id="cache" class="textbox">
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
         <legend>{$LANG.common.other}</legend>
         <div><label for="feed_access_key">{$LANG.settings.feed_access_key}</label><span><input name="config[feed_access_key]" id="feed_access_key" type="text" class="textbox" value="{$CONFIG.feed_access_key}"></span></div>
      </fieldset>
   </div>
   <div id="Copyright" class="tab_content">
      <h3>{$LANG.settings.title_copyright}</h3>
      <fieldset>
         <div><span><textarea name="config[store_copyright]" id="copyright_content" class="textbox fck">{$CONFIG.store_copyright}</textarea></span></div>
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
         <div><label for="product_clone_code">{$LANG.settings.product_clone_code}</label><span><select name="config[product_clone_code]" id="product_clone_code" class="textbox">
            {foreach from=$OPT_PRODUCT_CLONE_CODE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="product_clone_translations">{$LANG.settings.product_clone_translations}</label><span><input name="config[product_clone_translations]" id="product_clone_translations" type="hidden" class="toggle" value="{$CONFIG.product_clone_translations}"></span></div>
         <div><label for="product_clone_redirect">{$LANG.settings.product_clone_redirect}</label><span><input name="config[product_clone_redirect]" id="product_clone_redirect" type="hidden" class="toggle" value="{$CONFIG.product_clone_redirect}"></span></div>
      </fieldset>
   </div>
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="hidden" name="config[bftime]" value="600">
      <input type="hidden" name="config[bfattempts]" value="5">
      <input id="submit" type="submit" class="button" value="{$LANG.common.save}">
      <input type="hidden" name="previous-tab" id="previous-tab" value="">
   </div>
   <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>
<script type="text/javascript">
   {if $VAL_JSON_COUNTY} var county_list = {$VAL_JSON_COUNTY};{/if}
   {if $JSON_STYLES}var json_skins	= {$JSON_STYLES};{/if}
</script>
