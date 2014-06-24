<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
    <div id="Icepay" class="tab_content"
        style="background-image: url(http://192.168.1.76/icepay/branches/wouter/development/webshop_modules/woocommerce/1_6_5_2/dev/wp-content/plugins/woocommerce-icepay/images/icepay-header.png); 
                background-repeat: no-repeat; 
                padding-top: 95px;
                margin-left: 10px;
                background-color: #F5F5F5;
    ">
    <h3 style="margin-bottom: 20px;">Configuration</h3>    
        <fieldset>
            <legend>{$LANG.module.cubecart_settings}</legend>
            <div>
                <label for="status">Enabled</label>
                <input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" />  
            </div>
            <div style="margin: 15px 0;">
                <label for="default">Default</label>
                <input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" />
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; Enable ICEPAY as the default payment method.</div>
            </div>
            <div style="margin: 15px 0;">
                <label for="icepay-paymentmethod">Paymentmethod</label>
                
                <select name="module[paymentmethod]" id="icepay-paymentmethod" style="padding: 5px; width: 345px;">
                    {$paymentmethods}
                </select>
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; The basic mode offers all payment methods and is the recommended setting if you want to provide more than one payment method. 
In the special case you want to offer one single payment method, you can select this from the following list. This one payment method will be shown in your own checkout screen. 
</div>
            </div>
            <div style="margin: 15px 0;">
                <label for="imageEnabled">Show Image</label>
                <input type="hidden" name="module[imageEnabled]" id="imageEnabled" class="toggle" value="{$MODULE.imageEnabled}" />  
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; Show an image instead of text on the checkout page.</div>
            </div>
            <div style="margin: 15px 0;">
                <label for="imageEnabled">Display name for paymentmethod</label>
                <input type="text" name="module[paymentmethodDisplayName]" id="icepay-merchantid" value="{$MODULE.paymentmethodDisplayName}" style="padding: 5px; width: 330px;" />
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; If not filled in, the paymentmethod image will be shown.</div>
            </div>
                
            <div style="margin: 15px 0;">
                <label for="icepay-merchantid" style="padding-top: 10px;">Merchant ID</label>
                <input type="text" name="module[merchantid]" id="icepay-merchantid" value="{$MODULE.merchantid}" style="padding: 5px; width: 330px;" />
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; Copy the Merchant ID from your ICEPAY account.</div>
            </div>
            <div style="margin: 15px 0;">
                <label for="icepay-secretcode" style="padding-top: 10px;">Secretcode</label>
                <input type="text" name="module[secretcode]" id="icepay-secretcode" value="{$MODULE.secretcode}" style="padding: 5px; width: 330px;" />
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; Copy the Secret Code from your ICEPAY account.</div>
            </div>
            <div style="margin: 15px 0;">
                <label for="icepay-merchanturl" style="padding-top: 10px;">Merchant URL</label>
                <input type="text" name="module[merchanturl]" id="icepay-secretcode" value="{$STORE_URL}/index.php?_g=rm&type=gateway&cmd=process&module=Icepay" style="padding: 5px; width: 330px;" />                
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; Copy-Paste this URL to the Success, Error and Postback section of your ICEPAY merchant account.</div>
            </div>
        </fieldset>
                
        <fieldset>
            <legend>Optional Settings</legend>
            <div style="margin: 0 0 15px 0;">
                <label for="icepay-customdescription" style="padding-top: 10px;">Description on transaction statement of customer</label>
                <input type="text" name="module[customdescription]" id="icepay-customdescription" value="{$MODULE.customdescription}" style="padding: 5px; width: 330px;" />
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; Some payment methods allow customized descriptions on the transaction statement. If left empty the Order ID is used. (Max 100 char.)</div>
            </div>
            <div style="margin: 15px 0;">
                <label for="icepay-customiprange" style="padding-top: 10px;">Custom IP Range for IP Check for Postbackk</label>
                <input type="text" name="module[customiprange]" id="icepay-customiprange" value="{$MODULE.customiprange}" style="padding: 5px; width: 330px;" />
                <div style="font-style: italic; margin-left: 5px; font-size: 11px;">&#187; For example a proxy: 1.222.333.444-100.222.333.444 For multiple ranges use a , seperator: 2.2.2.2-5.5.5.5,8.8.8.8-9.9.9.9</div>
            </div>
            <div style="margin: 15px 0;">
                <label for="icepay-logging">Enable logging</label>
                <input type="hidden" name="module[logging]" id="icepay-logging" class="toggle" value="{$MODULE.logging}" />  
                <div style="font-style: italic; margin: 10px 0 0 5px; font-size: 11px;">&#187; Log all icepay data to the /icepay_log folder. This folder needs writing privileges. (CHMOD 777)</div>
            </div>
        </fieldset>

        <div class="form_control">            
            <input type="submit" name="save" value="{$LANG.common.save}" />
            <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
        </div>        
    </div>
</form>
