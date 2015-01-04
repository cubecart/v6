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
<div class="row">
   <div class="small-12 columns">
      <h2>{$DOCUMENT.doc_name}</h2>
   </div>
</div>
<div class="row">
   <div class="small-12 columns">{$DOCUMENT.doc_content}</div>
</div>
{if $SHARE}
<hr>
<div class="row">
   <div class="small-12 columns">
      {foreach from=$SHARE item=html}
      {$html}
      {/foreach}
   </div>
</div>
{/if}
{foreach from=$COMMENTS item=html}
{$html}
{/foreach}