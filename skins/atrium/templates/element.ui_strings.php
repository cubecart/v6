{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Labels for controls js/src/14-controls.js BUILDS. Separate from
 * element.validation_messages.php, whose keys are validator rule names.
 *
 * |html_entity_decode before |json_encode: the strings are CDATA holding
 * entities, and a JSON script body is raw text the parser does not decode.
 *}
<script type="application/json" id="cc-ui-strings">
{ldelim}
"show_password":     {$LANG.common.show_password|default:'Show password'|html_entity_decode|json_encode nofilter},
"hide_password":     {$LANG.common.hide_password|default:'Hide password'|html_entity_decode|json_encode nofilter},
"quantity_increase": {$LANG.common.quantity_increase|default:'Increase quantity'|html_entity_decode|json_encode nofilter},
"quantity_decrease": {$LANG.common.quantity_decrease|default:'Decrease quantity'|html_entity_decode|json_encode nofilter},
"back_to_top":       {$LANG.common.back_to_top|default:'Back to top'|html_entity_decode|json_encode nofilter},
"pw_strength":       {$LANG.common.password_strength|default:'Password strength'|html_entity_decode|json_encode nofilter},
"pw_levels":         [{$LANG.common.password_weak|default:'Weak'|html_entity_decode|json_encode nofilter},
                      {$LANG.common.password_fair|default:'Fair'|html_entity_decode|json_encode nofilter},
                      {$LANG.common.password_good|default:'Good'|html_entity_decode|json_encode nofilter},
                      {$LANG.common.password_strong|default:'Strong'|html_entity_decode|json_encode nofilter}],
"pw_hint_length":    {$LANG.common.password_hint_length|default:'Longer is stronger.'|html_entity_decode|json_encode nofilter},
"pw_hint_common":    {$LANG.common.password_hint_common|default:'This is a commonly used password.'|html_entity_decode|json_encode nofilter}
{rdelim}
</script>
