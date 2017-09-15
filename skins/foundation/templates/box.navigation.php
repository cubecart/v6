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
{if $CATEGORIES}
{$CATEGORIES}
{else}
<nav class="top-bar category-nav" data-topbar="" id="box-navigation">
  <ul class="title-area">
     <li class="name"></li>
     <li class="toggle-topbar left"><a href="">{$LANG.navigation.title} <svg class="icon"><use xlink:href="#icon-caret-down"></use></svg></a></li>
  </ul>
  <section class="top-bar-section">
     <h5 class="hide">{$LANG.navigation.title}</h5>
     <ul itemscope itemtype="http://www.schema.org/SiteNavigationElement" class="left">
        <li itemprop="name" class="show-for-medium-up"><a itemprop="url" href="{$ROOT_PATH}" title="{$LANG.common.home}"><svg class="icon"><use xlink:href="#icon-home"></use></svg></a></li>
        {$NAVIGATION_TREE}
        {if $CTRL_CERTIFICATES && !$CATALOGUE_MODE}
        <li itemprop="name"><a itemprop="url" href="{$URL.certificates}" title="{$LANG.navigation.giftcerts}">{$LANG.navigation.giftcerts}</a></li>
        {/if}
        {if $CTRL_SALE}
        <li itemprop="name"><a itemprop="url" href="{$URL.saleitems}" title="{$LANG.navigation.saleitems}">{$LANG.navigation.saleitems}</a></li>
        {/if}
     </ul>
  </section>
</nav>{/if}