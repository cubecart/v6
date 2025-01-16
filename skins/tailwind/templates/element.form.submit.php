<div class="mt-6 flex items-center gap-x-6">
    <button type="{if isset($type) && !empty($type)}{$type}{else}submit{/if}"{if isset($name) && !empty($name)} name="{$name}"{/if} class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">{$value}</button>
{if isset($reset) && $reset}
    <button type="reset" class="text-sm font-semibold leading-6 text-gray-900">{$LANG.common.reset}</button>{/if}
</div>