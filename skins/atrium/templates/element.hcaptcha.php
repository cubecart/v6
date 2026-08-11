{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Third-party captcha widget embed. $ga_fid is appended to the element id and
 * the JS callback name so multiple widgets can coexist on one page.
 * The {literal} interleaving around the callback name is load-bearing.
 *}
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