<ul class="pagination hide-for-small-only">
   {if ($page >= $show-1)}
   {$params[$var_name] = 1}
   <li><a href="{$current}{http_build_query($params)}{$anchor}">1</a></li>
   <li class="unavailable">&hellip;</li>
   {/if}
   {if ($page > 1)}
   {$params[$var_name] = $page-1}
   <li class="arrow"><a href="{$current}{http_build_query($params)}{$anchor}"><i class="fa fa-angle-double-left"></i></a></li>
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
   <li class="current"><a href="">{$i}</a></li>
   {else}
   <li><a href="{$current}{http_build_query($params)}{$anchor}">{$i}</a></li>
   {/if}
   {/for}
   {if ($page < $total)}
   {$params[$var_name] = $page + 1}
   <li class="arrow"><a href="{$current}{http_build_query($params)}{$anchor}"><i class="fa fa-angle-double-right"></i></a></li>
   {/if}
   {if ($i <= $total)}
   {$params[$var_name] = $total}
   <li class="unavailable">&hellip;</li>
   <li><a href="{$current}{http_build_query($params)}{$anchor}">{$total}</a></li>
   {/if}
   <!-- Replaced with dropdown quantities
   {if ($view_all)}
   {if (strtolower($page) != 'all')}
   {$params[$var_name] = 'all'}
   <li><a href="{$current}{http_build_query($params)}{$anchor}">{$LANG.common.view_all}</a></li>
   {else}
   <li><strong>[{$LANG.common.view_all}]</strong></li>
   {/if}
   {/if}
   -->
</ul>