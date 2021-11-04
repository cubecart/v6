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
{if $RECAPTCHA=='2'}
<div class="row">
   <div class="medium-8 columns">
        <div class="g-recaptcha" id="RecaptchaField{$ga_fid}"></div>
        <script type="text/javascript">
        {literal}
        var reCaptchaCallback = function() {
            var gr_exists = document.getElementById("RecaptchaField");
            if(gr_exists){
                grecaptcha.render('RecaptchaField', {'sitekey' : '{/literal}{if empty($CONFIG.recaptcha_public_key)}6LdEQRMdAAAAAJkDyPLs0pD2V6EoWf-XDpggiNNp{else}{$CONFIG.recaptcha_public_key}{/if}{literal}'});
            }
            {/literal}{if $ga_fid}{literal}grecaptcha.render('RecaptchaField{/literal}{$ga_fid}{literal}', {'sitekey' : '{/literal}{if empty($CONFIG.recaptcha_public_key)}6LdEQRMdAAAAAJkDyPLs0pD2V6EoWf-XDpggiNNp{else}{$CONFIG.recaptcha_public_key}{/if}{literal}'});{/literal}{/if}{literal}
        };
        {/literal}
        </script>
    </div>
</div>
{/if}