{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<li class="has-dropdown">
   <a href="{$BRANCH.url}" title="{$BRANCH.name}">{$BRANCH.name}</a>
   {if isset($BRANCH.children)}
   <ul class="dropdown">
      <li itemprop="name"><label itemprop="url" content="{$BRANCH.url}" rel="{$BRANCH.url}">{$BRANCH.name}</label></li>
      {$BRANCH.children}
   </ul>
   {/if}
</li>