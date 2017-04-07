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
 {if !$CATALOGUE_MODE}
<div id="mini-basket">
   <div class="show-for-medium-up">
      <div class="text-right"><a href="#" id="basket-summary" class="button white small"><svg class="icon icon-basket"><use xlink:href="#icon-basket"></use></svg> {$CART_TOTAL}</a></div>
      <div class="basket-detail-container hide" id="basket-detail">
         <div class="mini-basket-arrow"></div>
         {include file='templates/box.basket.content.php'} 
      </div>
   </div>
   <div class="show-for-small-only">
      <div class="show-for-small-only"><a class="right-off-canvas-toggle button white tiny" href="#"><svg class="icon icon-basket icon-x2"><use xlink:href="#icon-basket"></use></svg></a></div>
      <div class="hide panel radius small-basket-detail-container js_fadeOut" id="small-basket-detail"><svg class="icon"><use xlink:href="#icon-check"></use></svg> {$LANG.catalogue.added_to_basket}</div>
   </div>
   <div class="session_token hide">{$SESSION_TOKEN}</div>
</div>
{/if}