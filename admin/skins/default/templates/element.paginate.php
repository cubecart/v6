{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if ($page >= $show-1)}
  {$params[$var_name] = 1}
  <a href="{$current}{http_build_query($params)}{$anchor}">1</a> &hellip;
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
  		<div class="current">{$i}</div>
	{else}
  		<a href="{$current}{http_build_query($params)}{$anchor}">{$i}</a>
  	{/if}
{/for}

{if ($i <= $total)}
  {$params[$var_name] = $total}
  &hellip; <a href="{$current}{http_build_query($params)}{$anchor}">{$total}</a>
{/if}

{if ($page < $total)}
  {$params[$var_name] = $page + 1}
  <a href="{$current}{http_build_query($params)}{$anchor}">&gt;</a>
{/if}