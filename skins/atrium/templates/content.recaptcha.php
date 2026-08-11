{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Captcha dispatcher. $RECAPTCHA is the store's `recaptcha` config value,
 * assigned by GUI::recaptchaRequired(). Mode 3 (invisible) is not handled here —
 * it renders from content.recaptcha.head.php instead.
 *
 * ⚠ $ga_fid must be passed by the caller: it is appended to every element id and
 * JS callback name so several widgets can coexist on one page. Omit it and
 * element.recaptcha.php renders nothing at all, while hCaptcha/Turnstile widgets
 * collide on id and callback name — in both cases no token, and the form is
 * rejected with "The verification code was incorrect."
 *}
{if in_array($RECAPTCHA, array('2','4','5'))}
<div class="cc-captcha">
   {if $RECAPTCHA=='2'}
   {include file='templates/element.recaptcha.php' ga_fid=$ga_fid}
   {elseif $RECAPTCHA=='4'}
   {include file='templates/element.hcaptcha.php' ga_fid=$ga_fid}
   {elseif $RECAPTCHA=='5'}
   {include file='templates/element.turnstile.php' ga_fid=$ga_fid}
   {/if}
</div>
{/if}
