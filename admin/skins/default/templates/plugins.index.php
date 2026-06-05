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

   <div class="ext-tabs" id="ext-paid-tabs">
      <button type="button" class="ext-tab active" data-paid-tab="free">{$LANG.common.free}</button>
      <button type="button" class="ext-tab" data-paid-tab="paid">{$LANG.common.paid}</button>
   </div>

   <div class="ext-grid ext-tab-panel active" id="ext-grid-free" data-paid-panel="free">
      {if is_array($MARKETPLACE)}
      {foreach from=$MARKETPLACE item=ext}
      {if !$ext.purchase_url}
      {include file='templates/element.ext_card.php' ext=$ext}
      {/if}
      {/foreach}
      {/if}
   </div>

   <div class="ext-grid ext-tab-panel" id="ext-grid-paid" data-paid-panel="paid">
      {if is_array($MARKETPLACE)}
      {foreach from=$MARKETPLACE item=ext}
      {if $ext.purchase_url}
      {include file='templates/element.ext_card.php' ext=$ext}
      {/if}
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
      var filter = $('#ext-filters .ext-filter-btn[data-filter].active').data('filter') || 'all';
      var search = $('#ext-search').val() || '';
      sessionStorage.setItem('ext_filter', filter);
      sessionStorage.setItem('ext_search', search);
      sessionStorage.setItem('ext_paid_tab', $('#ext-paid-tabs .ext-tab.active').data('paid-tab') || 'free');
   }

   function setPaidTab(tab) {
      $('#ext-paid-tabs .ext-tab').removeClass('active');
      $('#ext-paid-tabs .ext-tab[data-paid-tab="' + tab + '"]').addClass('active');
      // Drive display inline so it works regardless of stylesheet cache state.
      $('.ext-tab-panel').removeClass('active').css('display', 'none');
      $('.ext-tab-panel[data-paid-panel="' + tab + '"]').addClass('active').css('display', '');
   }

   // Recompute category badge counts for the active tab; hide empty categories.
   function updateBadges() {
      var $panel = $('.ext-tab-panel.active');
      var total = $panel.find('.ext-card').length;
      $('#ext-filters .ext-filter-btn[data-filter="all"] .badge').text(total);
      // Toolbar "N extensions available" — keep the label, swap the leading number.
      var $count = $('.ext-count');
      $count.text(total + ' ' + $count.text().replace(/^\s*\d+\s*/, ''));
      $('#ext-filters .ext-filter-btn[data-filter]').each(function() {
         var f = $(this).data('filter');
         if (f === 'all') return;
         var n = $panel.find('.ext-card[data-category="' + f + '"]').length;
         $(this).find('.badge').text(n);
         $(this).toggle(n > 0);
      });
      // If the active category is empty in this tab, fall back to All.
      var $active = $('#ext-filters .ext-filter-btn[data-filter].active');
      if ($active.length && $active.data('filter') !== 'all' && $active.is(':hidden')) {
         $('#ext-filters .ext-filter-btn[data-filter]').removeClass('active');
         $('#ext-filters .ext-filter-btn[data-filter="all"]').addClass('active');
      }
   }

   function restoreFilterState() {
      var filter = sessionStorage.getItem('ext_filter') || 'all';
      var search = sessionStorage.getItem('ext_search') || '';
      if (filter !== 'all') {
         $('#ext-filters .ext-filter-btn[data-filter]').removeClass('active');
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
      // Default: free tab.
      setPaidTab(sessionStorage.getItem('ext_paid_tab') || 'free');
      updateBadges();
      applySearch();
   }

   // Category filter — scoped to [data-filter] (refresh link in #ext-filters also
   // carries .ext-filter-btn but has no data-filter).
   $('#ext-filters').on('click', '.ext-filter-btn[data-filter]', function() {
      var filter = $(this).data('filter');
      $('#ext-filters .ext-filter-btn[data-filter]').removeClass('active');
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
      var activeFilter = $('#ext-filters .ext-filter-btn[data-filter].active').data('filter');

      // Free/paid are separate tab panels; search + category filter apply within each.
      $('.ext-card').each(function() {
         var $card = $(this);
         var matchesFilter = (activeFilter === 'all' || $card.data('category') === activeFilter);
         var matchesSearch = (!term || $card.data('name').indexOf(term) !== -1 || $card.data('type').indexOf(term) !== -1);
         $card.toggle(matchesFilter && matchesSearch);
      });
   }

   // Free/Paid tabs
   $('#ext-paid-tabs').on('click', '.ext-tab', function() {
      setPaidTab($(this).data('paid-tab'));
      updateBadges();
      applySearch();
      saveFilterState();
   });

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
