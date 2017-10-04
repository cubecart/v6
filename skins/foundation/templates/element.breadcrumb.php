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
<div id="element-breadcrumbs">
   {if $CRUMBS}
   <ul class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">
      <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
         <a itemprop="item" href="{$STORE_URL}">
            <span class="show-for-small-only">
               <svg class="icon"><use xlink:href="#icon-home"></use></svg>
            </span>
            <span class="show-for-medium-up" itemprop="name">{$LANG.common.home}</span>
         </a>
         <meta itemprop="position" content="1" />
      </li>
      {foreach from=$CRUMBS item=crumb name=crumbposition}
      {assign var="position" value=$smarty.foreach.crumbposition.iteration+1}
      <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
         <a itemprop="item" href="{$crumb.url}">
            <span itemprop="name">{$crumb.title}</span>
         </a>
         <meta itemprop="position" content="{$position}" />
      </li>
      {/foreach}
   </ul>
   {else}
   <div class="thickpad-top"></div>
   {/if}
</div>