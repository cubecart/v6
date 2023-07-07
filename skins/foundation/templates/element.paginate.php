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
<ul class="pagination right" id="element-paginate">
   {if ($page > 1)}
   {$params[$var_name] = $page-1}
   <li class="arrow"><a href="{$current}{http_build_query($params)}{$anchor}"><svg class="icon"><use xlink:href="#icon-angle-left"></use></svg> {$LANG.common.previous}</a></li>
   {/if}
   {if ($page >= $show-1)}
   {$params[$var_name] = 1}
   <li><a href="{$current}{http_build_query($params)}{$anchor}">1</a></li>
   <li class="unavailable">&hellip;</li>
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
   {if ($i <= $total)}
   {$params[$var_name] = $total}
   <li class="unavailable">&hellip;</li>
   <li><a href="{$current}{http_build_query($params)}{$anchor}">{$total}</a></li>
   {/if}
   {if ($page < $total)}
   {$params[$var_name] = $page + 1}
   <li class="arrow"><a href="{$current}{http_build_query($params)}{$anchor}">{$LANG.common.next} <svg class="icon"><use xlink:href="#icon-angle-right"></use></svg></a></li>
   {/if}
</ul>