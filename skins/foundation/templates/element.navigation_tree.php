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
<li class="has-dropdown">
   <a href="{$BRANCH.url}" title="{$BRANCH.name}">{$BRANCH.name}</a>
   {if isset($BRANCH.children)}
   <ul class="dropdown">
      <li itemprop="name" class="hide-for-large-up"><label itemprop="url" content="{$BRANCH.url}" rel="{$BRANCH.url}">{$BRANCH.name}</label></li>
      {$BRANCH.children}
   </ul>
   {/if}
</li>