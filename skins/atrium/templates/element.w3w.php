{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * what3words address autosuggest. Callers gate it on $CONFIG.w3w and pass
 * value, as_id, input_id, input_name, country_id.
 *
 * ⚠ Two silent contracts. Country clipping reads data-iso off the selected
 * <option> of the country <select> named by $country_id — drop that attribute in
 * the address templates and suggestions quietly stop being narrowed. And
 * admin/sources/settings.index.inc.php file_exists()-probes this filename for its
 * w3w_compatibility flag, so deleting or renaming it makes the settings screen
 * report the skin as not what3words-capable.
 *
 * ⚠ THE API NAMES BELOW ARE v5 (element.js_head.php loads
 * javascript-components@5.0.0) AND ARE NOT INTERCHANGEABLE WITH v3's:
 *   clip_to_country       underscores — the hyphenated form is simply ignored,
 *                         so suggestions silently stop being clipped
 *   selected_suggestion   the event; "select" never fires, which left the
 *                         posted field empty however the customer typed
 *   e.detail.suggestion.words   not e.detail
 *   initial_value         how an existing address is prefilled; the component
 *                         has no `value` attribute (that is internal state)
 *}
<what3words-autosuggest{if !empty($CONFIG.w3w_user_key)} api_key="{$CONFIG.w3w_user_key}"{/if} id="{$as_id}" initial_value="{$value}"></what3words-autosuggest>
{* The component renders its OWN input and does not adopt a slotted one, so the
   posted value lives in this hidden field and is written by the event below.
   A visible <input> here renders a second, empty box above the real one. *}
<input type="hidden" name="{$input_name}" id="{$input_id}" value="{$value}">
<script>
(function () {
   var as = document.getElementById('{$as_id}');
   var country = document.getElementById('{$country_id}');
   var field = document.getElementById('{$input_id}');
   if (!as) return;

   function clip() {
      if (!country) return;
      var opt = country.options[country.selectedIndex];
      var iso = opt ? opt.getAttribute('data-iso') : '';
      if (iso) as.setAttribute('clip_to_country', iso);
   }
   clip();
   if (country) country.addEventListener('change', clip);

   as.addEventListener('selected_suggestion', function (e) {
      var suggestion = e.detail && e.detail.suggestion;
      if (!field || !suggestion || !suggestion.words) return;
      // Core stores and renders the bare words; the /// is added by the templates.
      field.value = String(suggestion.words).replace(/^\/{3}/, '');
   });
})();
</script>
