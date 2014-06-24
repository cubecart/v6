<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   <div id="PayPal_Pro" class="tab_content">
      <h3>{$TITLE}</h3>
      <fieldset>
         <legend>{$LANG.module.config_settings}</legend>
         <div><label for="paypal_status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="paypal_status" class="toggle" value="{$MODULE.status}" /></span></div>
         <div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>
         <div>
            <label for="scope">{$LANG.module.scope}</label>
            <span>
            <select name="module[scope]">
            <option value="both" {$SELECT_scope_both}>{$LANG.module.both}</option>
            <option value="main" {$SELECT_scope_main}>{$LANG.module.main}</option>
            <option value="mobile" {$SELECT_scope_mobile}>{$LANG.module.mobile}</option>
            </select>
            </span>
         </div>
         <div>
            <label for="paypal_mode">{$LANG.paypal_pro.mode}</label>
            <span>
            <select name="module[mode]" id="paypal_mode" class="required">
            {foreach from=$modes item=mode}<option value="{$mode.value}"{$mode.selected}>{$mode.title}</option>{/foreach}
            </select>
            </span>
         </div>
         <div>
            <label for="ec_mode">{$LANG.paypal_pro.ec_action}</label>
            <span>
            <select name="module[ec_mode]" id="ec_mode">
            {foreach from=$ec_modes item=ec_mode}<option value="{$ec_mode.value}"{$ec_mode.selected}>{$ec_mode.title}</option>{/foreach}
            </select>
            </span>
         </div>
         <div><label for="paypal_username">{$LANG.paypal_pro.api_username}</label><span><input type="text" name="module[username]" id="paypal_username" value="{$MODULE.username}" class="textbox required" /></span></div>
         <div><label for="paypal_password">{$LANG.paypal_pro.api_password}</label><span><input type="text" name="module[password]" id="paypal_password" value="{$MODULE.password}" class="textbox required" /></span></div>
         <div><label for="paypal_signature">{$LANG.paypal_pro.api_signature}</label><span><input type="text" name="module[signature]" id="paypal_signature" value="{$MODULE.signature}" class="textbox required" /></span></div>
         {if $country==826}
         <div><label for="paypal_password">{$LANG.paypal_pro.partner}</label><span><input type="text" name="module[partner]" id="paypal_partner" value="{$MODULE.partner}" class="textbox" /></span></div>
         <div><label for="paypal_vendor">{$LANG.paypal_pro.vendor}</label><span><input type="text" name="module[vendor]" id="paypal_vendor" value="{$MODULE.vendor}" class="textbox" /></span></div>
         {/if}
         <div><label for="amex">{$LANG.paypal_pro.amex}</label><span><input type="hidden" name="module[amex]" id="amex" class="toggle" value="{$MODULE.amex}" /></span></div>
         <div>
            <label for="paypal_mode">{$LANG.paypal_pro.gateway}</label>
            <span>
            <select name="module[gateway]" id="paypal_gateway">
            {foreach from=$gateways item=gateway}<option value="{$gateway.value}"{$gateway.selected}>{$gateway.title}</option>{/foreach}
            </select>
            </span>
         </div>
         <!-- Inline Checkout Depreciated for now
         <div>
            <label for="paypal_action">{$LANG.paypal_pro.payment_action}</label>
            <span>
            <select name="module[paymentAction]" id="paypal_action">
            {foreach from=$actions item=action}<option value="{$action.value}"{$action.selected}>{$action.title}</option>{/foreach}
            </select>
            </span>
         </div>
         -->
         <div>
            <label for="paypal_confirmed">{$LANG.paypal_pro.confirmed_address}</label>
            <span>
            <select name="module[confAddress]" id="paypal_confirmed">
            {foreach from=$confirmed item=confirm}<option value="{$confirm.value}"{$confirm.selected}>{$confirm.title}</option>{/foreach}
            </select>
            </span>
         </div>
      </fieldset>
      <fieldset>
         <legend>{$LANG.paypal_pro.styling}</legend>
         <div><label for="cartborder_color">{$LANG.paypal_pro.cartborder_color}:</label><span>#<input type="text" name="module[cartborder_color]" id="cartborder_color" value="{$MODULE.cartborder_color}" class="textbox" /></span></div>
         <div><small>{$LANG.paypal_pro.cartborder_color_example}</small></div>
         <div><label for="logoimg">{$LANG.paypal_pro.logoimg}:</label><span><input type="text" name="module[logoimg]" id="logoimg" value="{$MODULE.logoimg}" class="textbox" /></span></div>
         <div><small>{$LANG.paypal_pro.logoimg_example}</small></div>
      </fieldset>
      {if $CONFIG.store_country==840 || $CONFIG.store_country==826}
      <fieldset>
         <legend>{$LANG.paypal_pro.cubecart_styling}</legend>
         <div><label for="acceptance_mark">{$LANG.paypal_pro.acceptance_mark}</label><span><input type="hidden" name="module[acceptance_mark]" id="acceptance_mark" class="toggle" value="{$MODULE.acceptance_mark}" /></span></div>
         <div><img src="modules/plugins/PayPal_Pro/images/acceptance_marks_{if $CONFIG.store_country==840}US{else}UK{/if}.png" /></div>
      </fieldset>
      {/if}
      
      {if $BML}
      <fieldset>
         <legend>{$LANG.paypal_pro.financing}</legend>
         <div><label for="paypal_billmelater">{$LANG.paypal_pro.billmelater}</label><span><input type="hidden" name="module[billmelater]" id="paypal_billmelater" class="toggle" value="{$MODULE.billmelater}" /></span></div>
      </fieldset>
      {/if}
      {if $MODULE.mode!=2}
      <fieldset id='3ds'>
         <legend>{$LANG.paypal_pro.settings_3ds}</legend>
         <div><label for="paypal_status_3ds">{$LANG.common.status}</label><span><input type="hidden" name="module[3ds_status]" id="paypal_status_3ds" class="toggle" value="{$MODULE.3ds_status}" />&nbsp;</span></div>
         <div><label for="paypal_merchant_3ds">{$LANG.module.merchant_id}</label><span><input type="text" name="module[3ds_merchant]" value="{$MODULE.3ds_merchant}" class="textbox" /></span></div>
         <div><label for="paypal_password_3ds">{$LANG.paypal_pro.transaction_password}</label><span><input type="text" name="module[3ds_password]" value="{$MODULE.3ds_password}" class="textbox" /></span></div>
      </fieldset>
      {/if}
      <fieldset id='3ds'>
         <legend>{$LANG.paypal_pro.settings_paypal}</legend>
         <div><label for="paypal_ipn_url">{$LANG.paypal_pro.paypal_ipn_url}</label><span><input type="text" readonly="readonly" name="paypal_ipn_url" id="paypal_ipn_url" class="textbox" value="{$paypal_ipn_url}" /></span></div>
         <p>{$LANG.paypal_pro.paypal_ipn_url_explained}</p>
      </fieldset>
      {if $country==826}
      <p>{$LANG.paypal_pro.payflow_fields}</p>
      {/if}
      <fieldset>
         <legend>IMPORTANT NOTICE</legend>
         CubeCart requires the customers phone number in order to checkout. On returning from an Express Checkout payment CubeCart will display a form to the customers if no phone number is present. To bypass this and speed up the checkout process we recommend that &quot;Contact Telephone Number&quot; is set to &quot;On&quot; in your PayPal account. This can be found under <em>Profile &gt; Website payment settings &gt; Website Payments Standard and Express Checkout &gt; Preferences</em>.
      </fieldset>
   </div>
   {$MODULE_ZONES}
   <div class="form_control">
      <input type="submit" value="{$LANG.common.save}" />
   </div>
   <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>