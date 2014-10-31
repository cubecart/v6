/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
<li class="has-dropdown">
   <a href="{$BRANCH.url}" title="{$BRANCH.name}">{$BRANCH.name}</a>
   {if isset($BRANCH.children)}
   <ul class="dropdown">
      <li><label>{$BRANCH.name}</label></li>
      {$BRANCH.children}
   </ul>
   {/if}
</li>