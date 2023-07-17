{if empty($CONFIG.recaptcha_public_key) || empty($CONFIG.recaptcha_secret_key)}
<p>{$LANG.form.recaptcha_key_not_set}</p>
{else}
<div class="h-captcha" id="hCaptchaField{$ga_fid}" data-sitekey="{$CONFIG.recaptcha_public_key}" data-error-callback="onError"></div>
<script src="https://js.hcaptcha.com/1/api.js?onload=hCaptchaCallback{$ga_fid}&render=explicit" async defer></script>
<script type="text/javascript">{literal}
var hCaptchaCallback{/literal}{$ga_fid}{literal} = function () {
    var hCaptcha{/literal}{$ga_fid}{literal} = hcaptcha.render('hCaptchaField{/literal}{$ga_fid}{literal}', { sitekey: '{/literal}{$CONFIG.recaptcha_public_key}{literal}' });
  };
{/literal}</script>
{/if}