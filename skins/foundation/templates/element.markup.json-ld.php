<script type="application/ld+json">
{ 
  "@context" : "http://schema.org",
  "@type" : "Organization",
  "legalName" : "{$CONFIG.store_name}",
  "url" : "{$STORE_URL}",
  "contactPoint" : [{
    "@type" : "ContactPoint",
    "url" : "{$CONTACT_URL}",
    "contactType" : "customer service"
  }],
  "logo" : "{$STORE_LOGO}"{if $SOCIAL_LINKS},
"sameAs" : [{foreach from=$SOCIAL_LINKS item=link name=social_links}
"{$link.url}"{if !$smarty.foreach.social_links.last},{else}]{/if}
  {/foreach}
  {/if}
}
</script>
<script type="application/ld+json">
{
  "@context" : "http://schema.org",
  "@type" : "WebSite", 
  "name" : "{$CONFIG.store_name}",
  "url" : "{$STORE_URL}",
  "potentialAction" : {
    "@type" : "SearchAction",
    "target" : "{$STORE_URL}/search.html?search%5Bkeywords%5D={literal}{search_term}{/literal}&_a=category",
    "query-input" : "required name=search_term"
  }                     
}
</script>