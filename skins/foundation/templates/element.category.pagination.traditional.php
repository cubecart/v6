<div class="row">
    <div class="small-12 large-8 columns">
        {$PAGINATION}
    </div>
    <div class="large-4 columns show-for-medium-up">
        <dl>
        <dd>
            <select class="url_select">
            {foreach from=$PAGE_SPLITS item=page_split}
            <option value="{$page_split.url}"{if $page_split.selected} selected{/if}>{$LANG.common.show} {$page_split.amount} {$LANG.settings.product_per_page|lower}</option>
            {/foreach}
            </select>
        </dd>
        </dl>
    </div>
</div>