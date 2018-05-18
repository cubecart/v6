<link href="//fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet" type='text/css'>
{assign var=css_input value=[   'skins/{$SKIN_FOLDER}/css/normalize.css',
                                'skins/{$SKIN_FOLDER}/css/foundation.css',
                                'skins/{$SKIN_FOLDER}/css/cubecart.css',
                                'skins/{$SKIN_FOLDER}/css/cubecart.common.css',
                                'skins/{$SKIN_FOLDER}/css/cubecart.helpers.css',
                                'skins/{$SKIN_FOLDER}/css/jquery.bxslider.css',
                                'skins/{$SKIN_FOLDER}/css/jquery.chosen.css']}
{foreach from=$CSS key=css_keys item=css_files}
    {$css_input[] = $css_files}
{/foreach}
{if !empty($SKIN_SUBSET)}
    {$css_input[] = 'skins/{$SKIN_FOLDER}/css/cubecart.{$SKIN_SUBSET}.css'}
{/if}
{combine input=$css_input output='cache/css.{$SKIN_FOLDER}.css' age='604800' debug=$CONFIG.debug||!$CONFIG.cache}