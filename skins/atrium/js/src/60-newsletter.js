/**
 * Atrium — newsletter signup box.
 *
 * HOUSE RULE: no ES6 template literals in this folder. See 00-boot.js.
 *
 * The captcha block is hidden until the email field is focused, so an unused
 * footer widget never loads third-party captcha JS on every page view. Once
 * revealed it stays revealed — re-hiding it would tear down a widget the
 * customer may already have solved.
 */

document.addEventListener('alpine:init', function () {
    window.Alpine.data('ccNewsletter', function () {
        return {
            showCaptcha: false
        };
    });
});
