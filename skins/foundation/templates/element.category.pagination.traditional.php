<div class="row show-for-medium-up">
    <div class="large-3 columns">
        <dl>
        <dd>
            <select class="url_select">
            {foreach from=$PAGE_SPLITS item=page_split}
            <option value="{$page_split.url}"{if $page_split.selected} selected{/if}>{$page_split.amount} {$LANG.common.per_page}</option>
            {/foreach}
            </select>
        </dd>
        </dl>
    </div>
    <div class="large-9 columns">
        {$PAGINATION}
    </div>
</div>