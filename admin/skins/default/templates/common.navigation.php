{if isset($NAVIGATION)}
{foreach from=$NAVIGATION item=group}
<div id="{$group.group}" class="menu">{if $group.visible=="false"}<i class="fa fa-plus-square-o"></i>{else}<i class="fa fa-minus-square-o"></i>{/if} {$group.title}</div>
{if isset($group.members)}
<ul id="menu_{$group.group}" class="submenu{if $group.visible=="false"} hide{/if}">
   {foreach from=$group.members item=nav}
   <li><a href="{$nav.url}" target="{$nav.target}"{if !empty($nav.id)} id="{$nav.id}"{/if}>{$nav.title}{if !empty($nav.icon)} <i class="fa {$nav.icon}"></i>{/if}</a></li>
   {/foreach}
</ul>
{/if}
{/foreach}
{/if}