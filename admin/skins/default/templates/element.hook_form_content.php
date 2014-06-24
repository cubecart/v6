<!-- Bring in Tab Content for plugin hooks. -->
{if $HOOK_TAB_CONTENT}
  {foreach from=$HOOK_TAB_CONTENT item=tabfile}
  	  {include file=$tabfile}
  {/foreach}
{/if}