<link rel="preconnect" href="https://rsms.me/">
<link rel="stylesheet" href="https://rsms.me/inter/inter.css">
{assign var=css_input value=['skins/{$SKIN_FOLDER}/css/tailwind.css']}
{foreach from=$CSS key=css_keys item=css_files}
    {$css_input[] = $css_files}
{/foreach}
{* if !empty($SKIN_SUBSET)}
    {$css_input[] = 'skins/{$SKIN_FOLDER}/css/tailwind.{$SKIN_SUBSET}.css'}
{/if *}
{combine input=$css_input output='cache/css.{$SKIN_FOLDER}{$SKIN_SUBSET}.css' age='604800' debug=$CONFIG.debug||!$CONFIG.cache}