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
      {if count($GALLERY) > 1}
         <a href="#" class="open-clearing" data-thumb-index="0"><img src="{$PRODUCT.medium}" alt="{if isset($PRODUCT.image_tags.medium.alt) && !empty($PRODUCT.image_tags.medium.alt)}{$PRODUCT.image_tags.medium.alt}{else}{$PRODUCT.name}{/if}"{if isset($PRODUCT.image_tags.medium.title)} title="{$PRODUCT.image_tags.medium.title}"{/if} id="img-preview"></a>
      {else}
         <div data-clearing><a href="{$PRODUCT.source}"><img src="{$PRODUCT.medium}" alt="{if isset($PRODUCT.image_tags.medium.alt) && !empty($PRODUCT.image_tags.medium.alt)}{$PRODUCT.image_tags.medium.alt}{else}{$PRODUCT.name}{/if}"{if isset($PRODUCT.image_tags.medium.title)} title="{$PRODUCT.image_tags.medium.title}"{/if} id="img-preview"></a></div>
      {/if}
      {if count($GALLERY) > 1}
      <ul class="clearing-thumbs small-block-grid-3 medium-block-grid-5 marg-top" data-clearing>
         {foreach from=$GALLERY item=image}
         <li><a href="{$image.source}" class="th"><img src="{$image.small}" data-image-swap="{$image.medium}" data-caption="{$PRODUCT.name}{if !empty($image.image_tags.title)}: {/if}{$image.image_tags.title}" class="image-gallery" alt="{if isset($image.image_tags.alt) && !empty($image.image_tags.alt)}{$image.image_tags.alt}{else}{$image.name}{/if} - {$LANG.catalogue.click_enlarge}"{if isset($image.image_tags.title)} title="{$image.image_tags.title}"{/if}></a></li>
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