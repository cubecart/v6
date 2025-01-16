{if empty($CONFIG.recaptcha_public_key) || empty($CONFIG.recaptcha_secret_key)}
<p>{$LANG.form.recaptcha_key_not_set}</p>
{else}
<div class="g-recaptcha" id="RecaptchaField{$ga_fid}"></div>
<script src="https://www.google.com/recaptcha/api.js?onload=reCaptchaCallback{$ga_fid}&render=explicit" async defer></script>
<script type="text/javascript">
{literal}
var reCaptchaCallback{/literal}{$ga_fid}{literal} = function() {
    {/literal}{if $ga_fid}{literal}grecaptcha.render('RecaptchaField{/literal}{$ga_fid}{literal}', {'sitekey' : '{/literal}{$CONFIG.recaptcha_public_key}{literal}'});{/literal}{/if}{literal}
};
{/literal}
</script>
{/if}