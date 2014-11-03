{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 *}
<!-- Bring in Tab Content for plugin hooks. -->
{if $HOOK_TAB_CONTENT}
  {foreach from=$HOOK_TAB_CONTENT item=tabfile}
  	  {include file=$tabfile}
  {/foreach}
{/if}