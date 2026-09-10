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
 * ⚠ THE API NAMES BELOW ARE v5 (this file loads javascript-components@5.0.0
 * when the merchant has a w3w_user_key) AND ARE NOT INTERCHANGEABLE WITH v3's:
 *   clip_to_country       underscores — the hyphenated form is simply ignored,
 *                         so suggestions silently stop being clipped
 *   selected_suggestion   the event; "select" never fires, which left the
 *                         posted field empty however the customer typed
 *   e.detail.suggestion.words   not e.detail
 *   initial_value         how an existing address is prefilled; the component
 *                         has no `value` attribute (that is internal state)
 *}
{* The SDK, loaded here rather than in the head so it only ever arrives on a
   page that has an address form. checkout renders this template twice (billing
   and delivery), so the flag stops the second include emitting a second copy:
   loading the v5 bundle twice throws on the repeated customElements.define().

   scope='global' is required. A plain {assign} is local to this include, so the
   second copy would not see the flag set by the first.

   defer/async is safe: the inline script below only calls addEventListener on
   the element, which works before the custom element upgrades. *}
{if !isset($cc_w3w_sdk)}
{assign var='cc_w3w_sdk' value=true scope='global'}
{if !empty($CONFIG.w3w_user_key)}
<script type="module" async src="https://cdn.what3words.com/javascript-components@5.0.0/dist/what3words/what3words.esm.js"></script>
<script nomodule async src="https://cdn.what3words.com/javascript-components@5.0.0/dist/what3words/what3words.js"></script>
{elseif !empty($CONFIG.w3w)}
<script defer src="https://assets.what3words.com/sdk/v3.1/what3words.js?key={$CONFIG.w3w}"></script>
{/if}
{/if}
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
