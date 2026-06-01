{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/extensions.css?{$VERSION_HASH}" media="screen">

<div id="marketplace" class="tab_content">
   <h3><i class="fa fa-cloud-download"></i> {$LANG.module.ext_marketplace}</h3>
   <p>{$LANG.module.ext_marketplace_desc}</p>

   <div class="ext-toolbar">
      <input type="text" id="ext-search" class="ext-search" placeholder="{$LANG.common.search}...">
      <span class="ext-count">{$EXT_COUNT} {$LANG.module.ext_available}</span>
      <a href="?_g=plugins&refresh=1" class="ext-filter-btn" title="{$LANG.common.refresh}"><i class="fa fa-refresh"></i></a>
   </div>

   <div class="ext-toolbar" id="ext-filters">
      <button type="button" class="ext-filter-btn active" data-filter="all">{$LANG.common.all} <span class="badge">{$EXT_COUNT}</span></button>
      {foreach from=$CATEGORIES key=cat item=count}
      <button type="button" class="ext-filter-btn" data-filter="{$cat}">{$cat|replace:'_':' '|ucwords} <span class="badge">{$count}</span></button>
      {/foreach}
   </div>

   <div class="ext-grid" id="ext-marketplace-grid">
      {if is_array($MARKETPLACE)}
      {foreach from=$MARKETPLACE item=ext}
      <div class="ext-card{if $ext.has_upgrade} ext-card-has-upgrade{elseif $ext.is_enabled} ext-card-installed ext-card-enabled{elseif $ext.is_installed} ext-card-installed ext-card-disabled{/if}" data-category="{$ext.category}" data-name="{$ext.name|lower}" data-type="{$ext.type}" data-installed="{if $ext.is_installed}1{else}0{/if}" data-installed-version="{$ext.installed_version}" data-installed-basename="{$ext.installed_basename}">
         <div class="ext-card-header">
            <h4 class="ext-card-name">{if $ext.recommended}<i class="fa fa-star ext-recommended" title="{$LANG.common.recommended|default:'Recommended'}"></i> {/if}{$ext.name}</h4>
            {if count($ext.versions) > 1 || $ext.is_installed}
            <div class="ext-version-dropdown" data-card-version data-value="{$ext.current_url}">
               <button type="button" class="ext-version-trigger">
                  <span class="ext-version-trigger-label">{if $ext.current_version && $ext.current_version != 'n/a'}v{$ext.current_version}{else}n/a{/if}</span>
                  <i class="fa fa-caret-down"></i>
               </button>
               <div class="ext-version-panel">
                  {if count($ext.versions) > 1}
                  <div class="ext-version-options">
                     {foreach from=$ext.versions item=ver}
                     <button type="button" class="ext-version-option{if $ver.version == $ext.current_version} selected{/if}" data-value="{$ver.download}" data-version="{$ver.version}">v{$ver.version}</button>
                     {/foreach}
                  </div>
                  {/if}
                  {if $ext.is_installed}
                  <label class="ext-version-autoupdate">
                     <input type="checkbox" class="btn-ext-autoupdate" data-module="{$ext.installed_basename}"{if !$ext.autoupdate_disabled} checked{/if}>
                     <span>{$LANG.module.auto_update}</span>
                  </label>
                  {/if}
               </div>
            </div>
            {else}
            <span class="ext-card-version">{if $ext.latest_version && $ext.latest_version != 'n/a'}v{$ext.latest_version}{else}n/a{/if}</span>
            {/if}
         </div>
         {if $ext.description}<p class="ext-card-desc">{$ext.description}</p>{/if}
         <div class="ext-card-meta">
            {if $ext.category_label}<span class="ext-badge ext-badge-cat">{$ext.category_label}</span>{/if}
            {if $ext.third_party}
            <span class="ext-badge ext-badge-thirdparty"><i class="fa fa-cube"></i> {$LANG.module.ext_third_party}</span>
            {else}
            <span class="ext-badge ext-badge-official"><i class="fa fa-shield"></i> {$LANG.module.ext_official}</span>
            {/if}
            {if $ext.purchase_url || $ext.price}
            <span class="ext-badge ext-badge-paid"><i class="fa fa-shopping-cart"></i> {if $ext.price}{$ext.price}{else}{$LANG.module.ext_paid|default:'Paid'}{/if}</span>
            {/if}
            {if $ext.ioncube}
            <span class="ext-badge ext-badge-ioncube"><i class="fa fa-lock"></i> ionCube</span>
            {/if}
            {if $ext.php_versions}
            <span class="ext-badge ext-badge-php{if !$ext.php_compatible} ext-badge-php-incompatible{/if}" title="{if !$ext.php_compatible}Your server runs PHP {$SERVER_PHP} which is not supported{/if}"><i class="fa fa-{if $ext.php_compatible}check{else}warning{/if}"></i> PHP {$ext.php_versions|replace:',':', '}</span>
            {/if}
            {if $ext.has_upgrade}
            <span class="ext-badge ext-badge-upgrade"><i class="fa fa-arrow-up"></i> {$LANG.module.ext_update_available}</span>
            {/if}
         </div>
         <div class="ext-card-actions">
            {if $ext.is_installed && $ext.type !== 'skin'}
            <label class="ext-toggle" title="{if $ext.is_enabled}{$LANG.common.disable|default:'Disable'}{else}{$LANG.common.enable|default:'Enable'}{/if}">
               <input type="checkbox" class="ext-toggle-input btn-ext-toggle" data-module="{$ext.installed_basename}" data-type="{$ext.type}"{if $ext.is_enabled} checked{/if}>
               <span class="ext-toggle-slider"></span>
            </label>
            {/if}
            {if !$ext.is_installed && $ext.images|@count > 0}
            <button type="button" class="ext-btn ext-btn-gallery btn-ext-gallery ext-btn-icon" title="{$LANG.catalogue.title_images|default:'Images'}" data-images='{$ext.images|@json_encode}' data-name="{$ext.name}">
               <i class="fa fa-picture-o"></i>
            </button>
            {/if}
            {if $ext.folder_name}
            <button type="button" class="ext-btn ext-btn-notes btn-ext-notes ext-btn-icon" title="{$LANG.settings.release_notes}" data-category="{$ext.category}" data-folder="{$ext.folder_name}" data-name="{$ext.name}">
               <i class="fa fa-file-text-o"></i>
            </button>
            {/if}
            {if $ext.purchase_url}
            <a href="{$ext.purchase_url}" target="_blank" class="ext-btn ext-btn-buy{if $ext.is_installed} ext-btn-icon{/if}" title="{$LANG.module.ext_more_info|default:'More Info'}">
               <i class="fa fa-external-link"></i>{if !$ext.is_installed} {$LANG.module.ext_more_info|default:'More Info'}{/if}
            </a>
            {/if}
            {if $ext.php_versions && !$ext.php_compatible && !$ext.is_installed}
            <button type="button" class="ext-btn ext-btn-disabled" disabled title="Requires PHP {$ext.php_versions|replace:',':', '} — your server runs PHP {$SERVER_PHP}">
               <i class="fa fa-ban"></i> {$LANG.module.incompatible}
            </button>
            {elseif $ext.third_party && !$ext.download_url}
            {* 3rd party local-only: configure and delete only *}
            {elseif $ext.has_upgrade}
            <button type="button" class="ext-btn ext-btn-upgrade btn-ext-action" data-action="install" data-url="{$ext.download_url}" data-latest-url="{$ext.download_url}" data-name="{$ext.name}" data-type="{$ext.type}">
               <i class="fa fa-arrow-up"></i> {$LANG.module.ext_upgrade}
            </button>
            {elseif !$ext.is_installed}
            <button type="button" class="ext-btn ext-btn-install btn-ext-action" data-action="install" data-url="{$ext.download_url}" data-name="{$ext.name}" data-type="{$ext.type}">
               <i class="fa fa-download"></i> {$LANG.common.install}
            </button>
            {else}
            <button type="button" class="ext-btn ext-btn-disabled btn-ext-action" data-action="install" data-url="{$ext.download_url}" data-name="{$ext.name}" data-type="{$ext.type}" disabled>
               <i class="fa fa-check"></i> {$LANG.module.ext_up_to_date}
            </button>
            {/if}
            {if $ext.is_installed}
            <div class="ext-card-actions-right">
               {if $ext.edit_url}
               <a href="{$ext.edit_url}" class="ext-btn ext-btn-configure ext-btn-icon" title="{$LANG.common.configure}"><i class="fa fa-cog"></i></a>
               {/if}
               {if $ext.is_active_skin}
               <button type="button" class="ext-btn ext-btn-delete ext-btn-icon" disabled title="{$LANG.module.skin_in_use}">
                  <i class="fa fa-trash"></i>
               </button>
               {else}
               <button type="button" class="ext-btn ext-btn-delete btn-ext-delete-market ext-btn-icon" title="{$LANG.common.delete}" data-type="{$ext.installed_dir_type}" data-module="{$ext.installed_basename}" data-name="{$ext.name}">
                  <i class="fa fa-trash"></i>
               </button>
               {/if}
            </div>
            {/if}
         </div>
      </div>
      {/foreach}
      {/if}
   </div>
</div>

<div id="ext-toast" class="ext-toast"></div>
<input type="hidden" id="ext-csrf-token" value="{$SESSION_TOKEN}">

<div id="ext-gallery-modal" class="ext-gallery-overlay">
   <div class="ext-gallery-modal">
      <div class="ext-gallery-header">
         <h4 id="ext-gallery-title"></h4>
         <button type="button" class="ext-gallery-close" id="ext-gallery-close"><i class="fa fa-times"></i></button>
      </div>
      <div class="ext-gallery-body">
         <button type="button" class="ext-gallery-nav ext-gallery-prev" id="ext-gallery-prev"><i class="fa fa-chevron-left"></i></button>
         <div class="ext-gallery-img-wrap">
            <img id="ext-gallery-img" src="" alt="">
         </div>
         <button type="button" class="ext-gallery-nav ext-gallery-next" id="ext-gallery-next"><i class="fa fa-chevron-right"></i></button>
      </div>
      <div class="ext-gallery-footer">
         <span id="ext-gallery-counter"></span>
         <div id="ext-gallery-thumbs" class="ext-gallery-thumbs"></div>
      </div>
   </div>
</div>

<div id="ext-notes-modal" class="ext-gallery-overlay">
   <div class="ext-gallery-modal ext-notes-modal">
      <div class="ext-gallery-header">
         <h4 id="ext-notes-title" data-prefix="{$LANG.settings.release_notes}"></h4>
         <button type="button" class="ext-gallery-close" id="ext-notes-close"><i class="fa fa-times"></i></button>
      </div>
      <div class="ext-gallery-body ext-notes-body" id="ext-notes-body" data-empty-label="{$LANG.form.none}"></div>
   </div>
</div>

{literal}
<script>
document.addEventListener('DOMContentLoaded', function() {

   var ajaxUrl = window.location.href.split('#')[0].split('?')[0] + '?_g=plugins';
   var csrfToken = $('#ext-csrf-token').val();

   // Highlight configure button after install/upgrade
   var params = new URLSearchParams(window.location.search);
   var installed = params.get('installed');
   if (installed) {
      var needle = installed.toLowerCase();
      var $card = $('.ext-card').filter(function() {
         return $(this).attr('data-name') === needle;
      });
      if ($card.length) {
         var $configBtn = $card.find('.ext-btn-configure');
         if ($configBtn.length) {
            setTimeout(function() {
               $card[0].scrollIntoView({ behavior:'smooth', block:'center' });
               setTimeout(function() {
                  $configBtn.addClass('ext-btn-highlight');
               }, 400);
            }, 500);
         }
      }
   }

   function reloadWithTab() {
      var url = window.location.href.split('#')[0];
      var activeTab = window.location.hash || '#marketplace';
      window.location.href = url + activeTab;
      window.location.reload();
   }

   // Toast notification
   function showToast(message, type) {
      var $toast = $('#ext-toast');
      $toast.removeClass('show ext-toast-success ext-toast-error')
            .addClass('ext-toast-' + type)
            .text(message);
      setTimeout(function() { $toast.addClass('show'); }, 10);
      setTimeout(function() { $toast.removeClass('show'); }, 4000);
   }

   // Persist filter/search state across reloads
   function saveFilterState() {
      var filter = $('#ext-filters .ext-filter-btn.active').data('filter') || 'all';
      var search = $('#ext-search').val() || '';
      sessionStorage.setItem('ext_filter', filter);
      sessionStorage.setItem('ext_search', search);
   }

   function restoreFilterState() {
      var filter = sessionStorage.getItem('ext_filter') || 'all';
      var search = sessionStorage.getItem('ext_search') || '';
      if (filter !== 'all') {
         $('#ext-filters .ext-filter-btn').removeClass('active');
         var $btn = $('#ext-filters .ext-filter-btn[data-filter="' + filter + '"]');
         if ($btn.length) {
            $btn.addClass('active');
         } else {
            $('#ext-filters .ext-filter-btn[data-filter="all"]').addClass('active');
         }
      }
      if (search) {
         $('#ext-search').val(search);
      }
      applySearch();
   }

   // Category filter
   $('#ext-filters').on('click', '.ext-filter-btn', function() {
      var filter = $(this).data('filter');
      $('#ext-filters .ext-filter-btn').removeClass('active');
      $(this).addClass('active');

      if (filter === 'all') {
         $('.ext-card').show();
      } else {
         $('.ext-card').hide();
         $('.ext-card[data-category="' + filter + '"]').show();
      }
      applySearch();
      saveFilterState();
   });

   // Search
   var searchTimer;
   $('#ext-search').on('input', function() {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function() {
         applySearch();
         saveFilterState();
      }, 200);
   });

   function applySearch() {
      var term = $('#ext-search').val().toLowerCase().trim();
      var activeFilter = $('#ext-filters .ext-filter-btn.active').data('filter');

      $('.ext-card').each(function() {
         var $card = $(this);
         var matchesFilter = (activeFilter === 'all' || $card.data('category') === activeFilter);
         var matchesSearch = (!term || $card.data('name').indexOf(term) !== -1 || $card.data('type').indexOf(term) !== -1);
         $card.toggle(matchesFilter && matchesSearch);
      });
   }

   // Restore state on load
   restoreFilterState();

   // Custom version dropdown: open / close
   $(document).on('click', '.ext-version-trigger', function(e) {
      e.stopPropagation();
      var $dd = $(this).closest('.ext-version-dropdown');
      $('.ext-version-dropdown').not($dd).removeClass('open');
      $dd.toggleClass('open');
   });
   $(document).on('click', '.ext-version-panel', function(e) { e.stopPropagation(); });
   $(document).on('click', function() { $('.ext-version-dropdown.open').removeClass('open'); });

   // Custom version dropdown: option click → update state + fire change event
   $(document).on('click', '.ext-version-option', function(e) {
      e.stopPropagation();
      var $opt = $(this);
      var $dd = $opt.closest('.ext-version-dropdown');
      var ver = String($opt.data('version'));
      var url = String($opt.data('value'));
      $dd.attr('data-value', url);
      $dd.find('.ext-version-trigger-label').text('v' + ver);
      $dd.find('.ext-version-option').removeClass('selected');
      $opt.addClass('selected');
      $dd.removeClass('open');
      $dd.trigger('card-version:change');
   });

   // Version selector change — update action button dynamically
   $(document).on('card-version:change', '[data-card-version]', function() {
      var $select = $(this);
      var $card = $select.closest('.ext-card');
      var $btn = $card.find('.btn-ext-action');
      if (!$btn.length) return;

      var selectedVersion = ($select.find('.ext-version-trigger-label').text() || '').replace(/^v/, '').trim();
      var installedVersion = String($card.attr('data-installed-version') || '');
      var isInstalled = $card.attr('data-installed') == '1';

      if (!isInstalled) return;

      // Latest = first option (versions are newest-first)
      var $firstOpt = $select.find('.ext-version-option').first();
      var latestVersion = String($firstOpt.data('version') || '');
      var latestUrl = String($firstOpt.data('value') || '');

      $btn.prop('disabled', false).removeData('latest-url');
      if (selectedVersion === installedVersion) {
         // Dropdown matches installed — offer upgrade to latest if available
         if (versionCompare(latestVersion, installedVersion) > 0) {
            $btn.removeClass('ext-btn-disabled ext-btn-install ext-btn-downgrade')
                .addClass('ext-btn-upgrade')
                .data('latest-url', latestUrl)
                .html('<i class="fa fa-arrow-up"></i> Upgrade to ' + latestVersion);
         } else {
            $btn.removeClass('ext-btn-install ext-btn-upgrade ext-btn-downgrade')
                .addClass('ext-btn-disabled').prop('disabled', true)
                .html('<i class="fa fa-check"></i> Installed');
         }
      } else if (versionCompare(selectedVersion, installedVersion) > 0) {
         $btn.removeClass('ext-btn-disabled ext-btn-install ext-btn-downgrade')
             .addClass('ext-btn-upgrade')
             .html('<i class="fa fa-arrow-up"></i> Upgrade');
      } else {
         $btn.removeClass('ext-btn-disabled ext-btn-install ext-btn-upgrade')
             .addClass('ext-btn-downgrade')
             .html('<i class="fa fa-history"></i> Downgrade');
      }
   });

   function versionCompare(a, b) {
      var pa = a.split('.'), pb = b.split('.');
      for (var i = 0; i < Math.max(pa.length, pb.length); i++) {
         var na = parseInt(pa[i] || 0, 10);
         var nb = parseInt(pb[i] || 0, 10);
         if (na > nb) return 1;
         if (na < nb) return -1;
      }
      return 0;
   }

   // Install / Upgrade / Downgrade
   $(document).on('click', '.btn-ext-action', function() {
      var $btn = $(this);
      if ($btn.prop('disabled') || $btn.data('loading')) return;

      var action = $btn.data('action');
      var $card = $btn.closest('.ext-card');
      var $versionSelect = $card.find('[data-card-version]');
      var latestUrl = $btn.data('latest-url');
      var url = latestUrl ? latestUrl : ($versionSelect.length ? $versionSelect.attr('data-value') : $btn.data('url'));
      var name = $btn.data('name');
      var type = $btn.data('type');

      var isDowngrade = $btn.hasClass('ext-btn-downgrade');
      if (isDowngrade && !confirm('Downgrade "' + name + '"? This will overwrite the current version.')) return;

      var originalHtml = $btn.html();
      var loadingText = isDowngrade ? 'Downgrading...' : (action === 'install' ? 'Installing...' : 'Upgrading...');
      $btn.data('loading', true).html('<span class="ext-spinner"></span> ' + loadingText);

      $.ajax({
         url: ajaxUrl,
         type: 'POST',
         dataType: 'json',
         headers: { 'X-Requested-With': 'XMLHttpRequest' },
         data: {
            token: csrfToken,
            ajax_action: 'install_extension',
            download_url: url,
            ext_name: name,
            ext_type: type,
            is_downgrade: isDowngrade ? 1 : 0
         },
         success: function(resp) {
            if (resp.success) {
               $btn.data('loading', false);
               window.location.href = window.location.href.split('#')[0].split('?')[0] + '?_g=plugins&installed=' + encodeURIComponent(name) + '&t=' + Date.now() + '#marketplace';
            } else {
               showToast(resp.message, 'error');
               $btn.html(originalHtml).data('loading', false);
            }
         },
         error: function(xhr) {
            console.error('Install AJAX error:', xhr.status, 'Response:', xhr.responseText.substring(0, 500));
            showToast('Error: ' + xhr.status + ' - ' + xhr.responseText.substring(0, 200), 'error');
            $btn.html(originalHtml).data('loading', false);
         }
      });
   });

   // Image gallery modal
   var galleryImages = [];
   var galleryIndex = 0;

   function openGallery(images, name, startIndex) {
      galleryImages = images;
      galleryIndex = startIndex || 0;
      $('#ext-gallery-title').text(name);
      updateGalleryImage();
      buildThumbnails();
      $('#ext-gallery-modal').addClass('show');
      $(document).on('keydown.gallery', function(e) {
         if (e.keyCode === 27) closeGallery();
         if (e.keyCode === 37) navigateGallery(-1);
         if (e.keyCode === 39) navigateGallery(1);
      });
   }

   function closeGallery() {
      $('#ext-gallery-modal').removeClass('show');
      $(document).off('keydown.gallery');
      galleryImages = [];
   }

   function navigateGallery(dir) {
      if (!galleryImages.length) return;
      galleryIndex = (galleryIndex + dir + galleryImages.length) % galleryImages.length;
      updateGalleryImage();
      updateActiveThumbnail();
   }

   function updateGalleryImage() {
      var img = galleryImages[galleryIndex];
      var src = (typeof img === 'string') ? img : (img.url || img.src || '');
      $('#ext-gallery-img').attr('src', src);
      $('#ext-gallery-counter').text((galleryIndex + 1) + ' / ' + galleryImages.length);
      $('#ext-gallery-prev, #ext-gallery-next').toggle(galleryImages.length > 1);
   }

   function buildThumbnails() {
      var $thumbs = $('#ext-gallery-thumbs').empty();
      if (galleryImages.length <= 1) return;
      $.each(galleryImages, function(i, img) {
         var src = (typeof img === 'string') ? img : (img.thumb || img.url || img.src || '');
         $('<img>').attr('src', src).addClass('ext-gallery-thumb' + (i === galleryIndex ? ' active' : ''))
            .data('index', i).appendTo($thumbs);
      });
   }

   function updateActiveThumbnail() {
      $('#ext-gallery-thumbs .ext-gallery-thumb').removeClass('active')
         .eq(galleryIndex).addClass('active');
   }

   $(document).on('click', '.btn-ext-gallery', function() {
      var images = $(this).data('images');
      var name = $(this).data('name');
      if (images && images.length) openGallery(images, name, 0);
   });

   $('#ext-gallery-close').on('click', closeGallery);
   $('#ext-gallery-prev').on('click', function() { navigateGallery(-1); });
   $('#ext-gallery-next').on('click', function() { navigateGallery(1); });
   $('#ext-gallery-modal').on('click', function(e) {
      if ($(e.target).is('#ext-gallery-modal')) closeGallery();
   });
   $(document).on('click', '.ext-gallery-thumb', function() {
      galleryIndex = $(this).data('index');
      updateGalleryImage();
      updateActiveThumbnail();
   });

   // Release notes modal
   function escapeHtml(s) {
      return String(s == null ? '' : s)
         .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
         .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
   }
   function closeNotes() { $('#ext-notes-modal').removeClass('show'); }
   function setNotesTitle(name) {
      var $t = $('#ext-notes-title');
      var prefix = $t.data('prefix') || 'Release Notes';
      $t.text(prefix + ' - ' + name);
   }
   function renderNotes(name, payload) {
      var $body = $('#ext-notes-body').empty();
      setNotesTitle(name);
      var notes = (payload && payload.release_notes) || [];
      if (!notes.length) {
         var emptyLabel = (payload && payload.message) ? payload.message : ($body.data('empty-label') || 'None');
         $body.append('<p class="ext-notes-empty">' + escapeHtml(emptyLabel) + '</p>');
      } else {
         var html = '';
         for (var i = 0; i < notes.length; i++) {
            var n = notes[i];
            html += '<div class="ext-notes-entry">'
                 +   '<div class="ext-notes-entry-head">'
                 +     '<strong>' + escapeHtml(n.version) + '</strong>'
                 +     '<span class="ext-notes-date">' + escapeHtml((n.updated_at || n.created_at || '').substring(0,10)) + '</span>'
                 +   '</div>'
                 +   '<div class="ext-notes-entry-body">' + escapeHtml(n.notes).replace(/\n/g, '<br>') + '</div>'
                 + '</div>';
         }
         $body.html(html);
      }
      $('#ext-notes-modal').addClass('show');
   }
   $(document).on('click', '.btn-ext-notes', function() {
      var $btn = $(this);
      var name = $btn.data('name');
      setNotesTitle(name);
      $('#ext-notes-body').html('<p class="ext-notes-empty"><i class="fa fa-spinner fa-spin"></i></p>');
      $('#ext-notes-modal').addClass('show');
      $.ajax({
         url: ajaxUrl,
         type: 'POST',
         dataType: 'json',
         headers: { 'X-Requested-With': 'XMLHttpRequest' },
         data: {
            token: csrfToken,
            ajax_action: 'release_notes',
            category: $btn.data('category'),
            folder: $btn.data('folder')
         },
         success: function(resp) { renderNotes(name, resp); },
         error: function() { renderNotes(name, { release_notes: [], message: 'Could not load release notes.' }); }
      });
   });
   $('#ext-notes-close').on('click', closeNotes);
   $('#ext-notes-modal').on('click', function(e) {
      if ($(e.target).is('#ext-notes-modal')) closeNotes();
   });

   // Auto-update opt-out toggle (inside the version dropdown)
   $(document).on('change', '.btn-ext-autoupdate', function() {
      var $cb = $(this);
      var module = $cb.data('module');
      var disabled = $cb.is(':checked') ? 0 : 1;
      $cb.prop('disabled', true);
      $.ajax({
         url: ajaxUrl,
         type: 'POST',
         dataType: 'json',
         headers: { 'X-Requested-With': 'XMLHttpRequest' },
         data: {
            token: csrfToken,
            ajax_action: 'toggle_autoupdate',
            ext_module: module,
            disabled: disabled
         },
         success: function(resp) {
            if (!resp.success) {
               showToast(resp.message || 'Update failed.', 'error');
               $cb.prop('checked', !$cb.is(':checked'));
            }
         },
         error: function() {
            showToast('Network error.', 'error');
            $cb.prop('checked', !$cb.is(':checked'));
         },
         complete: function() { $cb.prop('disabled', false); }
      });
   });

   // Enable/disable toggle
   $(document).on('change', '.btn-ext-toggle', function() {
      var $input = $(this);
      var $card = $input.closest('.ext-card');
      var enabled = $input.is(':checked') ? 1 : 0;
      var module = $input.data('module');
      var type = $input.data('type');

      $input.prop('disabled', true);

      $.ajax({
         url: ajaxUrl,
         type: 'POST',
         dataType: 'json',
         headers: { 'X-Requested-With': 'XMLHttpRequest' },
         data: {
            token: csrfToken,
            ajax_action: 'toggle_module',
            ext_module: module,
            ext_type: type,
            enabled: enabled
         },
         success: function(resp) {
            if (resp.success) {
               if (resp.enabled) {
                  $card.removeClass('ext-card-disabled').addClass('ext-card-enabled');
               } else {
                  $card.removeClass('ext-card-enabled').addClass('ext-card-disabled');
               }
               showToast(resp.enabled ? 'Enabled' : 'Disabled', 'success');
            } else {
               showToast(resp.message || 'Failed to toggle.', 'error');
               $input.prop('checked', !enabled);
            }
            $input.prop('disabled', false);
         },
         error: function() {
            showToast('Network error. Please try again.', 'error');
            $input.prop('checked', !enabled).prop('disabled', false);
         }
      });
   });

   // Delete extension from marketplace card
   $(document).on('click', '.btn-ext-delete-market', function(e) {
      e.preventDefault();
      var $btn = $(this);
      var type = $btn.data('type');
      var module = $btn.data('module');
      var name = $btn.data('name');

      if (!confirm('Delete "' + name + '"? This will remove the extension files.')) return;

      var originalHtml = $btn.html();
      $btn.html('<span class="ext-spinner"></span> Deleting...');

      $.ajax({
         url: ajaxUrl,
         type: 'POST',
         dataType: 'json',
         headers: { 'X-Requested-With': 'XMLHttpRequest' },
         data: {
            token: csrfToken,
            ajax_action: 'delete_extension',
            ext_type: type,
            ext_module: module
         },
         success: function(resp) {
            if (resp.success) {
               showToast(resp.message, 'success');
               setTimeout(function() { reloadWithTab(); }, 1500);
            } else {
               showToast(resp.message, 'error');
               $btn.html(originalHtml);
            }
         },
         error: function() {
            showToast('Network error. Please try again.', 'error');
            $btn.html(originalHtml);
         }
      });
   });


});
</script>
{/literal}
