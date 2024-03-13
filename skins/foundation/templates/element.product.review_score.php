{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if $PRODUCT.review_score && $CTRL_REVIEW}
<div>
   {for $i = 1; $i <= 5; $i++}
   {if $PRODUCT.review_score >= $i}
   <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star.png" alt="">
   {elseif $PRODUCT.review_score > ($i - 1) && $PRODUCT.review_score < $i}
   <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_half.png" alt="">
   {else}
   <img src="{$STORE_URL}/skins/{$SKIN_FOLDER}/images/star_off.png" alt="">
   {/if}
   {/for}
</div>
<div>{$LANG_REVIEW_INFO}</div>
{/if}