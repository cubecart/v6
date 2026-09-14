{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * SCRIPT ORDER MATTERS HERE. Read before editing.
 *
 * 1. Plugin JS via {combine}. Plugins drop files into skins/<skin>/js/ expecting
 *    them to be auto-loaded; GUI::__construct() globs them into $JS_SCRIPTS and
 *    this is where they render.
 *    ⚠ The {if $js_foot} guard is required: smarty_print_out() in
 *    function.combine.php raises E_USER_NOTICE 'input cannot be empty' and bails
 *    on an empty array. Atrium ships NO top-level js/*.js — its own bundle lives
 *    in js/vendor/, clear of JSMin — so on a store with no such plugins the
 *    array IS empty.
 *
 * 2. atrium.app.js - js/src/*.js, Alpine plugins, then Alpine core, in that
 *    order (build/bundle-js.sh). Core fires alpine:init on load, so a plugin
 *    bundled after it would register too late.
 *
 * Both are `defer`, so they run in document order after parsing.
 *
 * ⚠ js/vendor/ is NOT auto-globbed — GUI::__construct()'s glob is
 * non-recursive — so every file below needs its tag written out by hand.
 *}
{assign var=js_foot value=[]}
{foreach from=$BODY_JS item=js}{$js_foot[] = $js}{/foreach}
{foreach from=$JS_SCRIPTS item=script}{$js_foot[] = $script}{/foreach}
{if $js_foot}
{combine input=$js_foot output='cache/js_foot.{$SKIN_FOLDER}.js' age='604800' debug=$CONFIG.debug||!$CONFIG.cache}
{/if}

<script defer src="{$ROOT_PATH}skins/{$SKIN_FOLDER}/js/vendor/atrium.app.js?v={$CSS_VERSION}"></script>
