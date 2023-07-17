<div class="h-captcha" id="hCaptchaField{$ga_fid}" data-sitekey="{$CONFIG.recaptcha_public_key}" data-size="normal"></div>
<script src="https://js.hcaptcha.com/1/api.js?onload=hCaptchaCallback{$ga_fid}" async defer></script>
<script type="text/javascript">
{literal}
var hCaptchaCallback{/literal}{$ga_fid}{literal} = function() {
    {/literal}{if $ga_fid}{literal}hcaptcha.render('hCaptchaField{/literal}{$ga_fid}{literal}', {'sitekey' : '{/literal}{$CONFIG.recaptcha_public_key}{literal}'});{/literal}{/if}{literal}
};
{/literal}
</script>