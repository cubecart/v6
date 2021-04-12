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
<div class="row">
   {if count($GALLERY) > 1}
   <div  class="medium-1 columns thinpad-right off-canvas-for-small vertical">
      <div id="scrollUp" class="scroller"><svg class="icon"><use xlink:href="#icon-angle-up"></use></svg></div>
      <ul class="clearing-thumbs small-block-grid-1" id="scrollContent" data-clearing>
         {foreach from=$GALLERY item=image}
         <li{if $image@total lt 2} style="display:none"{/if}><a href="{$image.source}" class="th"><img src="{$image.small}" data-image-swap="{$image.medium}" data-caption="{$PRODUCT.name}{if !empty($image.description)}: {/if}{$image.description}" class="image-gallery" alt="{$LANG.catalogue.click_enlarge}"></a></li>
         {/foreach}
      </ul>
      <div id="scrollDown" class="scroller"><svg class="icon"><use xlink:href="#icon-angle-down"></use></svg></div>
   </div>
   {/if}
   <div class="small-5 medium-{if count($GALLERY) > 1}6{else}7{/if} columns text-center nopad">            
      {if count($GALLERY) > 1}
         <a href="#" class="open-clearing" data-thumb-index="0"><img src="{$PRODUCT.medium}" alt="{$PRODUCT.name}" id="img-preview"></a>
      {else}
         <div data-clearing><a href="{$image.source}"><img src="{$PRODUCT.medium}"></a></div>
      {/if}
      <p class="show-for-small-only">{if $image@total==1}{$LANG.catalogue.tap_enlarge}{else}{$LANG.catalogue.tap_gallery}{/if}</p>
   </div>
   <div class="small-7 medium-5 columns thinpad-left">
      {include file='templates/element.product.options.php'}
      {include file='templates/element.product.review_score.php'}
      {include file='templates/element.product.call_to_action.php'}
   </div>
</div>