{assign var=js_head value=[ 'skins/{$SKIN_FOLDER}/js/vendor/modernizr.js',
                            'skins/{$SKIN_FOLDER}/js/vendor/jquery.js']}
{foreach from=$HEAD_JS item=js}
    {$js_head[] = $js}
{/foreach}
{combine input=$js_head output='cache/js_head.{$SKIN_FOLDER}.js' age='604800' debug=false}