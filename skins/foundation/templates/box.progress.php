{if isset($BLOCKS)}
<ul class="row no-bullet collapse checkout-progress-wrapper">
  {foreach from=$BLOCKS item=block}
  <li class="small-4 columns text-center checkout-progress {$block.class}"><a href="{$block.url}">{$block.step}. {$block.title}</a></li>
  {/foreach}
</ul>
 {/if}