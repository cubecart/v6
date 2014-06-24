<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>

<script type="text/javascript" >

    $(document).ready(function() 
    {

	    if ({$MODULE.testMode}) {
	        $("div#test_credentials").show();
	        $("div#production_credentials").hide();
	        $("div#ca_credentials").hide();
	    } else {
	        $("div#production_credentials").show();
	    	
	    	switch ($('select[name="module[mode]"] option:selected').text())
	    	{
	        	case "{$LANG.iridiumcorp.api}":
	        		$("div#ca_credentials").show();
	    			break;
				default :
					break;
			}
			
	        $("div#test_credentials").hide();
	    }

	    $("div span img[rel=#testMode]").click(function () {
	
	        switch ($(this).attr('alt').toLowerCase())
	        {
	            case "enable":
	                $("div#test_credentials").show();
	                $("div#production_credentials").hide();
	                $("div#ca_credentials").hide();
	
	                break;
	            case "disable":
	                $("div#test_credentials").hide();
	                $("div#production_credentials").show();
					switch ($('select[name="module[mode]"] option:selected').text())
			    	{
			        	case "{$LANG.iridiumcorp.api}":
			        		$("div#ca_credentials").show();
			    			break;
						default :
							break;
					}
	                break;
	        }
	    });

		switch ($('select[name="module[mode]"] option:selected').text())
	    {
	        case "{$LANG.iridiumcorp.api}":
	        
	            $(".hpfVariables").hide();
	            $(".trVariables").hide();
	            $(".apiVariables").show();
	        	break;
	        	
	        case "{$LANG.iridiumcorp.tr}":
	        
	            $(".apiVariables").hide();
	            $(".hpfVariables").hide();
	            $(".trVariables").show();
	            break;
	
	        case "{$LANG.iridiumcorp.hpf}":
	            
	            $(".apiVariables").hide();
	            $(".trVariables").hide();
	            $(".hpfVariables").show();
	            break;
	    }
    
	    $('select[name="module[mode]"]').change(function () {
	
	        switch ($("option:selected", this).text())
	        {
	            case "{$LANG.iridiumcorp.api}":
	        
		            $(".hpfVariables").hide();
		            $(".trVariables").hide();
		            $(".apiVariables").show();
		        	break;
		        	
		        case "{$LANG.iridiumcorp.tr}":
		        
		            $(".apiVariables").hide();
		            $(".hpfVariables").hide();
		            $(".trVariables").show();
		            break;
		
		        case "{$LANG.iridiumcorp.hpf}":
		            
		            $(".apiVariables").hide();
		            $(".trVariables").hide();
		            $(".hpfVariables").show();
		            break;
	        }
	    });
	});
</script>

<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
    <div id="IridiumCorp" class="tab_content">
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
                <label for="mode">{$LANG.iridiumcorp.mode}</label>
                <span>
                    <select name="module[mode]" id="mode">
                        <option value="api" {$SELECT_mode_api}>{$LANG.iridiumcorp.api}</option>
                        <option value="hpf" {$SELECT_mode_hpf}>{$LANG.iridiumcorp.hpf}</option>
                        <option value="tr" {$SELECT_mode_tr}>{$LANG.iridiumcorp.tr}</option>
                    </select>
                </span>
            </div>
            <div class="apiVariables">
                <label for="crt">{$LANG.iridiumcorp.crt}</label>
                <span>
                    <input type="hidden" name="module[crt]" id="crt" class="toggle" value="{if isset($MODULE.crt)}{$MODULE.crt}{else}1{/if}" />
                </span>
            </div>
            <div id="test_credentials">
                <div>
                    <label for="mid_test">{$LANG.iridiumcorp.merchant_id_test}</label>
                    <span><input name="module[mid_test]" id="mid_test" class="textbox" type="text" value="{$MODULE.mid_test}" /></span>
                </div>
                <div>
                    <label for="pass_test">{$LANG.iridiumcorp.password_test}</label>
                    <span><input name="module[pass_test]" id="pass_test" class="textbox" type="text" value="{$MODULE.pass_test}" /></span>
                </div>
                <div>
	                <label for="ca_test">{$LANG.iridiumcorp.ca_test}</label>
	                <span>
	                    <input type="hidden" name="module[ca_test]" id="ca_test" class="toggle" value="{$MODULE.ca_test}" />
	                </span>
               	</div> 
            </div>
            <div id="production_credentials">
                <div>
                    <label for="mid_prod">{$LANG.iridiumcorp.merchant_id_prod}</label>
                    <span>
                        <input name="module[mid_prod]" id="mid_prod" class="textbox" type="text" value="{$MODULE.mid_prod}" />
                    </span>
                </div>
                <div>
                    <label for="pass_prod">{$LANG.iridiumcorp.password_prod}</label>
                    <span>
                        <input name="module[pass_prod]" id="pass_prod" class="textbox" type="text" value="{$MODULE.pass_prod}" />
                    </span>
                </div>
                <div id="ca_credentials" class="apiVariables">
                    <div>
                        <label for="mid_ca">{$LANG.iridiumcorp.merchant_id_ca}</label>
                        <span>
                            <input name="module[mid_ca]" id="mid_ca" class="textbox" type="text" value="{$MODULE.mid_ca}" />
                        </span>
                    </div>
                    <div>
                        <label for="pass_ca">{$LANG.iridiumcorp.password_ca}</label>
                        <span>
                            <input name="module[pass_ca]" id="pass_ca" class="textbox" type="text" value="{$MODULE.pass_ca}" />
                        </span>
                    </div>
                </div>
            </div>
            <div id="hpfVariables">
                <div class="hpfVariables trVariables">
                    <label for="hpfPreSharedKey">{$LANG.iridiumcorp.hpfPreSharedKey}</label>
                    <span><input name="module[hpfPreSharedKey]" id="hpfPreSharedKey" class="textbox" type="text" value="{$MODULE.hpfPreSharedKey}" /></span>
                </div>
                <div class="hpfVariables trVariables">
                    <label for="hpfHashMethod">{$LANG.iridiumcorp.hpfHashMethod}</label>
                    <span>
                        <select name="module[hpfHashMethod]">
                            <option value="SHA1" {$SELECT_hpfHashMethod_SHA1}>{$LANG.iridiumcorp.hmSHA1}</option>
                            <option value="MD5" {$SELECT_hpfHashMethod_MD5}>{$LANG.iridiumcorp.hmMD5}</option>
                            <option value="HMACSHA1" {$SELECT_hpfHashMethod_HMACSHA1}>{$LANG.iridiumcorp.hmHMACSHA1}</option>
                            <option value="HMACMD5" {$SELECT_hpfHashMethod_HMACMD5}>{$LANG.iridiumcorp.hmHMACMD5}</option>
                        </select>
                    </span>
                </div>
                <div class="hpfVariables">
                    <label for="hpfResultDeliveryMethod">{$LANG.iridiumcorp.hpfResultDeliveryMethod}</label>
                    <span>
                        <select name="module[hpfResultDeliveryMethod]">
                            <option value="POST" {$SELECT_hpfResultDeliveryMethod_POST}>{$LANG.iridiumcorp.rdmPost}</option>
                            <option value="SERVER" {$SELECT_hpfResultDeliveryMethod_SERVER}>{$LANG.iridiumcorp.rdmServer}</option>
                            <option value="SERVER_PULL" {$SELECT_hpfResultDeliveryMethod_SERVER_PULL}>{$LANG.iridiumcorp.rdmServer_Pull}</option>
                        </select>
                    </span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfCV2Mandatory">{$LANG.iridiumcorp.hpfCV2Mandatory}</label>
                    <span><input type="hidden" name="module[hpfCV2Mandatory]" id="hpfCV2Mandatory" class="toggle" value="{if isset($MODULE.hpfCV2Mandatory)}{$MODULE.hpfCV2Mandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfAddress1Mandatory">{$LANG.iridiumcorp.hpfAddress1Mandatory}</label>
                    <span><input type="hidden" name="module[hpfAddress1Mandatory]" id="hpfAddress1Mandatory" class="toggle" value="{if isset($MODULE.hpfAddress1Mandatory)}{$MODULE.hpfAddress1Mandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfCityMandatory">{$LANG.iridiumcorp.hpfCityMandatory}</label>
                    <span><input type="hidden" name="module[hpfCityMandatory]" id="hpfCityMandatory" class="toggle" value="{if isset($MODULE.hpfCityMandatory)}{$MODULE.hpfCityMandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfPostCodeMandatory">{$LANG.iridiumcorp.hpfPostCodeMandatory}</label>
                    <span><input type="hidden" name="module[hpfPostCodeMandatory]" id="hpfPostCodeMandatory" class="toggle" value="{if isset($MODULE.hpfPostCodeMandatory)}{$MODULE.hpfPostCodeMandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfStateMandatory">{$LANG.iridiumcorp.hpfStateMandatory}</label>
                    <span><input type="hidden" name="module[hpfStateMandatory]" id="hpfStateMandatory" class="toggle" value="{if isset($MODULE.hpfStateMandatory)}{$MODULE.hpfStateMandatory}{else}{1}{/if}" /></span>
                </div>
                <div class="hpfVariables" >
                    <label for="hpfCountryMandatory">{$LANG.iridiumcorp.hpfCountryMandatory}</label>
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