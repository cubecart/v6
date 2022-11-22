<meta charset="{$CHARACTER_SET}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="{if isset($META_DESCRIPTION)}{$META_DESCRIPTION}{/if}">
<meta name="robots" content="index, follow">
<meta name="generator" content="cubecart">
{if $FBOG}<meta property="og:image" content="{$PRODUCT.thumbnail}">
<meta property="og:url" content="{$VAL_SELF}">{/if}
{if $LANGUAGES}
{foreach from=$LANGUAGES item=language}
<link rel="alternate" href="{$language.url}" hreflang="{if $language.code==$CONFIG.default_language}x-default{else}{$language.code}{/if}">
{/foreach}
{/if}