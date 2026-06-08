<meta charset="{$CHARACTER_SET}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="{if isset($META_DESCRIPTION)}{$META_DESCRIPTION}{/if}">
{if in_array($SECTION_NAME, ['account','checkout','login','register','recover','recovery','withdraw','order','receipt','gateway','search','download'])}
<meta name="robots" content="noindex, follow">
{else}
<meta name="robots" content="index, follow">
{/if}
<meta name="generator" content="cubecart">
<meta property="og:type" content="{if $SECTION_NAME == 'product'}product{else}website{/if}"/>
<meta property="og:site_name" content="{$CONFIG.store_name}"/>
{if isset($META_TITLE)}<meta property="og:title" content="{$META_TITLE}"/>{/if}
{if isset($META_DESCRIPTION)}<meta property="og:description" content="{$META_DESCRIPTION}"/>{/if}
<meta property="og:image" content="{if isset($PRODUCT.medium)}{$PRODUCT.medium}{else}{$STORE_LOGO}{/if}"/>
<meta property="og:url" content="{$VAL_SELF}"/>
<meta name="twitter:card" content="summary_large_image">
{if isset($META_TITLE)}<meta name="twitter:title" content="{$META_TITLE}">{/if}
{if isset($META_DESCRIPTION)}<meta name="twitter:description" content="{$META_DESCRIPTION}">{/if}
<meta name="twitter:image" content="{if isset($PRODUCT.medium)}{$PRODUCT.medium}{else}{$STORE_LOGO}{/if}">
{if $LANGUAGES}
{foreach from=$LANGUAGES item=language}
<link rel="alternate" href="{$language.url}" hreflang="{if $language.code==$CONFIG.default_language}x-default{else}{$language.code}{/if}">
{/foreach}
{/if}
