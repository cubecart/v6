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
 * ⚠ ALWAYS the v5 components, whichever key the store has. It used to load the
 * v3.1 SDK for stores on the auto-provisioned partner key while still rendering
 * v5 markup and listening for v5 events, so on those stores the widget produced
 * nothing the form could post and the what3words address was silently dropped
 * on every save. The partner key works against the same API the v5 components
 * call, so it is simply passed as api_key.
 *
 * ⚠ THE API NAMES BELOW ARE v5 AND ARE NOT INTERCHANGEABLE WITH v3's:
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
<script type="module" async src="https://cdn.what3words.com/javascript-components@5.0.0/dist/what3words/what3words.esm.js"></script>
<script nomodule async src="https://cdn.what3words.com/javascript-components@5.0.0/dist/what3words/what3words.js"></script>
{/if}
{* ⚠ The component WRAPS A SLOTTED INPUT. Left empty it hydrates to a 30px box
   with nothing to type in, which is why what3words addresses were never being
   captured: there was no field, so no suggestion was ever selected and the
   hidden value below stayed empty through every save.

   The slotted input carries no name, so it is never posted. The posted value is
   the hidden field, written by the selected_suggestion handler below, which
   keeps what reaches the server to a real what3words address rather than
   whatever half-typed text was in the box. *}
<div class="w3w-field relative">
   {* The /// is a real element in front of the field, not part of its value:
      the component owns the value and you cannot colour three characters of it.
      pointer-events-none so clicking the slashes still focuses the input, and
      z-10 because the component wraps the input in its own positioned box,
      which otherwise paints over the slashes and hides them entirely. *}
   <span aria-hidden="true" class="w3w-slashes pointer-events-none absolute inset-y-0 start-3 z-10 flex items-center">///</span>
   <what3words-autosuggest api_key="{if !empty($CONFIG.w3w_user_key)}{$CONFIG.w3w_user_key}{else}{$CONFIG.w3w}{/if}" id="{$as_id}" initial_value="{$value}">
      <input type="text" id="{$input_id}_visible" class="w-full" autocomplete="off" placeholder="{$LANG.address.w3w_address|default:'what3words address'}"{if $value} value="{$value}"{/if}>
   </what3words-autosuggest>
</div>
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

   var visible = document.getElementById('{$input_id}_visible');
   if (visible) {
      /* The component owns the value and keeps putting its own /// prefix back:
         on focus, on selection, and after its own reformatting. The slashes are
         already drawn in front of the box, so left alone the customer sees six.

         ⚠ Every one of these listeners is needed. Setting a value from script
         fires no input event, so the focus case is invisible to an input
         listener, and the component writes AFTER its own handlers run, hence
         the deferral to the next tick. */
      var strip = function () {
         if (visible.value.indexOf('///') === 0) {
            var caret = visible.selectionStart;
            visible.value = visible.value.slice(3);
            if (typeof caret === 'number' && visible === document.activeElement) {
               caret = Math.max(0, caret - 3);
               try { visible.setSelectionRange(caret, caret); } catch (e) {}
            }
         }
      };
      var stripSoon = function () { window.setTimeout(strip, 0); };
      strip();
      ['focus', 'click', 'input', 'keyup', 'change', 'blur'].forEach(function (name) {
         visible.addEventListener(name, stripSoon);
      });
      as.addEventListener('selected_suggestion', stripSoon);

      /* Clearing the box clears the saved address. Without this, emptying the
         field left the previously chosen address in the hidden input and it was
         silently saved again. */
      visible.addEventListener('input', function () {
         if (!visible.value.trim() && field) field.value = '';
      });
   }
})();
</script>
