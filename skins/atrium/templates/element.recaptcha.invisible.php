{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ INTENTIONALLY EMPTY — CAPABILITY MARKER, NOT A TEMPLATE. DO NOT DELETE.
 *
 * Nothing includes or fetches this file. Its only consumer is the
 * $unavailable_captchas file_exists() probe in admin/sources/settings.index.inc.php.
 * Delete it and the store settings screen tells the merchant this skin does not
 * support "reCaptcha v2 - Invisible", while mode 3 in fact works.
 *
 * ⚠ Do not try to make the probes logical — core's labels do not match what the
 * templates do. The probe on content.recaptcha.head.php is labelled "v2 - Tickbox"
 * yet that file drives mode 3 (Invisible); this file's probe is labelled
 * "v2 - Invisible" and guards nothing; hCaptcha and Turnstile are correct; and
 * mode 2's real dependency, element.recaptcha.php, is not probed at all.
 *}
