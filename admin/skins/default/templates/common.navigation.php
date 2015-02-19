{if isset($NAVIGATION)}
{foreach from=$NAVIGATION item=group}
<div id="{$group.group}" class="menu">{$group.title}</div>
{if isset($group.members)}
<ul id="menu_{$group.group}" class="submenu"{if $group.visible=="false"} style="display: none"{/if}>
   {foreach from=$group.members item=nav}
   <li><a href="{$nav.url}" target="{$nav.target}"{if !empty($nav.id)} id="{$nav.id}"{/if}>{$nav.title}</a></li>
   {/foreach}
</ul>
{/if}
{/foreach}
{/if}