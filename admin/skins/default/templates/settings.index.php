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
<div class="settings-search-bar">
   <span id="settings-search-count" class="settings-search-count" hidden></span>
   <input type="search" id="settings-search" class="textbox" placeholder="{$LANG.common.search|default:'Search'} settings…" autocomplete="off">
</div>
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
         <legend>{$LANG.settings.title_domain}</legend>
         <div><label for="standard_url">{$LANG.settings.standard_url}</label><span><input id="standard_url" type="text" class="textbox" value="{$CONFIG.standard_url}" readonly disabled> <em>{$LANG.settings.edit_in_global_file|default:'Edit in includes/global.inc.php'}</em></span></div>
         <div><label for="cookie_domain">{$LANG.settings.cookie_domain}</label><span><input id="cookie_domain" type="text" class="textbox" value="{$CONFIG.cookie_domain}" readonly disabled> <em>{$LANG.settings.edit_in_global_file|default:'Edit in includes/global.inc.php'}</em></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_tax_lang}</legend>
         <div><label for="product_weight_unit">{$LANG.settings.weight_unit}</label><span><select name="config[product_weight_unit]" id="product_weight_unit" class="textbox">
            {foreach from=$OPT_PRODUCT_WEIGHT_UNIT item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="product_size_unit">{$LANG.settings.size_unit}</label><span><select name="config[product_size_unit]" id="product_size_unit" class="textbox">
            {foreach from=$OPT_PRODUCT_SIZE_UNIT item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
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
         <div><label for="bsky">Bluesky</label><span><input name="config[bsky]" id="bsky" type="text" class="textbox" value="{$CONFIG.bsky}"></span></div>
         <div><label for="facebook">Facebook</label><span><input name="config[facebook]" id="facebook" type="text" class="textbox" value="{$CONFIG.facebook}"></span></div>
         <div><label for="instagram">Instagram</label><span><input name="config[instagram]" id="instagram" type="text" class="textbox" value="{$CONFIG.instagram}"></span></div>
         <div><label for="linkedin">LinkedIn</label><span><input name="config[linkedin]" id="linkedin" type="text" class="textbox" value="{$CONFIG.linkedin}"></span></div>
         <div><label for="pinterest">Pinterest</label><span><input name="config[pinterest]" id="pinterest" type="text" class="textbox" value="{$CONFIG.pinterest}"></span></div>
         <div><label for="reddit">Reddit</label><span><input name="config[reddit]" id="reddit" type="text" class="textbox" value="{$CONFIG.reddit}"></span></div>
         <div><label for="twitter">X</label><span><input name="config[twitter]" id="twitter" type="text" class="textbox" value="{$CONFIG.twitter}"></span></div>
         <div><label for="youtube">YouTube</label><span><input name="config[youtube]" id="youtube" type="text" class="textbox" value="{$CONFIG.youtube}"></span></div>
      </fieldset>
   </div>
   <div id="Features" class="tab_content">
      <h3>{$LANG.settings.title_features}</h3>
      <fieldset>
         <legend>{$LANG.navigation.nav_prod_reviews}</legend>
         <div><label for="enable_reviews">{$LANG.settings.enable_reviews}</label><span>
         <select name="config[enable_reviews]" class="textbox">
         <option value="0"{if $CONFIG.enable_reviews=='0'} selected="selected"{/if}>{$LANG.common.disabled}</option>
         <option value="1"{if $CONFIG.enable_reviews=='1'} selected="selected"{/if}>{$LANG.common.enabled}</option>
         <option value="2"{if $CONFIG.enable_reviews=='2'} selected="selected"{/if}>{$LANG.catalogue.reviews_no_gravatar}</option>
         </select>
         </span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_orders}</legend>
         <div><label for="basket_order_expire">{$LANG.settings.expire_pending}</label><span><input name="config[basket_order_expire]" id="basket_order_expire" class="textbox number" value="{$CONFIG.basket_order_expire}"> {$LANG.common.blank_to_disable}</span></div>
         <div><label for="order_minimum">{$LANG.settings.order_minimum}</label><span><input name="config[order_minimum]" id="order_minimum" type="text" class="textbox number" value="{$CONFIG.order_minimum}"> {$LANG.common.blank_to_disable}</span></div>
         <div><label for="order_maximum">{$LANG.settings.order_maximum}</label><span><input name="config[order_maximum]" id="order_maximum" type="text" class="textbox number" value="{$CONFIG.order_maximum}"> {$LANG.common.blank_to_disable}</span></div>
         <div><label for="oid_mode">{$LANG.orders.id_mode}</label><span><select name="config[oid_mode]" id="oid_mode" class="textbox preview_order" onchange="this.value == 'i' ? document.getElementById('i_options').style.display = 'block' :  document.getElementById('i_options').style.display = 'none';">
         {foreach from=$OPT_OID_MODE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span></div>
         <div{if $CONFIG.oid_mode!=="i"} style="display: none"{/if} id="i_options">
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
      <div><label>{$LANG.common.preview}</label><span><code id="oid_format_preview" class="oid-preview">&mdash;</code></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_sales}</legend>
         <div><label for="catalogue_sale_mode">{$LANG.settings.sales_mode}</label><span><select name="config[catalogue_sale_mode]" id="catalogue_sale_mode" class="textbox">
            {foreach from=$OPT_CATALOGUE_SALE_MODE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="catalogue_sale_percentage">{$LANG.settings.sales_percentage}</label><span><input name="config[catalogue_sale_percentage]" id="catalogue_sale_percentage" type="text" class="textbox number" value="{$CONFIG.catalogue_sale_percentage}">%</span></div>
         <div><label for="sale_starts">{$LANG.catalogue.title_coupon_starts} (YYYY-MM-DD)</label><span><input type="text" name="config[sale_starts]" id="sale_starts" value="{if $CONFIG.sale_starts=='0000-00-00'}{else}{$CONFIG.sale_starts}{/if}" class="textbox date number"></span></div>
	      <div><label for="sale_expires">{$LANG.catalogue.title_coupon_expires} (YYYY-MM-DD)</label><span><input type="text" name="config[sale_expires]" id="sale_expires" value="{if $CONFIG.sale_expires=='0000-00-00'}{else}{$CONFIG.sale_expires}{/if}" class="textbox date number"></span></div>
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
            {if !empty($unavailable_captchas)}
            {$LANG.settings.captcha_warning}
            <ul style="margin:0">
               {foreach from=$unavailable_captchas key=k item=v}
               <li>{$k}</li>
               {/foreach}
            </ul>
            {/if}
         </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.navigation.nav_subscribers}</legend>
         <div><label for="newsletter_status">{$LANG.common.status}</label><span>
            <select name="config[newsletter_status]" id="newsletter_status" class="textbox">
            {foreach from=$OPT_NEWSLETTER_STATUS item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select>
         </span></div>
         <div><label for="exit_modal">{$LANG.settings.enable_exit_modal}</label><span><input name="config[exit_modal]" id="exit_modal" type="hidden" class="toggle" value="{$CONFIG.exit_modal}"></span></div>
         <div><label for="dbl_opt">{$LANG.settings.dbl_opt}</label><span>
         {if isset($CONFIG.subscribe_mode_lax) && $CONFIG.subscribe_mode_lax=='1'}
            <input name="config[dbl_opt]" id="dbl_opt" type="hidden" class="toggle" value="{$CONFIG.dbl_opt}">
         {else}
            <input name="config[dbl_opt]" id="dbl_opt" type="hidden" value="1">
            <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/1_checkbox.png">
         {/if}
         </span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.address.w3w} - <a href="https://what3words.com/business/ecommerce/" target="_blank">{$LANG.common.learn_more}</a></legend>
         <div><label for="w3w">{$LANG.settings.w3w_status}</label><span><input name="config[w3w_status]" id="w3w_status" type="hidden" class="toggle" value="{$CONFIG.w3w_status}"></span></div>
         <div><label for="w3w_user_key">{$LANG.settings.w3w_user_key|default:'Your API key'}</label><span><input name="config[w3w_user_key]" id="w3w_user_key" type="text" class="textbox" value="{$CONFIG.w3w_user_key}" placeholder="{$LANG.common.optional}"><br><small>{$LANG.settings.w3w_user_key_desc|default:'Leave blank to use the auto-provisioned partner key. Enter your own key from <a href="https://accounts.what3words.com/" target="_blank">accounts.what3words.com</a> to use the latest SDK.'}</small></span></div>
         {if !$w3w_compatibility}
         <p><strong>{$LANG.settings.w3w_na}</strong></p>
         {/if}
         <input name="config[w3w]" id="w3w" type="hidden" value="{$CONFIG.w3w}">
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
            <select name="config[shipping_defaults]" class="textbox">
            {foreach from=$OPT_SHIPPING_DEFAULTS item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select>
         </span></div>
         <div><label for="force_completed">{$LANG.settings.force_completed}</label><span><input name="config[force_completed]" id="force_completed" type="hidden" class="toggle" value="{$CONFIG.force_completed}"></span></div>
         <div><label for="disable_estimates">{$LANG.settings.disable_estimates}</label><span><input name="config[disable_estimates]" id="disable_estimates" type="hidden" class="toggle" value="{$CONFIG.disable_estimates}"></span></div>
         <div><label for="basket_allow_non_invoice_address">{$LANG.settings.dispatch_to_non_invoice}</label><span><input name="config[basket_allow_non_invoice_address]" id="basket_allow_non_invoice_address" type="hidden" class="toggle" value="{$CONFIG.basket_allow_non_invoice_address}"></span></div>
         <div><label for="emailconf">{$LANG.settings.emailconf}</label><span><input name="config[emailconf]" id="emailconf" type="hidden" class="toggle" value="{$CONFIG.emailconf}"></span></div>
         <div><label for="admin_login_notify">{$LANG.settings.admin_login_notify}</label><span><input name="config[admin_login_notify]" id="admin_login_notify" type="hidden" class="toggle" value="{$CONFIG.admin_login_notify}"></span></div>
         <div><label for="allow_telemetry">{$LANG.settings.allow_telemetry}</label><span><input name="config[allow_telemetry]" id="allow_telemetry" type="hidden" class="toggle" value="{$CONFIG.allow_telemetry}"></span></div>
      </fieldset>
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
         <div><label for="stock_change_time">{$LANG.settings.stock_reduce}</label><span><select name="config[stock_change_time]" id="stock_change_time" class="textbox">
            {foreach from=$OPT_STOCK_CHANGE_TIME item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="hide_out_of_stock">{$LANG.settings.title_hide_out_of_stock}</label><span><input name="config[hide_out_of_stock]" id="hide_out_of_stock" type="hidden" class="toggle" value="{$CONFIG.hide_out_of_stock}"></span>&nbsp;{$LANG.settings.no_admin_affect}</div>
         <div><label for="update_main_stock">{$LANG.settings.update_main_stock}</label><span><input name="config[update_main_stock]" id="update_main_stock" type="hidden" class="toggle" value="{$CONFIG.update_main_stock}"></span>&nbsp;{$LANG.settings.matrix_in_use}</div>
         <div><label for="image_delete">{$LANG.settings.image_delete}</label><span><input name="config[image_delete]" id="image_delete" type="hidden" class="toggle" value="{$CONFIG.image_delete}"></span></div>
         <div><label for="image_upload_format">{$LANG.settings.image_upload_format}</label><span><select name="config[image_upload_format]" id="image_upload_format" class="textbox">
            <option value="webp"{if $CONFIG.image_upload_format=='webp'} selected="selected"{/if}>{$LANG.settings.image_upload_format_webp}</option>
            <option value="jpeg"{if $CONFIG.image_upload_format=='jpeg'} selected="selected"{/if}>{$LANG.settings.image_upload_format_jpeg}</option>
            <option value="png"{if $CONFIG.image_upload_format=='png'} selected="selected"{/if}>{$LANG.settings.image_upload_format_png}</option>
            <option value="original"{if $CONFIG.image_upload_format=='original'} selected="selected"{/if}>{$LANG.settings.image_upload_format_original}</option>
            </select></span>
         </div>
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
         <div><label for="show_basket_weight">{$LANG.settings.show_basket_weight}</label><span><input name="config[show_basket_weight]" id="show_basket_weight" type="hidden" class="toggle" value="{$CONFIG.show_basket_weight}"></span></div>
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
         <div><label for="catalogue_related_products">{$LANG.settings.product_related}</label><span><input name="config[catalogue_related_products]" id="catalogue_related_products" type="hidden" class="toggle" value="{$CONFIG.catalogue_related_products|default:'1'}"></span></div>
         <div><label for="catalogue_related_products_count">{$LANG.settings.product_related_number}</label><span><input name="config[catalogue_related_products_count]" id="catalogue_related_products_count" type="number" min="1" max="20" class="textbox number" value="{$CONFIG.catalogue_related_products_count|default:'5'}"></span></div>
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
      {if isset($LOGOS)}
      <fieldset>
         <legend>{$LANG.settings.title_logo}</legend>
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
      <fieldset>
         <legend>{$LANG.settings.title_copyright}</legend>
         <div><span><textarea name="config[store_copyright]" id="copyright_content" class="textbox fck">{$CONFIG.store_copyright|escape:"html"}</textarea></span></div>
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
            <div><label for="seo_ext">{$LANG.settings.seo_ext}</label><span>
         <select name="config[seo_ext]" id="seo_ext" class="textbox">
            {foreach from=$OPT_SEO_EXT item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
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
         <div id="smtp_settings"{if !$SHOW_SMTP} style="display: none"{/if}>
            <div><label for="email_smtp_host">{$LANG.settings.smtp_host}</label><span><input name="config[email_smtp_host]" id="email_smtp_host" type="text" class="textbox preview_email" value="{$CONFIG.email_smtp_host}"></span></div>
            <div><label for="email_smtp_port">{$LANG.settings.smtp_port}</label><span><input name="config[email_smtp_port]" id="email_smtp_port" type="text" class="textbox number preview_email" value="{$CONFIG.email_smtp_port}"></span></div>
            <div><label for="email_smtp">{$LANG.settings.smtp_auth}</label><span><select name="config[email_smtp]" id="email_smtp" class="textbox preview_email" autocomplete="off">
                  {foreach from=$OPT_EMAIL_SMTP item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
                  </select></span>
            </div>
            <div><label for="email_smtp_user">{$LANG.settings.smtp_user}</label><span><input name="config[email_smtp_user]" id="email_smtp_user" type="text" class="textbox preview_email" value="{$CONFIG.email_smtp_user}" autocomplete="off"></span></div>
            <div><label for="email_smtp_password">{$LANG.settings.smtp_pass}</label><span><input name="config[email_smtp_password]" id="email_smtp_password" type="password" class="textbox preview_email" value="{$CONFIG.email_smtp_password|escape:'html'}" onfocus="this.removeAttribute('readonly');" readonly autocomplete="off"></span></div>
         </div>
         {$EMAIL_PLUGIN_FIELDS}
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
         {if $EMAIL_PLUGIN_JS}
         <script>
            document.addEventListener("DOMContentLoaded", function() {ldelim}
               $("#email_method").off("change").on("change", function() {ldelim}
                  $(".email-plugin-settings").hide();
                  switch($(this).val()) {ldelim}
                     case 'mail':
                        $("#smtp_settings").slideUp();
                        break;
                     {$EMAIL_PLUGIN_JS}
                     default:
                        $("#smtp_settings").slideDown();
                  {rdelim}
               {rdelim});
            {rdelim});
         </script>
         {/if}
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.title_performance}</legend>
         <div><label for="debug">{$LANG.settings.debug_enable}</label><span><select name="config[debug]" id="debug" class="textbox">
            {foreach from=$OPT_DEBUG item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
         <div><label for="debug_ip_addresses">{$LANG.settings.debug_ip_addresses}</label><span><input name="config[debug_ip_addresses]" id="debug_ip_addresses" type="text" class="textbox" value="{$CONFIG.debug_ip_addresses}"></span></div>
         <div><label for="cache">{$LANG.settings.cache_enable} (<a href="https://support.cubecart.com/hc/en-gb/articles/360003831737" target="_blank">{$CACHE_METHOD}</a>)</label><span><select name="config[cache]" id="cache" class="textbox">
     {foreach from=$OPT_CACHE item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
   </select></span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.elasticsearch}</legend>
         <div>Elasticsearch brings lightening fast, search-as-you-type functionality to your store. This is included as standard with official <a href="https://hosted.cubecart.com/" target="_blank">CubeCart Hosting</a>. Alternatively please contact your hosting company to check for availability.</div>
         <div><label for="elasticsearch">{$LANG.common.enable}</label><span><input name="config[elasticsearch]" id="elasticsearch" type="hidden" class="toggle" value="{$CONFIG.elasticsearch}"></span></div>
         <div><label>{$LANG.settings.es_hosts}</label><span><code>{if $ES_SUMMARY.es_h}{$ES_SUMMARY.es_h|escape:'html'}{else}&mdash;{/if}</code></span></div>
         <div><label>{$LANG.account.authtype}</label><span>{$ES_SUMMARY.es_t|escape:'html'}</span></div>
         {if $ES_SUMMARY.is_basic}
         <div><label>{$LANG.account.username}</label><span><code>{if $ES_SUMMARY.es_u}{$ES_SUMMARY.es_u|escape:'html'}{else}&mdash;{/if}</code></span></div>
         <div><label>{$LANG.account.password}</label><span><code>{if $ES_SUMMARY.es_p}{$ES_SUMMARY.es_p}{else}&mdash;{/if}</code></span></div>
         {elseif $ES_SUMMARY.is_api}
         <div><label>{$LANG.account.api}</label><span><code>{if $ES_SUMMARY.es_a}{$ES_SUMMARY.es_a}{else}&mdash;{/if}</code></span></div>
         {/if}
         <div><label>{$LANG.settings.index_name}</label><span><code>{if $ES_SUMMARY.es_i}{$ES_SUMMARY.es_i|escape:'html'}{else}&mdash;{/if}</code></span></div>
         <div><label>{$LANG.settings.es_ssl_v}</label><span>{$ES_SUMMARY.es_v}</span></div>
         <div><label>{$LANG.settings.cert_auth}</label><span><code>{if $ES_SUMMARY.es_c}{$ES_SUMMARY.es_c|escape:'html'}{else}&mdash;{/if}</code></span></div>
         <div><label>Only index items in stock</label><span>{$ES_SUMMARY.es_is}</span></div>
         <div class="clear important"><strong>Connection details are configured in <code>includes/global.inc.php</code>. See <code>global.inc.php-dist</code> for the available <code>$glob['es_*']</code> keys. After enabling, build your search index <a href="?_g=maintenance#elasticsearch">here</a>.</strong></div>
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
         <div><label for="fuzzy_time_format">{$LANG.settings.fuzzy_time_format}</label><span><input name="config[fuzzy_time_format]" id="fuzzy_time_format" type="text" class="textbox" value="{$CONFIG.fuzzy_time_format}"> PHP <a href="http://www.php.net/date" target="_blank">date</a></span></div>
         <div><label for="time_format">{$LANG.settings.time_format}</label><span><input name="config[time_format]" id="time_format" type="text" class="textbox" value="{$CONFIG.time_format}"> PHP <a href="http://www.php.net/date" target="_blank">date</a></span></div>
         <div><label for="dispatch_date_format">{$LANG.settings.dispatch_date_format}</label><span><input name="config[dispatch_date_format]" id="dispatch_date_format" type="text" class="textbox" value="{if ($CONFIG.dispatch_date_format)}{$CONFIG.dispatch_date_format}{else}%b %d %Y{/if}"> PHP <a href="http://www.php.net/date" target="_blank">date</a></span></div>
         <div><label for="time_offset">{$LANG.settings.time_utc_offset}</label><span><input name="config[time_offset]" id="time_offset" type="text" class="textbox number" value="{$CONFIG.time_offset}"></span></div>
         {if isset($TIMEZONES)}
         <div><label for="time_zone">{$LANG.settings.time_zone}</label><span><select name="config[time_zone]" id="time_zone" type="text" class="textbox">
            {foreach from=$TIMEZONES item=timezone}<option value="{$timezone.value}"{$timezone.selected}>{$timezone.zone}</option>{/foreach}
            </select></span>
         </div>
         {/if}
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.log_retention} ({$LANG.settings.log_retention_desc})</legend>
         <div><label for="r_admin_activity">{$LANG.settings.admin_activity_log}</label><span><input name="config[r_admin_activity]" id="r_admin_activity" type="number" class="textbox number" value="{$CONFIG.r_admin_activity}"> {$LANG.common.days}</span></div>
         <div><label for="r_admin_error">{$LANG.settings.title_admin_error_log}</label><span><input name="config[r_admin_error]" id="r_admin_error" type="number" class="textbox number" value="{$CONFIG.r_admin_error}"> {$LANG.common.days}</span></div>
         <div><label for="r_email">{$LANG.settings.title_email_log}</label><span><input name="config[r_email]" id="r_email" type="number" class="textbox number" value="{$CONFIG.r_email}"> {$LANG.common.days}</span></div>
         <div><label for="email_track_opens">{$LANG.settings.email_track_opens}</label><span><input name="config[email_track_opens]" id="email_track_opens" type="hidden" class="toggle" value="{$CONFIG.email_track_opens|default:'1'}"></span></div>
         <div><label for="r_request">{$LANG.navigation.nav_request_log}</label><span><input name="config[r_request]" id="r_request" type="number" class="textbox number" value="{$CONFIG.r_request}"> {$LANG.common.days}</span></div>
         <div><label for="r_staff">{$LANG.navigation.nav_access_log}</label><span><input name="config[r_staff]" id="r_staff" type="number" class="textbox number" value="{$CONFIG.r_staff}"> {$LANG.common.days}</span></div>
         <div><label for="r_system_error">{$LANG.settings.title_system_error_log}</label><span><input name="config[r_system_error]" id="r_system_error" type="number" class="textbox number" value="{$CONFIG.r_system_error}"> {$LANG.common.days}</span></div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.common.other}</legend>
         <div><label for="feed_access_key">{$LANG.settings.feed_access_key}</label><span><input name="config[feed_access_key]" id="feed_access_key" type="text" class="textbox" value="{$CONFIG.feed_access_key}"></span></div>
         <div><label for="hide_chat">{$LANG.settings.hide_chat}</label><span><input name="config[hide_chat]" id="chat" type="hidden" class="toggle" value="{$CONFIG.hide_chat}"></span></div>
      </fieldset>
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
   <div id="Cart_Recovery" class="tab_content">
      <h3>{$LANG.settings.tab_cart_recovery}</h3>
      <fieldset>
         <legend>{$LANG.settings.abandoned_cart_settings}</legend>
         <div><label for="abandoned_cart_enabled">{$LANG.settings.abandoned_cart_enabled}</label><span><input name="config[abandoned_cart_enabled]" id="abandoned_cart_enabled" type="hidden" class="toggle" value="{$CONFIG.abandoned_cart_enabled}"></span></div>
         <div><label for="abandoned_cart_delay">{$LANG.settings.abandoned_cart_delay}</label><span><select name="config[abandoned_cart_delay]" id="abandoned_cart_delay" class="textbox">
            {foreach from=$OPT_ABANDONED_CART_DELAY item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select> <small class="recommended">{$LANG.common.recommended}: 1 Day</small></span>
         </div>
         <div><label for="abandoned_cart_notify_cooldown">{$LANG.settings.abandoned_cart_notify_cooldown}</label><span><select name="config[abandoned_cart_notify_cooldown]" id="abandoned_cart_notify_cooldown" class="textbox">
            {foreach from=$OPT_ABANDONED_CART_NOTIFY_COOLDOWN item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select> <small class="recommended">{$LANG.common.recommended}: 7 Days</small></span>
         </div>
         <div><label for="abandoned_cart_order_window">{$LANG.settings.abandoned_cart_order_window}</label><span><select name="config[abandoned_cart_order_window]" id="abandoned_cart_order_window" class="textbox">
            {foreach from=$OPT_ABANDONED_CART_ORDER_WINDOW item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select> <small class="recommended">{$LANG.common.recommended}: 3 Days</small></span>
         </div>
         <div><label for="abandoned_cart_coupon">{$LANG.settings.abandoned_cart_coupon}</label><span><select name="config[abandoned_cart_coupon]" id="abandoned_cart_coupon" class="textbox">
            {foreach from=$OPT_ABANDONED_CART_COUPON item=option}<option value="{$option.value}"{$option.selected}>{$option.title}</option>{/foreach}
            </select></span>
         </div>
      </fieldset>
      <fieldset>
         <p>{$LANG.settings.abandoned_cart_info}</p>
      </fieldset>
   </div>
   <div id="Scheduled_Tasks" class="tab_content">
      <h3>{$LANG.settings.tab_cron}</h3>
      <p>{$LANG.settings.cron_desc}</p>
      <p><strong>{$LANG.settings.cron_url}:</strong> {$STORE_URL}/index.php?_g=cron&_m=run <span class="copy_text" style="float:none;opacity:1" data-copied="{$LANG.common.copied}" data-copy="{$LANG.common.click_copy}" data-value="{$STORE_URL}/index.php?_g=cron&_m=run"></span></p>
      <fieldset>
         <table width="100%">
            <thead>
               <tr>
                  <th>{$LANG.settings.title_cron}</th>
                  <th>{$LANG.common.status}</th>
                  <th>{$LANG.settings.cron_frequency}</th>
                  <th>{$LANG.settings.cron_last_run}</th>
                  <th>{$LANG.settings.cron_last_result}</th>
               </tr>
            </thead>
            <tbody>
               {foreach from=$CRON_TASKS item=task}
               <tr>
                  <td>{$task.label}</td>
                  <td style="text-align:center"><input type="hidden" class="toggle" name="cron_tasks[{$task.id}][enabled]" id="cron_enabled_{$task.id}" value="{$task.enabled}"></td>
                  <td>
                     <select name="cron_tasks[{$task.id}][frequency]" class="textbox">
                        {foreach from=$CRON_FREQUENCIES key=secs item=flabel}
                        <option value="{$secs}"{if $task.frequency == $secs} selected="selected"{/if}>{$flabel}</option>
                        {/foreach}
                     </select>
                  </td>
                  <td>{if $task.last_run}{$task.last_run}{else}{$LANG.common.never}{/if}</td>
                  <td>{if $task.last_result}{$task.last_result}{else}&mdash;{/if}</td>
               </tr>
               {/foreach}
            </tbody>
         </table>
      </fieldset>
      <fieldset>
         <legend>{$LANG.settings.cron_examples}</legend>
         <p>{$LANG.settings.cron_examples_desc}</p>
         <div><label>PHP CLI</label><span><code>*/5 * * * * {$PHP_BINARY} {$CRON_CLI_PATH} > /dev/null 2>&1</code></span></div>
         <div><label>Cron</label><span><code>*/5 * * * * /usr/bin/curl -s "{$STORE_URL}/index.php?_g=cron&_m=run" > /dev/null 2>&1</code></span></div>
         <div><label>Wget</label><span><code>*/5 * * * * /usr/bin/wget -qO /dev/null "{$STORE_URL}/index.php?_g=cron&_m=run"</code></span></div>
      </fieldset>
      <p>
         * {$LANG.settings.cron_cache_note}<br>
         ** {$LANG.settings.scheduled_snippet}
      </p>
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
   </div>
      {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
   {$tab}
   {/foreach}
   {/if}
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
<script>{literal}
document.addEventListener('DOMContentLoaded', function() {

   // -- Live order-ID format preview ----------------------------------------
   // Replicates classes/order.class.php setOrderFormat() client-side. MySQL
   // DATE_FORMAT specifiers in prefix/postfix are substituted with "now",
   // sequence is zero-padded, and we sample the *next* order ID using max+1.
   var ORDER_NEXT_ID = {/literal}{if isset($ORDER_NEXT_ID)}{$ORDER_NEXT_ID}{else}1{/if}{literal};

   // -- Tab health badges ---------------------------------------------------
   {/literal}{if $TAB_HEALTH_CRON}$('#tab_Scheduled_Tasks').addClass('tab-health-warning').attr('title', 'A scheduled task is overdue — check the Scheduled Tasks tab');{/if}
   {if $TAB_HEALTH_GENERAL}$('#tab_General').addClass('tab-health-warning').attr('title', 'Standard URL does not match the current scheme or host');{/if}{literal}


   function pad2(n) { return ('0'+n).slice(-2); }
   function expandDateSpecifiers(str) {
      if (!str || str.indexOf('%') === -1) return str || '';
      var d = new Date();
      var Y = d.getFullYear();
      var m = d.getMonth() + 1;
      var day = d.getDate();
      var H = d.getHours();
      var i = d.getMinutes();
      var s = d.getSeconds();
      var map = {
         '%Y': String(Y), '%y': pad2(Y % 100),
         '%m': pad2(m),  '%c': String(m),
         '%d': pad2(day),'%e': String(day),
         '%H': pad2(H),  '%k': String(H),
         '%h': pad2(((H+11)%12)+1), '%I': pad2(((H+11)%12)+1), '%l': String(((H+11)%12)+1),
         '%i': pad2(i),
         '%s': pad2(s),  '%S': pad2(s),
         '%p': H < 12 ? 'AM' : 'PM'
      };
      return str.replace(/%[YymcdeHkhIlispSp]/g, function(m){ return map[m] !== undefined ? map[m] : m; });
   }
   function lpad(n, len) {
      var s = String(n);
      while (s.length < len) s = '0' + s;
      return s;
   }
   function buildOidSample(prefix, postfix, zeros, start, oid) {
      zeros = parseInt(zeros, 10) || 0;
      start = parseInt(start, 10) || 0;
      var num = (oid + start);
      var seq = zeros > 0 ? lpad(num, zeros) : String(num);
      return expandDateSpecifiers(prefix) + seq + expandDateSpecifiers(postfix);
   }
   function updateOidPreview() {
      var $out = $('#oid_format_preview');
      if (!$out.length) return;
      var mode = $('#oid_mode').val();
      var prefix  = $('#oid_prefix').val()  || '';
      var postfix = $('#oid_postfix').val() || '';
      var zeros   = $('#oid_zeros').val();
      var start   = $('#oid_start').val();
      var sample;
      if (mode === 't') {
         var d = new Date();
         sample = pad2(d.getFullYear() % 100) + pad2(d.getMonth()+1) + pad2(d.getDate())
                + '-' + pad2(d.getHours()) + pad2(d.getMinutes()) + pad2(d.getSeconds())
                + '-' + Math.floor(1000 + Math.random() * 9000);
      } else {
         sample = buildOidSample(prefix, postfix, zeros, start, ORDER_NEXT_ID);
      }
      $out.text(sample);
   }
   $('.preview_order').on('input change', updateOidPreview);
   updateOidPreview();

   // -- Settings search filter ---------------------------------------------
   var $search = $('#settings-search');
   var $form   = $('#form-settings');
   var $tabBar = $('#tab_control');
   var $count  = $('#settings-search-count');
   var savedActiveTab = null; // tab content id active when search started

   // Inject a pseudo-tab that activates while searching. Clicking it clears search.
   var $pseudoTab = $('<div class="tab settings-search-tab" id="tab_settings_search" style="display:none;"><a href="#">Search</a></div>');
   $pseudoTab.find('a').on('click', function(e) {
      e.preventDefault();
      $search.val('');
      clearSearchFilter();
      $search.focus();
   });
   $tabBar.append($pseudoTab);

   function rememberActiveTab() {
      // Detect the tab the framework had marked active (it sets inline display:block).
      var $active = $form.find('.tab_content').filter(function(){ return this.style.display === 'block'; }).first();
      if (!$active.length) {
         var hash = (window.location.hash || '').replace('#','');
         $active = hash ? $('#' + hash + '.tab_content') : $form.find('.tab_content').first();
      }
      savedActiveTab = $active.attr('id') || null;
   }
   function clearSearchFilter() {
      $form.removeClass('searching');
      $form.find('.tab_content').css('display', '');
      $form.find('fieldset, fieldset > div, .tab_content > div').css('display', '');
      $count.hide().text('');
      // Restore selection state: hide pseudo-tab, re-mark the previously-active tab.
      $pseudoTab.removeClass('tab-selected').hide();
      if (savedActiveTab) {
         $('#' + savedActiveTab + '.tab_content').css('display', 'block');
         $tabBar.find('.tab').removeClass('tab-selected');
         $('#tab_' + savedActiveTab).addClass('tab-selected');
      }
   }
   function applySearchFilter(q) {
      q = (q || '').trim().toLowerCase();
      if (!q) { clearSearchFilter(); return; }
      if (!$form.hasClass('searching')) rememberActiveTab();
      $form.addClass('searching');
      // Activate the pseudo-tab; deselect real tabs.
      $tabBar.find('.tab').removeClass('tab-selected');
      $pseudoTab.show().addClass('tab-selected');
      var matchCount = 0;
      $form.find('.tab_content').each(function() {
         var $tab = $(this);
         var legend = '';
         var anyTabMatch = false;
         $tab.find('fieldset').each(function() {
            var $fs = $(this);
            legend = $fs.find('> legend').text().toLowerCase();
            var anyFsMatch = false;
            $fs.find('> div').each(function() {
               var $row = $(this);
               var labelText = $row.find('label').text().toLowerCase();
               var smallText = $row.find('small').text().toLowerCase();
               var idHint    = ($row.find('input,select,textarea').attr('id') || '').toLowerCase();
               var hay = labelText + ' ' + smallText + ' ' + idHint + ' ' + legend;
               if (hay.indexOf(q) !== -1) {
                  $row.css('display', '');
                  anyFsMatch = true;
                  matchCount++;
               } else {
                  $row.hide();
               }
            });
            if (anyFsMatch) {
               $fs.css('display', '');
               anyTabMatch = true;
            } else {
               $fs.hide();
            }
         });
         // Also handle non-fieldset rows (rare)
         $tab.children('div, p').not('[id]').each(function() {
            var $r = $(this);
            if ($r.text().toLowerCase().indexOf(q) !== -1) { $r.css('display',''); anyTabMatch = true; matchCount++; }
            else $r.hide();
         });
         $tab.css('display', anyTabMatch ? 'block' : 'none');
      });
      $count.text(matchCount + ' match' + (matchCount === 1 ? '' : 'es')).show();
   }
   var searchTimer;
   $search.on('input', function() {
      var v = this.value;
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function(){ applySearchFilter(v); }, 80);
   });
   $search.on('keydown', function(e) {
      if (e.key === 'Escape') { this.value=''; clearSearchFilter(); }
   });

});
{/literal}</script>


