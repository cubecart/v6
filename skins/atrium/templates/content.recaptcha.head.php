{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ LOAD-BEARING FOR INVISIBLE reCAPTCHA (config recaptcha == 3), and the
 * failure mode is silent: the markup still looks right, the button just never
 * produces a g-recaptcha-response, so every protected form — registration,
 * contact, reviews, newsletter, checkout — is rejected server-side with "The
 * verification code was incorrect." MUST therefore be included by every layout
 * that can contain a protected form: main.php, main.checkout.php,
 * main.stream.php.
 *
 * Contract with the calling templates: the SUBMIT BUTTON carries
 * class="g-recaptcha", a unique id, and data-form-id="<form id>". grecaptcha.render()
 * takes an element ID, so an id-less .g-recaptcha element is skipped silently.
 * On solve the callback submits document.getElementById(data-form-id), falling
 * back to the button's closest form.
 *}
{if $RECAPTCHA=='3'}
<script src="https://www.google.com/recaptcha/api.js?onload=reCaptchaCallback&render=explicit" async defer></script>
<script>
var reCaptchaCallback = function () {
   document.querySelectorAll('.g-recaptcha').forEach(function (el) {
      // grecaptcha.render() takes an element ID; without one there is nothing
      // to render into and the control would silently never produce a token.
      if (!el.id) return;
      // One failure must not abort the loop: without this, a throw on any
      // element leaves every LATER .g-recaptcha button unrendered, and those
      // then submit natively with no token.
      try {
      grecaptcha.render(el.id, {
         'sitekey': '{$CONFIG.recaptcha_public_key}',
         'badge': '{$SKIN_CUSTOM.recaptcha_badge_position}',
         'callback': function () {
            var formId = el.getAttribute('data-form-id');
            var form = formId ? document.getElementById(formId) : el.closest('form');
            if (form) form.submit();
         }
      });
      } catch (e) {
         if (window.console && console.warn) console.warn('reCAPTCHA render failed for #' + el.id, e);
      }
   });
};
</script>
{/if}
