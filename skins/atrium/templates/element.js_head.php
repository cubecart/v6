{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ jQuery is a COMPATIBILITY SHIM — Atrium's own behaviour is Alpine and uses
 * none of it. It cannot be dropped: third-party plugins inject raw jQuery into
 * <head> through class.gui.head_js and through
 * class.cubecart.display_basket.alternate.
 *
 * It IS deferred, though, which the earlier note here said was impossible. What
 * plugins actually do is call jQuery from inside a DOMContentLoaded handler, and
 * that fires AFTER deferred scripts have run, so they are unaffected. Every
 * jQuery call paypal_commerce makes is inside one.
 *
 * The stub below covers the one pattern defer really does break: a plugin
 * emitting <script>$(function(){...})</script> inline in the body, where $ would
 * be undefined at parse time. Those callbacks are queued and replayed on ready.
 * A parse-time call that is NOT a ready callback, $('#x').hide() say, still
 * fails — as it largely would anyway, the element rarely existing yet — but it
 * warns first instead of dying silently.
 *
 * 3.7.1 + migrate, not 4.x: jQuery 4 removes the long-deprecated APIs those
 * plugins still call. Not slim either — slim omits $.ajax and the effects
 * methods, which are what they use most.
 *
 * Plain <script> tags rather than {combine}: already minified, and re-running
 * JSMin over vendor code risks corruption for no gain.
 *}
{literal}<script>
(function () {
   if (window.jQuery) return;
   var queue = [];
   var stub = function (fn) {
      if (typeof fn === 'function') { queue.push(fn); return stub; }
      if (window.console && window.console.warn) {
         window.console.warn('jQuery was used before it finished loading. Atrium defers jQuery; call it from a DOMContentLoaded handler or $(function(){}).');
      }
      return stub;
   };
   window.$ = window.jQuery = stub;
   document.addEventListener('DOMContentLoaded', function () {
      var real = window.jQuery;
      if (!real || real === stub) return;
      for (var i = 0; i < queue.length; i++) { real(queue[i]); }
      queue.length = 0;
   });
}());
</script>{/literal}
<script defer src="{$ROOT_PATH}skins/{$SKIN_FOLDER}/js/vendor/jquery.min.js"></script>
<script defer src="{$ROOT_PATH}skins/{$SKIN_FOLDER}/js/vendor/jquery-migrate.min.js"></script>
<script>window.CC_ROOT_PATH = "{$ROOT_PATH}";</script>
{* Plugin-injected head JS. MUST come after jQuery. *}
{foreach from=$HEAD_JS item=js}{$js}{/foreach}
{* what3words is NOT loaded here any more. The SDK is ~200KB of third party
   JavaScript that only the address forms use, and it was fetching on every page
   of the store, the v3 path without even async. element.w3w.php loads it now, so
   it arrives exactly where the widget does, including anywhere a plugin puts
   one. See the load-once guard in that file. *}
