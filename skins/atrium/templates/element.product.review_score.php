{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Star rating. Takes an optional `score` parameter so it serves two contexts:
 *   product page  {include file='...' score=$PRODUCT.review_score}
 *   category list {include file='...' score=$product.review_score}
 * With no parameter it falls back to $PRODUCT.review_score.
 *
 * $LANG_REVIEW_INFO is the "N reviews" summary, assigned only on the product page.
 *}
{if !isset($score)}{assign var=score value=$PRODUCT.review_score}{/if}
{if $score && $CTRL_REVIEW}
<div class="mt-2 flex items-center gap-2" id="review_rating">
   <div class="flex items-center gap-0.5" role="img" aria-label="{$score} {$LANG.reviews.out_of_five|default:'out of 5'}">
      {for $i = 1; $i <= 5; $i++}
      {if $score >= $i}
      <svg class="size-4 text-warn-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" id="review_rating_{$i}">
         <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/>
      </svg>
      {elseif $score > ($i - 1) && $score < $i}
      {* Half star: the same path filled by a 50% gradient. *}
      <svg class="size-4 text-warn-600" viewBox="0 0 20 20" aria-hidden="true" id="review_rating_{$i}">
         <defs><linearGradient id="half{$i}"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="transparent"/></linearGradient></defs>
         <path fill="url(#half{$i})" stroke="currentColor" stroke-width="1" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/>
      </svg>
      {else}
      <svg class="size-4 text-ink-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" id="review_rating_{$i}">
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
