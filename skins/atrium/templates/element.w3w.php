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
 *}
<what3words-autosuggest{if !empty($CONFIG.w3w_user_key)} api_key="{$CONFIG.w3w_user_key}"{/if} id="{$as_id}" value="{$value}">
   <input type="text" name="{$input_name}" id="{$input_id}" value="{$value}">
</what3words-autosuggest>
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
      if (iso) as.setAttribute('clip-to-country', iso);
   }
   clip();
   if (country) country.addEventListener('change', clip);

   as.addEventListener('select', function (e) {
      if (field) field.value = e.detail;
   });
})();
</script>
