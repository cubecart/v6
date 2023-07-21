{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<!-- Bring in Tab Content for plugin hooks. -->
{if $HOOK_TAB_CONTENT}
  {foreach from=$HOOK_TAB_CONTENT item=tabfile}
  	  {include file=$tabfile}
  {/foreach}
{/if}