<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
  <div class="flex flex-1 justify-between sm:hidden">
    {if ($page > 1)}
    {$params[$var_name] = $page-1} 
    <a href="{$current}{http_build_query($params)}{$anchor}" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">{$LANG.common.previous}</a>
    {else}
    <span class="relative inline-flex items-center px-4 py-2"></span>
    {/if}
    {if ($page < $total)}
    {$params[$var_name] = $page + 1}
    <a href="{$current}{http_build_query($params)}{$anchor}" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">{$LANG.common.next}</a>
    {else}
    <span class="relative inline-flex items-center px-4 py-2"></span>
    {/if}
  </div>
  <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
    <div>
      <p class="text-sm text-gray-700">
        Showing
        <span class="font-medium">{$page}</span>
        to
        <span class="font-medium">{if ($page < $total)}{$page*$per_page}{else}{$TOTAL_RESULTS}{/if}</span>
        of
        <span class="font-medium">{$TOTAL_RESULTS}</span>
        results
      </p>
    </div>
    <div>
      <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
      {if ($page > 1)}
      {$params[$var_name] = $page-1}
      {$disable = false}
      {else}
      {$params[$var_name] = 1}
      {$disable = true}
      {/if} 
      <a href="{if $disable}javascript:void(0);{else}{$current}{http_build_query($params)}{$anchor}{/if}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
          <span class="sr-only">{$LANG.common.previous}</span>
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
          </svg>
        </a>
        
        {for $i = 1; $i <= $total; $i++}
        {$params[$var_name] = $i}
        {if ($i == $page)}
        <a href="javascript:void(0);" aria-current="page" class="relative z-10 inline-flex items-center bg-indigo-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">{$i}</a>
        {else}
        <a href="{$current}{http_build_query($params)}{$anchor}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">{$i}</a>
        {/if}
        {/for}
        {if ($i <= $total)}
        {$params[$var_name] = $total}
        <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>
        <a href="{$current}{http_build_query($params)}{$anchor}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">{$total}</a>
        {/if}
        {if ($page < $total)}
        {$params[$var_name] = $page + 1}
        {$disable = false}
        {else}
        {$disable = true}
        {/if}
        {$params[$var_name] = $page + 1}
        <a href="{if $disable}javascript:void(0);{else}{$current}{http_build_query($params)}{$anchor}{/if}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
          <span class="sr-only">{$LANG.common.next}</span>
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
          </svg>
        </a>
      </nav>
    </div>
  </div>
</div>
