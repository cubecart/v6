<div class="ext-card{if $ext.has_upgrade} ext-card-has-upgrade{elseif $ext.is_enabled} ext-card-installed ext-card-enabled{elseif $ext.is_installed} ext-card-installed ext-card-disabled{/if}" data-category="{$ext.category|escape}" data-name="{$ext.name|lower|escape}" data-type="{$ext.type|escape}" data-installed="{if $ext.is_installed}1{else}0{/if}" data-installed-version="{$ext.installed_version|escape}" data-installed-basename="{$ext.installed_basename|escape}" data-paid="{if $ext.purchase_url}1{else}0{/if}">
   <div class="ext-card-header">
      <h4 class="ext-card-name">{if $ext.recommended}<i class="fa fa-star ext-recommended" title="{$LANG.common.recommended|default:'Recommended'}"></i> {/if}{if $ext.page_url}<a href="{$ext.page_url|escape}" target="_blank" rel="noopener" title="{$LANG.module.ext_view_page|default:'View on CubeCart.com'}">{$ext.name|escape}</a>{else}{$ext.name|escape}{/if}</h4>
      {if count($ext.versions) > 1 || $ext.is_installed}
      <div class="ext-version-dropdown" data-card-version data-value="{$ext.current_url|escape}">
         <button type="button" class="ext-version-trigger">
            <span class="ext-version-trigger-label">{if $ext.current_version && $ext.current_version != 'n/a'}v{$ext.current_version|escape}{else}n/a{/if}</span>
            <i class="fa fa-caret-down"></i>
         </button>
         <div class="ext-version-panel">
            {if count($ext.versions) > 1}
            <div class="ext-version-options">
               {foreach from=$ext.versions item=ver}
               <button type="button" class="ext-version-option{if $ver.version == $ext.current_version} selected{/if}" data-value="{$ver.download|escape}" data-version="{$ver.version|escape}">v{$ver.version|escape}</button>
               {/foreach}
            </div>
            {/if}
            {if $ext.is_installed}
            <label class="ext-version-autoupdate">
               <input type="checkbox" class="btn-ext-autoupdate" data-module="{$ext.installed_basename|escape}"{if !$ext.autoupdate_disabled} checked{/if}>
               <span>{$LANG.module.auto_update}</span>
            </label>
            {/if}
         </div>
      </div>
      {else}
      <span class="ext-card-version">{if $ext.latest_version && $ext.latest_version != 'n/a'}v{$ext.latest_version|escape}{else}n/a{/if}</span>
      {/if}
   </div>
   {if $ext.description}<p class="ext-card-desc">{$ext.description|escape}</p>{/if}
   <div class="ext-card-meta">
      {if $ext.category_label}<span class="ext-badge ext-badge-cat">{$ext.category_label|escape}</span>{/if}
      {if $ext.third_party}
      <span class="ext-badge ext-badge-thirdparty"><i class="fa fa-cube"></i> {$LANG.module.ext_third_party}</span>
      {else}
      <span class="ext-badge ext-badge-official"><i class="fa fa-shield"></i> {$LANG.module.ext_official}</span>
      {/if}
      {if $ext.purchase_url || $ext.price}
      <span class="ext-badge ext-badge-paid"><i class="fa fa-shopping-cart"></i> {if $ext.price}{$ext.price|escape}{else}{$LANG.common.paid|default:'Paid'}{/if}</span>
      {/if}
      {if $ext.ioncube}
      <span class="ext-badge ext-badge-ioncube"><i class="fa fa-lock"></i> ionCube</span>
      {/if}
      {if $ext.php_versions}
      <span class="ext-badge ext-badge-php{if !$ext.php_compatible} ext-badge-php-incompatible{/if}" title="{if !$ext.php_compatible}Your server runs PHP {$SERVER_PHP} which is not supported{/if}"><i class="fa fa-{if $ext.php_compatible}check{else}warning{/if}"></i> PHP {$ext.php_versions|replace:',':', '|escape}</span>
      {/if}
      {if $ext.has_upgrade}
      <span class="ext-badge ext-badge-upgrade"><i class="fa fa-arrow-up"></i> {$LANG.module.ext_update_available}</span>
      {/if}
   </div>
   <div class="ext-card-actions">
      {if $ext.is_installed && $ext.type !== 'skin'}
      <label class="ext-toggle" title="{if $ext.is_enabled}{$LANG.common.disable|default:'Disable'}{else}{$LANG.common.enable|default:'Enable'}{/if}">
         <input type="checkbox" class="ext-toggle-input btn-ext-toggle" data-module="{$ext.installed_basename|escape}" data-type="{$ext.type|escape}"{if $ext.is_enabled} checked{/if}>
         <span class="ext-toggle-slider"></span>
      </label>
      {/if}
      {if !$ext.is_installed && $ext.images|@count > 0}
      <button type="button" class="ext-btn ext-btn-gallery btn-ext-gallery ext-btn-icon" title="{$LANG.catalogue.title_images|default:'Images'}" data-images='{$ext.images|@json_encode|escape}' data-name="{$ext.name|escape}">
         <i class="fa fa-picture-o"></i>
      </button>
      {/if}
      {if $ext.folder_name}
      <button type="button" class="ext-btn ext-btn-notes btn-ext-notes ext-btn-icon" title="{$LANG.settings.release_notes}" data-category="{$ext.category|escape}" data-folder="{$ext.folder_name|escape}" data-name="{$ext.name|escape}">
         <i class="fa fa-file-text-o"></i>
      </button>
      {/if}
      {if $ext.purchase_url}
      <a href="{$ext.purchase_url|escape}" target="_blank" class="ext-btn ext-btn-buy{if $ext.is_installed} ext-btn-icon{/if}" title="{$LANG.module.ext_more_info|default:'More Info'}">
         <i class="fa fa-external-link"></i>{if !$ext.is_installed} {$LANG.module.ext_more_info|default:'More Info'}{/if}
      </a>
      {/if}
      {if $ext.php_versions && !$ext.php_compatible && !$ext.is_installed}
      <button type="button" class="ext-btn ext-btn-disabled" disabled title="Requires PHP {$ext.php_versions|replace:',':', '|escape} — your server runs PHP {$SERVER_PHP}">
         <i class="fa fa-ban"></i> {$LANG.module.incompatible}
      </button>
      {elseif $ext.third_party && !$ext.download_url}
      {* 3rd party local-only: configure and delete only *}
      {elseif $ext.has_upgrade}
      <button type="button" class="ext-btn ext-btn-upgrade btn-ext-action" data-action="install" data-url="{$ext.download_url|escape}" data-latest-url="{$ext.download_url|escape}" data-name="{$ext.name|escape}" data-type="{$ext.type|escape}">
         <i class="fa fa-arrow-up"></i> {$LANG.module.ext_upgrade}
      </button>
      {elseif !$ext.is_installed}
      <button type="button" class="ext-btn ext-btn-install btn-ext-action" data-action="install" data-url="{$ext.download_url|escape}" data-name="{$ext.name|escape}" data-type="{$ext.type|escape}">
         <i class="fa fa-download"></i> {$LANG.common.install}
      </button>
      {else}
      <button type="button" class="ext-btn ext-btn-disabled btn-ext-action" data-action="install" data-url="{$ext.download_url|escape}" data-name="{$ext.name|escape}" data-type="{$ext.type|escape}" disabled>
         <i class="fa fa-check"></i> {$LANG.module.ext_up_to_date}
      </button>
      {/if}
      {if $ext.is_installed}
      <div class="ext-card-actions-right">
         {if $ext.edit_url}
         <a href="{$ext.edit_url|escape}" class="ext-btn ext-btn-configure ext-btn-icon" title="{$LANG.common.configure}"><i class="fa fa-cog"></i></a>
         {/if}
         {if $ext.is_active_skin}
         <button type="button" class="ext-btn ext-btn-delete ext-btn-icon" disabled title="{$LANG.module.skin_in_use}">
            <i class="fa fa-trash"></i>
         </button>
         {else}
         <button type="button" class="ext-btn ext-btn-delete btn-ext-delete-market ext-btn-icon" title="{$LANG.common.delete}" data-type="{$ext.installed_dir_type|escape}" data-module="{$ext.installed_basename|escape}" data-name="{$ext.name|escape}">
            <i class="fa fa-trash"></i>
         </button>
         {/if}
      </div>
      {/if}
   </div>
</div>
