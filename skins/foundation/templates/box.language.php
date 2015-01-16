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
{if $LANGUAGES}
<div class="right text-center show-for-medium-up" id="box-language">
   <a href="#" data-dropdown="language-switch" class="button white small"><img class="flag flag-{$current_language.code|substr:3:2}" alt="{$current_language.title}" src="#"></a><br>
   <ul id="language-switch" data-dropdown-content class="f-dropdown">
      {foreach from=$LANGUAGES item=language}
      {if $current_language.code!==$language.code}
      <li class="text-left"><a href="{$language.url}"><img src="#" class="flag flag-{$language.code|substr:3:2}" alt="{$language.title}"> {$language.title}</a></li>
      {/if}
      {/foreach}
   </ul>
</div>
{/if}