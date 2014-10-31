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
<div id="mini-basket">
   <div class="show-for-medium-up">
      <a href="#" id="basket-summary" class="button white small"><i class="fa fa-shopping-cart"></i> {$CART_TOTAL}</a> 
      <div class="hide basket-detail-container" id="basket-detail">
         <div class="mini-basket-arrow"></div>
         {include file='templates/box.basket.content.php'} 
      </div>
   </div>
   <div class="show-for-small-only">
    <div class="show-for-small-only"><a class="right-off-canvas-toggle button white tiny" href="#"><i class="fa fa-shopping-cart fa-2x"></i></a></div>
   	<div class="hide panel radius small-basket-detail-container js_fadeOut" id="small-basket-detail"><i class="fa fa-check"></i> {$LANG.catalogue.added_to_basket}</div>
   </div>
</div>
