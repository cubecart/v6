<div class="show-for-small-only">
    <div class="hide" id="ccScrollCat">{$category.cat_id}</div>
    {if $page!=='all' && ($page < $total)}
    {$params[$var_name] = $page + 1}
    {* Add "hide-for-medium-up" to the class attribute to not display the more button *}
    <a href="{$current}{http_build_query($params)}{$anchor}" data-next-page="{$params[$var_name]}" data-cat="{$category.cat_id}" class="button tiny expand ccScroll-next">{$LANG.common.more} <svg class="icon"><use xlink:href="#icon-angle-down"></use></svg></a>
    {/if}
    <div class="text-center hide" id="loading"><svg class="icon-x3"><use xlink:href="#icon-spinner"></use></svg></div>
</div>