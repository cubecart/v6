{assign var=js_head value=[ 'skins/{$SKIN_FOLDER}/js/vendor/modernizr.js',
                            'skins/{$SKIN_FOLDER}/js/vendor/jquery.js']}
{combine input=$js_head output='cache/js_head.{$SKIN_FOLDER}.js' age='604800' debug=$CONFIG.debug||!$CONFIG.cache}
{foreach from=$HEAD_JS item=js}{$js}{/foreach}
{if !empty($CONFIG.w3w_user_key)}
<script type="module" async src="https://cdn.what3words.com/javascript-components@5.0.0/dist/what3words/what3words.esm.js"></script>
<script nomodule async src="https://cdn.what3words.com/javascript-components@5.0.0/dist/what3words/what3words.js"></script>
{elseif !empty($CONFIG.w3w)}
<script src="https://assets.what3words.com/sdk/v3.1/what3words.js?key={$CONFIG.w3w}"></script>
{/if}