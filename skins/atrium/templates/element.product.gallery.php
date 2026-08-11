{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * $GALLERY items: {source, small, medium, name, image_tags}
 * $PRODUCT.{medium, source, image_tags.medium}
 *
 * ⚠ id="img-preview" IS A CONTRACT: 20-product.js swaps this element's src when
 * a product option carries its own image (data-image on the option). Renaming
 * it silently breaks option image switching.
 *}
<div x-data="ccGallery('{if isset($PRODUCT.source)}{$PRODUCT.source}{else}{$PRODUCT.medium}{/if}')">

   <button type="button" @click="enlarge()"
           class="block w-full overflow-hidden rounded-cc-lg border border-ink-200 bg-ink-100"
           title="{$LANG.catalogue.click_enlarge}">
      <img id="img-preview"
           src="{$PRODUCT.medium}"
           alt="{if isset($PRODUCT.image_tags.medium.alt) && !empty($PRODUCT.image_tags.medium.alt)}{$PRODUCT.image_tags.medium.alt}{else}{$PRODUCT.name}{/if}"
           {if isset($PRODUCT.image_tags.medium.title)}title="{$PRODUCT.image_tags.medium.title}"{/if}
           class="aspect-square w-full object-contain">
      <span class="cc-sr-only">{$LANG.catalogue.click_enlarge}</span>
   </button>

   {if is_array($GALLERY) && count($GALLERY) > 1}
   <ul role="list" class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6">
      {foreach from=$GALLERY item=image}
      <li>
         <button type="button"
                 @click="show('{$image.medium}', '{$image.source}')"
                 class="image-gallery block w-full overflow-hidden rounded-cc border border-ink-200 bg-ink-100 hover:border-brand-600 focus-visible:border-brand-600">
            <img src="{$image.small}"
                 alt="{if isset($image.image_tags.alt) && !empty($image.image_tags.alt)}{$image.image_tags.alt}{else}{$image.name}{/if}"
                 {if isset($image.image_tags.title)}title="{$image.image_tags.title}"{/if}
                 loading="lazy"
                 class="aspect-square w-full object-cover">
         </button>
      </li>
      {/foreach}
   </ul>
   {/if}

   {* Plain overlay, not a native <dialog>: this sits inside the product
      <form>, and a <dialog> holding form controls alters submit behaviour. *}
   <div x-show="open" x-cloak x-trap.noscroll="open"
        @keydown.escape.window="close()"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/80 p-4"
        role="dialog" aria-modal="true" aria-label="{$PRODUCT.name}">
      <button type="button" @click="close()"
              class="absolute end-4 top-4 rounded-cc bg-ink-100 p-2 text-ink-800 hover:bg-ink-200">
         <span class="cc-sr-only">{$LANG.common.close|default:'Close'}</span>
         <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"/>
         </svg>
      </button>
      <img :src="full" alt="{$PRODUCT.name}" class="max-h-full max-w-full object-contain" @click.stop>
   </div>
</div>
