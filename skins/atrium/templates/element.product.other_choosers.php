{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ Keep this template, trivial as it looks: element.product.options.php
 * includes it in its final {elseif}, and any plugin adding an option type
 * depends on it existing. $OTHER_CHOOSERS is pre-rendered plugin HTML for the
 * option types this skin does not render natively (CHECKBOX, HIDDEN, PASSWORD,
 * DATEPICKER, FILE) and anything a plugin invents — print it unchanged.
 *}
{foreach from=$OTHER_CHOOSERS item=oc}{$oc}{/foreach}
