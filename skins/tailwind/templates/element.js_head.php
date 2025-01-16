<script
  src="https://code.jquery.com/jquery-3.7.1.slim.min.js"
  integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8="
  crossorigin="anonymous"></script>
{assign var=js_head value=['skins/{$SKIN_FOLDER}/js/tailwind.js']}
{combine input=$js_head output='cache/js_head.{$SKIN_FOLDER}.js' age='604800' debug=$CONFIG.debug||!$CONFIG.cache}
{foreach from=$HEAD_JS item=js}{$js}{/foreach}
{if !empty($CONFIG.w3w)}
<script type="module" src="https://cdn.what3words.com/javascript-components@4-latest/dist/what3words/what3words.esm.js"></script>
<script nomodule src="https://cdn.what3words.com/javascript-components@4-latest/dist/what3words/what3words.js?key={$CONFIG.w3w}"></script>
{/if}