{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Translated validation messages for js/src/50-validate.js.
 *
 * Foundation emits one hidden <div id="validate_*"> per message per template
 * (15 templates carry their own copies). This is a single JSON blob included
 * once from main.php instead — same strings, one place, and no stray divs that
 * a stylesheet has to remember to hide.
 *
 * Keys are RULE names, matched by messageFor() in 50-validate.js:
 *   required maxlength minlength email phone mobile match invalid
 *   emailInUse subscribed terms shipping gateway
 *
 * A single field can override any of these with data-msg-<rule>="..." — used
 * where the generic wording is too vague, e.g. first/last name on register.
 *
 * NOT </script>-escaped by hand: |json_encode handles the quoting, and the
 * strings come from the language pack, not from user input.
 *
 * |html_entity_decode BEFORE |json_encode is required, not cosmetic. The
 * language strings are CDATA holding literal entities ("Terms &amp;amp;
 * Conditions"). Foundation reads its messages out of a hidden <div>, so the
 * HTML parser decodes them on the way in; the content of a
 * <script type="application/json"> is raw text and is NOT entity-decoded, so
 * without this the customer literally sees "Terms &amp;amp; Conditions".
 *}
<script type="application/json" id="cc-validation-messages">
{ldelim}
"required":   {$LANG.form.field_required|html_entity_decode|json_encode nofilter},
"invalid":    {$LANG.form.field_required|html_entity_decode|json_encode nofilter},
"email":      {$LANG.common.error_email_invalid|html_entity_decode|json_encode nofilter},
"phone":      {$LANG.account.error_valid_phone|html_entity_decode|json_encode nofilter},
"mobile":     {$LANG.account.error_valid_mobile_phone|html_entity_decode|json_encode nofilter},
"minlength":  {$LANG.account.error_password_length|html_entity_decode|json_encode nofilter},
"maxlength":  {$LANG.account.error_password_length_max|html_entity_decode|json_encode nofilter},
"match":      {$LANG.account.error_password_mismatch|html_entity_decode|json_encode nofilter},
"emailInUse": {$LANG.account.error_email_in_use|html_entity_decode|json_encode nofilter},
"subscribed": {$LANG.account.error_email_in_use|html_entity_decode|json_encode nofilter},
"terms":      {$LANG.account.error_terms_agree|html_entity_decode|json_encode nofilter},
"shipping":   {$LANG.checkout.shipping_required|html_entity_decode|json_encode nofilter},
"gateway":    {$LANG.gateway.choose_payment|html_entity_decode|json_encode nofilter}
{rdelim}
</script>
