{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * The only star renderer in this skin: product page, category listing and
 * homepage cards all come through here.
 *
 * Params (all optional): score, context ('product'|'listing'), uid, wrapper_class.
 *
 * ⚠ The ids are product-context only — a listing renders this once per card, so
 * emitting them there gives one page many elements sharing an id. The gradient
 * id cannot just be dropped (the half star references it), so it takes `uid`.
 *}
{if !isset($score)}{assign var=score value=$PRODUCT.review_score}{/if}
{if !isset($context)}{assign var=context value='product'}{/if}
{if !isset($uid)}{assign var=uid value=''}{/if}
{if !isset($wrapper_class)}{assign var=wrapper_class value='mt-2'}{/if}
{assign var=cc_stars value=$SKIN_SETTINGS.review_stars|default:'product'}

{if $score && $CTRL_REVIEW && $cc_stars != 'none' && ($context == 'product' || $cc_stars == 'all')}
<div class="{$wrapper_class} flex items-center gap-2"{if $context == 'product'} id="review_rating"{/if}>
   <div class="flex items-center gap-0.5" role="img" aria-label="{$score} {$LANG.reviews.out_of_five|default:'out of 5'}">
      {for $i = 1; $i <= 5; $i++}
      {if $score >= $i}
      <svg class="size-4 cc-star-on" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"{if $context == 'product'} id="review_rating_{$i}"{/if}>
         <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/>
      </svg>
      {elseif $score > ($i - 1) && $score < $i}
      {* Half star: the same path filled by a 50% gradient. *}
      <svg class="size-4 cc-star-on" viewBox="0 0 20 20" aria-hidden="true"{if $context == 'product'} id="review_rating_{$i}"{/if}>
         <defs><linearGradient id="cc-half-{$uid}-{$i}"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="transparent"/></linearGradient></defs>
         <path fill="url(#cc-half-{$uid}-{$i})" stroke="currentColor" stroke-width="1" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/>
      </svg>
      {else}
      <svg class="size-4 cc-star-off" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"{if $context == 'product'} id="review_rating_{$i}"{/if}>
         <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/>
      </svg>
      {/if}
      {/for}
   </div>
   {if isset($LANG_REVIEW_INFO) && $LANG_REVIEW_INFO}
   <span class="text-sm text-ink-500" id="review_rating_info">{$LANG_REVIEW_INFO}</span>
   {/if}
</div>
{/if}
