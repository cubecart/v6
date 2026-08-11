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