<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

<script type="text/javascript" >
	var jQuery_1_10_2 = jQuery.noConflict(true);
	
    jQuery_1_10_2(document).ready(function() 
    {
    	var testCredentials = jQuery_1_10_2("div#test_credentials");
		var productionCredentials = jQuery_1_10_2("div#production_credentials");
		var caCredentials = jQuery_1_10_2("div#ca_credentials");
		
		var modeSelected = jQuery_1_10_2('select[name="module[mode]"] option:selected');

		console.log(modeSelected);
		
	    if ({if isset($MODULE.testMode)} {$MODULE.testMode} {else} 0 {/if}) {
	        testCredentials.show();
	        productionCredentials.hide();
	        caCredentials.hide();
	    } else {
	        productionCredentials.show();
	    	
	    	switch (modeSelected.text())
	    	{
	        	case "{$LANG.payvector.api}":
	        		caCredentials.show();
	    			break;
				default :
					break;
			}
			
	        testCredentials.hide();
	    }

	    jQuery_1_10_2("div span img[rel=#testMode]").click(function () {
	
	        switch (jQuery_1_10_2(this).attr('alt').toLowerCase())
	        {
	            case "enable":
	                testCredentials.show();
	                productionCredentials.hide();
	                caCredentials.hide();
	
	                break;
	            case "disable":
	                testCredentials.hide();
	                productionCredentials.show();
					switch (modeSelected.text())
			    	{
			        	case "{$LANG.payvector.api}":
			        		caCredentials.show();
			    			break;
						default :
							break;
					}
	                break;
	        }
	    });
	    
	    var hpfVariables = jQuery_1_10_2(".hpfVariables");
	    var trVariables = jQuery_1_10_2(".trVariables");
	    var apiVariables = jQuery_1_10_2(".apiVariables");

		switch (jQuery_1_10_2('select[name="module[mode]"] option:selected').text())
	    {
	        case "{$LANG.payvector.api}":
	        
	            $(".hpfVariables").hide();
	            $(".trVariables").hide();
	            $(".apiVariables").show();
	        	break;
	        	
	        case "{$LANG.payvector.tr}":
	        
	            $(".apiVariables").hide();
	            $(".hpfVariables").hide();
	            $(".trVariables").show();
	            break;
	
	        case "{$LANG.payvector.hpf}":
	            
	            $(".apiVariables").hide();
	            $(".trVariables").hide();
	            $(".hpfVariables").show();
	            break;
	    }
    
	    jQuery_1_10_2('select[name="module[mode]"]').change(function () {
	
	        switch ($("option:selected", this).text())
	        {
	            case "{$LANG.payvector.api}":
	        
		            $(".hpfVariables").hide();
		            $(".trVariables").hide();
		            $(".apiVariables").show();
		        	break;
		        	
		        case "{$LANG.payvector.tr}":
		        
		            $(".apiVariables").hide();
		            $(".hpfVariables").hide();
		            $(".trVariables").show();
		            break;
		
		        case "{$LANG.payvector.hpf}":
		            
		            $(".apiVariables").hide();
		            $(".trVariables").hide();
		            $(".hpfVariables").show();
		            break;
	        }
	    });
	});
</script>

<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
    <div id="PayVector" class="tab_content">
        <h3>{$TITLE}</h3>
        <fieldset>
            <legend>{$LANG.module.cubecart_settings}</legend>
            <div>
                <label for="description">{$LANG.common.description} *</label>
                <span>
                    <input type="text" name="module[desc]" id="description" class="textbox" value="{$MODULE.desc}" />
                </span>
            </div>
            <div>
                <label for="status">{$LANG.common.status}</label>
                <span>
                    <input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" />
                </span>
            </div>
            <div>
                <label for="default">{$LANG.common.default}</label>
                <span>
                    <input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" />
                </span>
            </div>
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
                <label for="testMode">{$LANG.module.mode_test}</label>
                <span>
                    <input type="hidden" name="module[testMode]" id="testMode" class="toggle" value="{$MODULE.testMode}" />
                </span>
            </div>            
            <div id="gateway_mode">
                <label for="mode">{$LANG.payvector.mode}</label>
                <span>
                    <select name="module[mode]" id="mode">
                        <option value="api" {$SELECT_mode_api}>{$LANG.payvector.api}</option>
                        <option value="hpf" {$SELECT_mode_hpf}>{$LANG.payvector.hpf}</option>
                        <option value="tr" {$SELECT_mode_tr}>{$LANG.payvector.tr}</option>
                    </select>
                </span>
            </div>
            <div class="apiVariables">
                <label for="crt">{$LANG.payvector.crt}</label>
                <span>
                    <input type="hidden" name="module[crt]" id="crt" class="toggle" value="{if isset($MODULE.crt)}{$MODULE.crt}{else}1{/if}" />
                </span>
            </div>
            <div id="test_credentials">
                <div>
                    <label for="mid_test">{$LANG.payvector.merchant_id_test}</label>
                    <span><input name="module[mid_test]" id="mid_test" class="textbox" type="text" value="{$MODULE.mid_test}" /></span>
                </div>
                <div>
                    <label for="pass_test">{$LANG.payvector.password_test}</label>
                    <span><input name="module[pass_test]" id="pass_test" class="textbox" type="text" value="{$MODULE.pass_test}" /></span>
                </div>
            </div>
            <div id="production_credentials">
                <div>
                    <label for="mid_prod">{$LANG.payvector.merchant_id_prod}</label>
                    <span>
                        <input name="module[mid_prod]" id="mid_prod" class="textbox" type="text" value="{$MODULE.mid_prod}" />
                    </span>
                </div>
                <div>
                    <label for="pass_prod">{$LANG.payvector.password_prod}</label>
                    <span>
                        <input name="module[pass_prod]" id="pass_prod" class="textbox" type="text" value="{$MODULE.pass_prod}" />
                    </span>
                </div>
            </div>
            <div id="hpfVariables">
                <div class="hpfVariables trVariables">
                    <label for="hpfPreSharedKey">{$LANG.payvector.hpfPreSharedKey}</label>
                    <span><input name="module[hpfPreSharedKey]" id="hpfPreSharedKey" class="textbox" type="text" value="{$MODULE.hpfPreSharedKey}" /></span>
                </div>
                <div class="hpfVariables trVariables">
                    <label for="hpfHashMethod">{$LANG.payvector.hpfHashMethod}</label>
                    <span>
                        <select name="module[hpfHashMethod]">
                            <option value="SHA1" {$SELECT_hpfHashMethod_SHA1}>{$LANG.payvector.hmSHA1}</option>
                            <option value="MD5" {$SELECT_hpfHashMethod_MD5}>{$LANG.payvector.hmMD5}</option>
                            <option value="HMACSHA1" {$SELECT_hpfHashMethod_HMACSHA1}>{$LANG.payvector.hmHMACSHA1}</option>
                            <option value="HMACMD5" {$SELECT_hpfHashMethod_HMACMD5}>{$LANG.payvector.hmHMACMD5}</option>
                        </select>
                    </span>
                </div>
                <div class="hpfVariables">
                    <label for="hpfResultDeliveryMethod">{$LANG.payvector.hpfResultDeliveryMethod}</label>
                    <span>
                        <select name="module[hpfResultDeliveryMethod]">
                            <option value="POST" {$SELECT_hpfResultDeliveryMethod_POST}>{$LANG.payvector.rdmPost}</option>
                            <option value="SERVER" {$SELECT_hpfResultDeliveryMethod_SERVER}>{$LANG.payvector.rdmServer}</option>
                            <option value="SERVER_PULL" {$SELECT_hpfResultDeliveryMethod_SERVER_PULL}>{$LANG.payvector.rdmServer_Pull}</option>
                        </select>
                    </span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfCV2Mandatory">{$LANG.payvector.hpfCV2Mandatory}</label>
                    <span><input type="hidden" name="module[hpfCV2Mandatory]" id="hpfCV2Mandatory" class="toggle" value="{if isset($MODULE.hpfCV2Mandatory)}{$MODULE.hpfCV2Mandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfAddress1Mandatory">{$LANG.payvector.hpfAddress1Mandatory}</label>
                    <span><input type="hidden" name="module[hpfAddress1Mandatory]" id="hpfAddress1Mandatory" class="toggle" value="{if isset($MODULE.hpfAddress1Mandatory)}{$MODULE.hpfAddress1Mandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfCityMandatory">{$LANG.payvector.hpfCityMandatory}</label>
                    <span><input type="hidden" name="module[hpfCityMandatory]" id="hpfCityMandatory" class="toggle" value="{if isset($MODULE.hpfCityMandatory)}{$MODULE.hpfCityMandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfPostCodeMandatory">{$LANG.payvector.hpfPostCodeMandatory}</label>
                    <span><input type="hidden" name="module[hpfPostCodeMandatory]" id="hpfPostCodeMandatory" class="toggle" value="{if isset($MODULE.hpfPostCodeMandatory)}{$MODULE.hpfPostCodeMandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfStateMandatory">{$LANG.payvector.hpfStateMandatory}</label>
                    <span><input type="hidden" name="module[hpfStateMandatory]" id="hpfStateMandatory" class="toggle" value="{if isset($MODULE.hpfStateMandatory)}{$MODULE.hpfStateMandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfCountryMandatory">{$LANG.payvector.hpfCountryMandatory}</label>
                    <span><input type="hidden" name="module[hpfCountryMandatory]" id="hpfCountryMandatory" class="toggle" value="{if isset($MODULE.hpfCountryMandatory)}{$MODULE.hpfCountryMandatory}{else}{1}{/if}" /></span>
                </div>
            </div>
        </fieldset>
        <p>{$LANG.module.description_options}</p>
    </div>
</div>

{$MODULE_ZONES}
<div class="form_control">
    <input type="submit" name="save" value="{$LANG.common.save}" />
</div>

<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>