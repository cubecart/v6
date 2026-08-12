{* bxslider is retained for legacy stores only. Fresh 6.8.0 installs get the
   JS-free .cc-hero carousel instead, so .bxSlider() below matches nothing --
   but the homepage document is written once at install and never rewritten on
   upgrade, so every store installed before 6.8.0 still has <ul class="bxslider">
   in its database. Removing this breaks all of their homepages. *}
{assign var=js_foot value=[ 'skins/{$SKIN_FOLDER}/js/vendor/jquery.rating.min.js',
                            'skins/{$SKIN_FOLDER}/js/vendor/jquery.validate.js',
                            'skins/{$SKIN_FOLDER}/js/vendor/jquery.cookie.js',
                            'skins/{$SKIN_FOLDER}/js/vendor/jquery.bxslider.js',
                            'skins/{$SKIN_FOLDER}/js/vendor/jquery.chosen.js']}
{foreach from=$BODY_JS item=js}
    {$js_foot[] = $js}
{/foreach}
{foreach from=$JS_SCRIPTS key=k item=script}
    {$js_foot[] = $script}
{/foreach}
{combine input=$js_foot output='cache/js_foot.{$SKIN_FOLDER}.js' age='604800' debug=$CONFIG.debug||!$CONFIG.cache}
<script>{literal}$(document).foundation({equalizer:{equalize_on_stack:true}});$('.bxslider').bxSlider({auto:true,captions:true});$('.chzn-select').chosen({width:"100%",search_contains:true});{/literal}</script>