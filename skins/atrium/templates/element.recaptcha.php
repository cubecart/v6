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