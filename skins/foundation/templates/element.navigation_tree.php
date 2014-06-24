<li class="has-dropdown">
   <a href="{$BRANCH.url}" title="{$BRANCH.name}">{$BRANCH.name}</a>
   {if isset($BRANCH.children)}
   <ul class="dropdown">
      <li><label>{$BRANCH.name}</label></li>
      {$BRANCH.children}
   </ul>
   {/if}
</li>