{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Labels for controls that js/src/14-controls.js BUILDS, so they cannot be
 * written in the template that owns the field. Same pattern as
 * element.validation_messages.php, kept separate because those keys are
 * validator rule names.
 *
 * |html_entity_decode before |json_encode: the language strings are CDATA
 * holding literal entities, and a <script type="application/json"> body is raw
 * text that the HTML parser does not decode.
 *}
<script type="application/json" id="cc-ui-strings">
{ldelim}
"show_password":     {$LANG.common.show_password|default:'Show password'|html_entity_decode|json_encode nofilter},
"hide_password":     {$LANG.common.hide_password|default:'Hide password'|html_entity_decode|json_encode nofilter},
"quantity_increase": {$LANG.common.quantity_increase|default:'Increase quantity'|html_entity_decode|json_encode nofilter},
"quantity_decrease": {$LANG.common.quantity_decrease|default:'Decrease quantity'|html_entity_decode|json_encode nofilter},
"back_to_top":       {$LANG.common.back_to_top|default:'Back to top'|html_entity_decode|json_encode nofilter}
{rdelim}
</script>
