{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ jQuery is a COMPATIBILITY SHIM — Atrium's own behaviour is Alpine and uses
 * none of it — but it can be neither dropped nor deferred. Third-party plugins
 * inject raw jQuery into <head> through the class.gui.head_js hook, and through
 * class.cubecart.display_basket.alternate, and it must already be defined when
 * those run (paypal_commerce does both).
 *
 * 3.7.1 + migrate, not 4.x: jQuery 4 removes the long-deprecated APIs those
 * plugins still call. Not slim either — slim omits $.ajax and the effects
 * methods, which are what they use most.
 *
 * Plain <script> tags rather than {combine}: already minified, and re-running
 * JSMin over vendor code risks corruption for no gain.
 *}
<script src="{$ROOT_PATH}skins/{$SKIN_FOLDER}/js/vendor/jquery.min.js"></script>
<script src="{$ROOT_PATH}skins/{$SKIN_FOLDER}/js/vendor/jquery-migrate.min.js"></script>
<script>window.CC_ROOT_PATH = "{$ROOT_PATH}";</script>
{* Plugin-injected head JS. MUST come after jQuery. *}
{foreach from=$HEAD_JS item=js}{$js}{/foreach}
{if !empty($CONFIG.w3w_user_key)}
<script type="module" async src="https://cdn.what3words.com/javascript-components@5.0.0/dist/what3words/what3words.esm.js"></script>
<script nomodule async src="https://cdn.what3words.com/javascript-components@5.0.0/dist/what3words/what3words.js"></script>
{elseif !empty($CONFIG.w3w)}
<script src="https://assets.what3words.com/sdk/v3.1/what3words.js?key={$CONFIG.w3w}"></script>
{/if}
