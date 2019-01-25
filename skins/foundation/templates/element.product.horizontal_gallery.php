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
   <div class="small-5 medium-7 columns horizontal">
      <div id="open-clearing-wrapper"><a href="#" class="open-clearing" data-thumb-index="0"><img src="{$PRODUCT.medium}" alt="{$PRODUCT.name}" id="img-preview"></a></div>
      {if $GALLERY}
      <ul class="clearing-thumbs small-block-grid-3 medium-block-grid-5 marg-top" data-clearing>
         {foreach from=$GALLERY item=image}
         <li{if $image@total lt 2} style="display:none"{/if}><a href="{$image.source}" class="th"><img src="{$image.small}" data-image-swap="{$image.medium}" data-caption="{$PRODUCT.name}{if !empty($image.description)}: {/if}{$image.description}" class="image-gallery" alt="{$LANG.catalogue.click_enlarge}"></a></li>
         {/foreach}
      </ul>
      {/if}
   </div>
   <div class="small-7 medium-5 columns">
      {include file='templates/element.product.options.php'}
      {include file='templates/element.product.review_score.php'}
      {include file='templates/element.product.call_to_action.php'}
   </div>
</div>