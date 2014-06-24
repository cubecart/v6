{if ($page >= $show-1)}
  {$params[$var_name] = 1}
  <a href="{$current}{
  ($params)}{$anchor}">1</a> &hellip;
{/if}

{if ($page > 1)}
  {$params[$var_name] = $page-1}
  <a href="{$current}{http_build_query($params)}{$anchor}">&lt;</a>
{/if}

{for $i = 1; $i <= $total; $i++}
	{if ($i < $page - floor($show / 2))}
	{continue}
	{/if}

	{if ($i > $page + floor($show / 2))}
	{break}
	{/if}

	{$params[$var_name] = $i}
	{if ($i == $page)}
  		<strong>[{$i}]</strong>
	{else}
  		<a href="{$current}{http_build_query($params)}{$anchor}">{$i}</a>
  	{/if}
{/for}

{if ($page < $total)}
  {$params[$var_name] = $page + 1}
  <a href="{$current}{http_build_query($params)}{$anchor}">&gt;</a>
{/if}

{if ($i <= $total)}
  {$params[$var_name] = $total}
  &hellip; <a href="{$current}{http_build_query($params)}{$anchor}">{$total}</a>
{/if}


{if ($view_all)}
	{if (strtolower($page) != 'all')}
	  {$params[$var_name] = 'all'}
	  <a href="{$current}{http_build_query($params)}{$anchor}">{$LANG.common.view_all}</a>
	{else}
	  <strong>[{$LANG.common.view_all}]</strong>
	{/if}
{/if}