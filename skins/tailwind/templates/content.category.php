<div class="">
<h2>{$category.cat_name}</h2>
{if isset($category.image)}
<div><img src="{$category.image}" alt="{if isset($category.image_tags.alt)}{$category.image_tags.alt}{else}{$category.cat_name}{/if}"{if isset($category.image_tags.title)} title="{$category.image_tags.title}"{/if} class="rounded-lg h-full w-full object-cover object-center"></div>
{/if}
{if !empty($category.cat_desc)}
<p>{$category.cat_desc}</p>
{/if}
</div>
{if isset($SUBCATS) && $SUBCATS}
<div class="mt-4 flow-root">
    <div class="-my-2">
    <div class="relative box-content h-80 overflow-x-auto py-2 xl:overflow-visible">
        <div class="absolute flex space-x-8 px-4 sm:px-6 lg:px-8 xl:relative xl:grid xl:grid-cols-5 xl:gap-x-8 xl:space-x-0 xl:px-0">
        {foreach from=$SUBCATS item=subcat}
        <a href="{$subcat.url}" class="relative flex h-80 w-56 flex-col overflow-hidden rounded-lg p-6 hover:opacity-75 xl:w-auto">
            <span aria-hidden="true" class="absolute inset-0">
            <img src="{$subcat.cat_image}" alt="{if isset($subcat.image_tags.alt)}{$subcat.image_tags.alt}{else}{$subcat.cat_name}{/if}"{if isset($subcat.image_tags.title)} title="{$subcat.image_tags.title}"{/if} class="h-full w-full object-cover object-center">
            </span>
            <span aria-hidden="true" class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-gray-800 opacity-50"></span>
            <span class="relative mt-auto text-center text-xl font-bold text-white">{$subcat.cat_name}</span>
        </a>
        {/foreach}
        </div>
    </div>
    </div>
</div>
{/if}

{if $PRODUCTS}
{if isset($SORTING)}
<section aria-labelledby="filter-heading" class="border-t border-gray-200 pt-6 mt-9">
    <form action="{$VAL_SELF}" class="fn-dd-post" method="post">
        <div class="md:flex md:items-center">
            <div class="md:w-20">
                <label for="location" class="inline-block text-sm font-medium leading-6 text-gray-900">{$LANG.form.sort_by}</label>
            </div>
            <div class="md:flex md:items-center">
                <select id="location" name="location" class="w-60 mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    {foreach from=$SORTING item=sort}
                    <option value="{$sort.field}|{$sort.order}" {$sort.selected}>{$sort.name} ({$sort.direction})</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </form>
</section>
{/if}




<div class="bg-white">
  <div class="mx-auto max-w-2xl px-6 py-6 lg:max-w-7xl">
    <h2 class="sr-only">Products</h2>
    <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-6 sm:gap-y-10 lg:grid-cols-3 lg:gap-x-8">
    {foreach from=$PRODUCTS item=product}
      <div class="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="aspect-h-4 aspect-w-3 bg-gray-200 sm:aspect-none group-hover:opacity-75 sm:h-96">
          <img src="{$product.thumbnail}" {if isset($product.image_tags.thumbnail.alt) && !empty($product.image_tags.thumbnail.alt)}{$product.image_tags.thumbnail.alt}{else}{$product.name}{/if} {if isset($product.image_tags.thumbnail.title)} title="{$product.image_tags.thumbnail.title}"{/if} class="h-full w-full object-cover object-center sm:h-full sm:w-full">
        </div>
        <div class="flex flex-1 flex-col space-y-2 p-4">
          <h3 class="text-sm font-medium text-gray-900">
            <a href="{$product.url}">
              <span aria-hidden="true" class="absolute inset-0"></span>
              {$product.name}
            </a>
          </h3>
          <p class="text-sm text-gray-500">{$product.description_short}</p>
          <div class="flex flex-1 flex-col justify-end">
            <p class="text-base font-medium">
            {if $product.ctrl_sale}
            <span class="text-red-600 line-through">{$product.price}</span> <span class="text-gray-900">{$product.sale_price}</span>
            {else}
            <span class="text-gray-900">{$product.price}</span>
            {/if}
            </p>
          </div>
        </div>
      </div>
      {/foreach}
    </div>
  </div>
</div>


{if isset($PAGINATION) && !empty($PAGINATION)}
<form action="{$VAL_SELF}" class="fn-dd-post" method="post">
    <div class="flex justify-end mb-3 mr-6">
        <label for="per_page" class="hidden text-sm font-medium leading-6 text-gray-900">{$LANG.common.show}</label>
        <select id="per_page" name="location" class="w-60 mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
        {foreach from=$PAGE_SPLITS item=page_split}
        <option value="{$page_split.url}"{if $page_split.selected} selected{/if}>{$page_split.amount} {$LANG.common.per_page}</option>
        {/foreach}
        </select>
    </div>
</form>
{$PAGINATION}
{/if}

{elseif !isset($SUBCATS) || !$SUBCATS}
<p>{$LANG.category.no_products}</p>
{/if}