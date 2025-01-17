{if empty($CONFIG.recaptcha_public_key) || empty($CONFIG.recaptcha_secret_key)}
<p>{$LANG.form.recaptcha_key_not_set}</p>
{else}
<div class="cf-turnstile" id="cf-turnstile{$ga_fid}" data-sitekey="{$CONFIG.recaptcha_public_key}"></div>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback{$ga_fid}&render=explicit" async defer></script>
<script>
{literal}
var onloadTurnstileCallback{/literal}{$ga_fid}{literal} = function() {
  turnstile.render("#cf-turnstile{/literal}{$ga_fid}{literal}", {
    sitekey: "{/literal}{$CONFIG.recaptcha_public_key}{literal}"
  });
};
{/literal}
</script>
{/if}