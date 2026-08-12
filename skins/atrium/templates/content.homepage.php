{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Fetched unguarded by Cubecart::displayHomePage().
 *
 * Form contract — none of these names may change:
 *   class="add_to_basket"   read by the paypal_commerce module (renderSmartButton)
 *                           via document.getElementsByClassName('add_to_basket')
 *   name="add"              product id, read by Cart::add()
 *   name="quantity"         quantity
 * The CSRF token input is injected before </form> by GUI::display() — do not
 * add one by hand.
 *}
{if isset($DOCUMENT)}
{* ccHero drives the .cc-hero scroller that arrives INSIDE $DOCUMENT.content
   (installer-seeded markup, so controls cannot be templated into it). The
   scroller works without JS — these only make the other banners discoverable,
   since the scrollbar is hidden. count<2 hides them entirely.
   Arrows are sm:block only: touch already has swipe, and the banner caption is
   real HTML anchored at left:6%, which the prev arrow would sit on top of. *}
<div id="content_homepage" class="prose-cc mb-12" x-data="ccHero()">
   {if $DOCUMENT.hide_title==0}<h1 class="mb-4 text-3xl font-semibold tracking-tight text-ink-900">{$DOCUMENT.title}</h1>{/if}
   <div class="relative">
      {$DOCUMENT.content}
      <template x-if="count > 1">
         <div>
            <button type="button" @click="prev()" :disabled="index === 0" aria-label="{$LANG.common.previous|default:'Previous'}"
                    class="absolute start-2 top-1/2 hidden -translate-y-1/2 rounded-full bg-ink-100/90 p-2 text-ink-800 shadow disabled:opacity-40 hover:bg-ink-100 sm:block">
               <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button type="button" @click="next()" :disabled="index >= count - 1" aria-label="{$LANG.common.next|default:'Next'}"
                    class="absolute end-2 top-1/2 hidden -translate-y-1/2 rounded-full bg-ink-100/90 p-2 text-ink-800 shadow disabled:opacity-40 hover:bg-ink-100 sm:block">
               <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="mt-3 flex justify-center gap-2">
               <template x-for="i in count" :key="i">
                  <button type="button" @click="go(i - 1)" :aria-label="'Banner ' + i" :aria-current="index === i - 1"
                          class="size-2.5 rounded-full transition-colors"
                          :class="index === i - 1 ? 'bg-ink-800' : 'bg-ink-300 hover:bg-ink-400'"></button>
               </template>
            </div>
         </div>
      </template>
   </div>
</div>
{/if}

{if $LATEST_PRODUCTS}
<section id="content_latest_products" aria-labelledby="latest-products-heading">
   <h2 id="latest-products-heading" class="mb-6 text-xl font-semibold tracking-tight text-ink-900">
      {$LANG.catalogue.latest_products}
   </h2>

   <ul role="list" class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-3 xl:grid-cols-4">
      {foreach from=$LATEST_PRODUCTS item=product}
      <li class="group flex flex-col">
         <form action="{$VAL_SELF}" method="post" class="add_to_basket flex h-full flex-col">

            <a href="{$product.url}" class="block overflow-hidden rounded-cc-lg border border-ink-200 bg-ink-100">
               <img src="{$product.image}"
                    alt="{if isset($product.image_tags.alt) && !empty($product.image_tags.alt)}{$product.image_tags.alt}{else}{$product.name}{/if}"
                    {if isset($product.image_tags.title)}title="{$product.image_tags.title}"{/if}
                    loading="lazy"
                    class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105">
            </a>

            <h3 class="mt-3 text-sm font-medium">
               {* text-ink-900 must sit on the <a>, not the <h3>: the base layer styles
                  bare `a` with the brand colour, which beats an inherited colour. *}
               <a href="{$product.url}" title="{$product.name}" class="text-ink-900 hover:underline">{$product.name}</a>
            </h3>

            {if $product.review_score && $CTRL_REVIEW}
            <div class="mt-1 flex items-center gap-0.5" role="img"
                 aria-label="{$product.review_score} {$LANG.reviews.out_of_five|default:'out of 5'}">
               {for $i = 1; $i <= 5; $i++}
               <svg class="size-4 {if $product.review_score >= $i}cc-star-on{elseif $product.review_score > ($i - 1)}cc-star-on{else}cc-star-off{/if}"
                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/>
               </svg>
               {/for}
            </div>
            {/if}

            <div class="mt-2">
               {if $product.ctrl_sale}
               <span class="price text-sm text-ink-500 line-through">{$product.price}</span>
               <span class="price ms-1 text-base font-semibold text-danger-600">{$product.sale_price}</span>
               {else}
               <span class="price text-base font-semibold text-ink-900">{$product.price}</span>
               {/if}
            </div>

            <div class="mt-3 flex-1"></div>

            {if $CTRL_HIDE_PRICES}
            <a href="{$product.url}" title="{$product.name}" class="cc-btn cc-btn-secondary mt-2 w-full">{$LANG.common.info}</a>

            {elseif $product.available <= 0}
            <button type="submit" class="cc-btn cc-btn-secondary mt-2 w-full" disabled>{$LANG.common.unavailable}</button>

            {* ctrl_stock: purchasable — in stock, or allowed out of stock by store settings. *}
            {elseif $product.ctrl_stock && !$CATALOGUE_MODE}
            <div class="mt-2 flex gap-2">
               <label class="cc-sr-only" for="qty-{$product.product_id}">{$LANG.common.quantity}</label>
               <input type="number" id="qty-{$product.product_id}" name="quantity"
                      value="{$product.minimum_quantity|default:'1'}"
                      min="{$product.minimum_quantity|default:'1'}" maxlength="3"
                      class="quantity required w-16 text-center">
               <button type="submit" class="cc-btn cc-btn-primary flex-1">{$LANG.catalogue.add_to_basket}</button>
            </div>

            {elseif !$CATALOGUE_MODE}
            <button type="submit" class="cc-btn cc-btn-secondary mt-2 w-full" disabled>{$LANG.catalogue.out_of_stock_short}</button>
            {/if}

            <input type="hidden" name="add" value="{$product.product_id}">
         </form>
      </li>
      {/foreach}
   </ul>
</section>
{/if}
