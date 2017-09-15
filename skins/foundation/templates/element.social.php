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
{if $SOCIAL_LINKS}
<div class="element-social">
   <h3>{$LANG.common.follow_us}</h3>
   <ul class="small-block-grid-4 no-bullet nomarg social-icons text-left">
      {foreach from=$SOCIAL_LINKS item=link}
      <li><a href="{$link.url}" title="{$link.name}" target="_blank" rel="noopener noreferrer"><svg class="icon"><use xlink:href="#icon-{$link.icon}"></use></svg></a></li>
      {/foreach}
   </ul>
</div>{/if}