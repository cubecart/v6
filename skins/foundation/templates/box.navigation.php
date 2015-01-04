{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}

{if $CATEGORIES}{$CATEGORIES}{else}<nav class="top-bar category-nav" data-topbar="">
  <ul class="title-area">
     <li class="name"></li>
     <li class="toggle-topbar left"><a href="">{$LANG.navigation.title} <i class="fa fa-caret-down"></i></a></li>
  </ul>
  <section class="top-bar-section">
     <ul class="left">
        <li class="show-for-medium-up"><a href="index.php" title="{$LANG.common.home}"><i class="fa fa-home"></i></a></li>
        {$NAVIGATION_TREE}
        {if $CTRL_CERTIFICATES && !$CATALOGUE_MODE}
        <li><a href="{$STORE_URL}/gift-certificates.html" title="{$LANG.navigation.giftcerts}">{$LANG.navigation.giftcerts}</a></li>
        {/if}
        {if $CTRL_SALE}
        <li><a href="{$STORE_URL}/sale-items.html" title="{$LANG.navigation.saleitems}">{$LANG.navigation.saleitems}</a></li>
        {/if}
     </ul>
  </section>
</nav>{/if}