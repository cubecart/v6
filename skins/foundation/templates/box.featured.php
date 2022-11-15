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
{if $featured}
<div class="panel" id="box-featured">
   <h3>{$LANG.catalogue.title_feature}</h3>
   <div class="text-center">
      <a class="th" href="{$featured.url}" title="{$featured.name}">
      <img src="{$featured.image}" alt="{if isset($featured.image_tags.alt) && !empty($featured.image_tags.alt)}{$featured.image_tags.alt}{else}{$featured.name}{/if}"{if isset($featured.image_tags.title)} title="{$featured.image_tags.title}"{/if}>
      </a>
   </div>
   <h4><a href="{$featured.url}" title="{$featured.name}">{$featured.name}</a></h4>
   {if $featured.ctrl_sale}
   <span class="old_price">{$featured.price}</span> <span class="sale_price">{$featured.sale_price}</span>
   {else}
   <span>{$featured.price}</span>
   {/if}
</div>
{/if}