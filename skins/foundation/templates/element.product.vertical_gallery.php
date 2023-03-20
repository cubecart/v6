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
   {if is_array($GALLERY) && count($GALLERY) > 1}
   <div  class="medium-1 columns thinpad-right off-canvas-for-small vertical">
      <div id="scrollUp" class="scroller"><svg class="icon"><use xlink:href="#icon-angle-up"></use></svg></div>
      <ul class="clearing-thumbs small-block-grid-1" id="scrollContent" data-clearing>
         {foreach from=$GALLERY item=image}
         <li><a href="{$image.source}" class="th"><img src="{$image.small}" data-image-swap="{$image.medium}" data-caption="{$PRODUCT.name}{if !empty($image.image_tags.title)}: {/if}{$image.image_tags.title}" class="image-gallery" alt="{if isset($image.image_tags.alt) && !empty($image.image_tags.alt)}{$image.image_tags.alt}{else}{$image.name}{/if} - {$LANG.catalogue.click_enlarge}"{if isset($image.image_tags.title)} title="{$image.image_tags.title}"{/if}></a></li>
         {/foreach}
      </ul>
      <div id="scrollDown" class="scroller"><svg class="icon"><use xlink:href="#icon-angle-down"></use></svg></div>
   </div>
   {/if}
   <div class="small-5 medium-{if is_array($GALLERY) && count($GALLERY) > 1}6{else}7{/if} columns text-center nopad">            
      {if is_array($GALLERY) && count($GALLERY) > 1}
         <a href="#" class="open-clearing" data-thumb-index="0"><img src="{$PRODUCT.medium}" alt="{if isset($PRODUCT.image_tags.medium.alt) && !empty($PRODUCT.image_tags.medium.alt)}{$PRODUCT.image_tags.medium.alt}{else}{$PRODUCT.name}{/if}"{if isset($PRODUCT.image_tags.medium.title)} title="{$PRODUCT.image_tags.medium.title}"{/if} id="img-preview"></a>
         <p class="show-for-small-only">{$LANG.catalogue.tap_gallery}</p>
      {else}
         <div data-clearing><a href="{$PRODUCT.source}"><img src="{$PRODUCT.medium}" alt="{if isset($PRODUCT.image_tags.medium.alt) && !empty($PRODUCT.image_tags.medium.alt)}{$PRODUCT.image_tags.medium.alt}{else}{$PRODUCT.name}{/if}"{if isset($PRODUCT.image_tags.medium.title)} title="{$PRODUCT.image_tags.medium.title}"{/if} id="img-preview"></a></div>
         <p class="show-for-small-only">{$LANG.catalogue.tap_enlarge}</p>
      {/if}
   </div>
   <div class="small-7 medium-5 columns thinpad-left">
      {include file='templates/element.product.options.php'}
      {include file='templates/element.product.review_score.php'}
      {include file='templates/element.product.call_to_action.php'}
   </div>
</div>