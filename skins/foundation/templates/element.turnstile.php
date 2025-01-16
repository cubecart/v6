{if empty($CONFIG.recaptcha_public_key) || empty($CONFIG.recaptcha_secret_key)}
<p>{$LANG.form.recaptcha_key_not_set}</p>
{else}
<div class="cf-turnstile" style="text-align: center;" data-sitekey="{$CONFIG.recaptcha_public_key}"></div>
{/if}