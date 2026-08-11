{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ $featured is LOWER CASE — core assigns it that way, unlike every other
 * product variable in the skin. Do not "correct" it to $FEATURED.
 * Keys: {url, name, image, image_tags, ctrl_sale, price, sale_price}
 *}
{if $featured}
<section id="box-featured" class="cc-card p-4">
   <h3 class="text-sm font-semibold uppercase tracking-wider text-ink-900">{$LANG.catalogue.title_feature}</h3>
   <a href="{$featured.url}" title="{$featured.name}" class="mt-3 block overflow-hidden rounded-cc border border-ink-200 bg-ink-50">
      <img src="{$featured.image}"
           alt="{if isset($featured.image_tags.alt) && !empty($featured.image_tags.alt)}{$featured.image_tags.alt}{else}{$featured.name}{/if}"
           {if isset($featured.image_tags.title)}title="{$featured.image_tags.title}"{/if}
           loading="lazy"
           class="aspect-square w-full object-cover">
   </a>
   <h4 class="mt-3 text-sm font-medium">
      <a href="{$featured.url}" title="{$featured.name}" class="text-ink-900 hover:underline">{$featured.name}</a>
   </h4>
   <p class="mt-1">
      {if $featured.ctrl_sale}
      <span class="price text-sm text-ink-500 line-through">{$featured.price}</span>
      <span class="price ms-1 font-semibold text-danger-600">{$featured.sale_price}</span>
      {else}
      <span class="price font-semibold text-ink-900">{$featured.price}</span>
      {/if}
   </p>
</section>
{/if}
