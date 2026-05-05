{*
 * CubeCart v6 — admin image picker (shared element).
 *
 * Renders the folder-browsing image picker with selected strip, drop-zone, and
 * folder grid. The companion JS lives in admin/skins/default/js/image-picker.js
 * and auto-initialises any `<div data-cc-image-picker>` it finds.
 *
 * Required params:
 *   storage_key    string  — unique localStorage suffix (e.g. "prod_42", "cat_5")
 *   dropzone_url   string  — Dropzone POST target (e.g. "?_g=filemanager&product_id=42")
 *   initial_json   string  — pre-encoded JSON of currently-selected items (server-built)
 *   placeholder    string  — URL of the placeholder image when nothing is selected
 *
 * Optional params:
 *   single         bool    — true for single-select mode (categories, etc.)
 *   hint           string  — instructional copy above the picker
 *   product_id_var string  — id of a hidden <div> exposing the product_id (for
 *                            dropzone refresh hooks; defaults to "val_product_id")
 *
 * The save handler on each consumer page already understands the POST shape
 * `imageset[file_id] = position` (1 = main).
 *}
<div class="img-picker"
     data-cc-image-picker
     {if !empty($single)}data-cc-single="1"{/if}
     data-cc-storage-key="{$storage_key|escape:'html'}"
     data-cc-initial-json="{$initial_json|escape:'html'}">

   {if !empty($hint)}
   <p class="img-picker-hint">{$hint}</p>
   {/if}

   <div class="img-picker__top">
      <div class="img-picker__selected">
         <ol class="img-picker__sel-grid"></ol>
         <div class="img-picker__empty"><img src="{$placeholder}" alt=""></div>
      </div>
      <div class="dropzone img-picker__upload">
         <div class="dz-default dz-message"><span>{$LANG.filemanager.file_upload_note}</span></div>
      </div>
   </div>

   <div class="img-picker__browser">
      <div class="img-picker__bar">
         <nav class="img-picker__breadcrumb fm-breadcrumb"></nav>
         <div class="img-picker__newdir">
            <input type="text" class="img-picker__newdir-name textbox" placeholder="{$LANG.filemanager.folder_create}" autocomplete="off">
            <button type="button" class="img-picker__newdir-btn button tiny"><i class="fa fa-plus"></i></button>
         </div>
      </div>
      <div class="img-picker__panel">
         <div class="img-picker__grid" aria-busy="false"></div>
         <div class="img-picker__loading" hidden><i class="fa fa-spinner fa-spin"></i></div>
      </div>
   </div>

   <div class="img-picker__val-subdir" id="val_subdir" hidden></div>
</div>

<div id="dropzone_url" style="display: none;">{$dropzone_url}</div>
{if !empty($product_id_value)}
<div id="val_product_id" style="display: none;">{$product_id_value}</div>
{/if}
{if !empty($cat_id_value)}
<div id="val_cat_id" style="display: none;">{$cat_id_value}</div>
{/if}
<script>window.CC_LANG_NONE = "{$LANG.common.none|escape:'javascript'}";</script>
