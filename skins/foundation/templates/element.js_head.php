{assign var=js_head value=[ 'skins/{$SKIN_FOLDER}/js/vendor/modernizr.js',
                            'skins/{$SKIN_FOLDER}/js/vendor/jquery.js']}
{combine input=$js_head output='cache/js_head.{$SKIN_FOLDER}.js' age='604800' debug=$CONFIG.debug||!$CONFIG.cache}
{foreach from=$HEAD_JS item=js}{$js}{/foreach}