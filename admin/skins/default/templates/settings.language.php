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
<form action="{$VAL_SELF}" id="edit_phrases" method="post" enctype="multipart/form-data">
   {if isset($LANGUAGES)}
   <div id="lang_list" class="tab_content">
      <h3>{$LANG.translate.title_languages}</h3>
      <table>
         <thead>
         <tr>
            <th>{$LANG.common.status}</th>
            <th colspan="2">{$LANG.common.language}</th>
            <th>{$LANG.form.action}</th>
         </tr>
         </thead>
         <tbody>
         <tr>
            <td></td>
            <td><img src="language/flags/globe.png" alt="{$LANG.translate.master_language}"></td>
            <td>{$LANG.translate.master_language}</td>
            <td class="actions"><a href="?_g=settings&node=language&download=definitions" title="{$LANG.common.download}"><i class="fa fa-download" title="{$LANG.common.download}"></i></a></td>
         </tr>
         {foreach from=$LANGUAGES item=language}
         <tr>
            <td style="text-align:center"><input type="hidden" name="status[{$language.code}]" id="status_{$language.code}" value="{$language.status}" class="toggle"></td>
            <td><img src="{$language.flag}" alt="{$language.title}"></td>
            <td><a href="{$language.edit}">{$language.title}</a></td>
            <td class="actions">
               <a href="{$language.download}" title="{$LANG.common.download}"><i class="fa fa-download" title="{$LANG.common.download}"></i></a>
               <a href="{$language.edit}" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
               <a href="{$language.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
            </td>
         </tr>
         {/foreach}
         </tbody>
         </table>
   </div>
   <div id="lang_create" class="tab_content">
      <h3>{$LANG.translate.title_language_create}</h3>
      <fieldset>
         <div><label for="create_title">{$LANG.translate.language_name}</label><span><input id="create_title" type="text" name="create[title]" class="textbox required" placeholder="{$LANG.translate.language_name_eg}"></span></div>
         <div><label for="create_code">{$LANG.translate.language_code}</label><span><input id="create_code" type="text" name="create[code]" class="textbox required" placeholder="{$LANG.translate.language_code_eg}"></span></div>
         <div><label for="create_currency">{$LANG.translate.language_currency}</label><span><input id="create_currency" type="text" name="create[currency_iso]" class="textbox number" placeholder="{$LANG.translate.language_code_eg2}"></span> {$LANG.translate.language_currency_code_url}</div>
         <div>
            <label for="create_direction">{$LANG.translate.language_direction}</label>
            <span>
               <select id="create_direction" name="create[text_direction]">
                  <option value="ltr">{$LANG.common.read_ltr}</option>
                  <option value="rtl">{$LANG.common.read_rtl}</option>
               </select>
            </span>
         </div>
      </fieldset>
   </div>
   <div id="lang_import" class="tab_content">
      <h3>{$LANG.translate.title_language_import}</h3>
      <fieldset>
         <div><label for="import_overwrite">{$LANG.filemanager.overwrite}</label><span><input id="import_overwrite" type="checkbox" name="import[overwrite]"></span></div>
         <div><label for="import_file">{$LANG.filemanager.file_upload}</label><span><input id="import_file" type="file" name="import[file]" class="textbox"> {$LANG.translate.example_upload}</span></div>
      </fieldset>
   </div>
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="hidden" name="previous-tab" id="previous-tab" value="">
      <input type="submit" name="save" value="{$LANG.common.save}">
   </div>
   {elseif !$DISPLAY_EDITOR && !$DISPLAY_EXPORT}
   <div id="lang_list" class="tab_content">
   <h3>{$LANG.translate.title_languages}</h3>
   <p>{$LANG.translate.error_no_languages}</p>
   </div>
   {/if}
   {if $DISPLAY_EDITOR}
   <div class="tab_content" id="general">
      <h3>{$EDIT_TITLE}</h3>
      {if $SHOW_SEARCH}
         <fieldset>
            <legend>{$LANG.translate.search_phrases}</legend>
            <div><input type="text" class="textbox" name="lang_groups_search_phrase" value="{$SEARCH_PHRASE}" /> <input type="submit" value="{$LANG.common.go}" name="go" class="update tiny">{if !empty($SEARCH_PHRASE)} <a href="?_g=settings&node=language&language={$SEARCH_LANG}">{$LANG.common.reset}</a>{/if}</div>
         </fieldset>
         {if isset($SEARCH_HITS) && count($SEARCH_HITS)>0}
         <p>{$LANG.translate.phrases_found}</p>
         <table class="collapsed">
            <tr>
               <th class="thead text-left" nowrap="nowrap">{$LANG.translate.language_group_edit}</th>
               <th class="thead text-left">{$LANG.common.key}</th>
               <th class="thead text-left">{$LANG.common.phrase}</th>
            </tr>
         {foreach from=$SEARCH_HITS item=hit key=group}
            {if !empty($SEARCH_HITS[$group])}
               {foreach $hit as $desc}
               <tr{if $desc@last} style="border-bottom: 1px solid #c5c5c5"{/if}>
                  {if $desc@first}
               <td valign="top" rowspan="{$SEARCH_HITS[$group]|count}" class="thead vertical" title="{$SEARCH_PHRASE_TITLES[$group]}">{$SEARCH_PHRASE_GROUPS[$group]}</td>
                  {/if}
               <td width="150px" valign="top"><a href="?_g=settings&node=language&language={$SEARCH_LANG}&type={$group}&key={$desc@key}">{$desc@key}</a></td>
               <td>{$desc}</td>
            </tr>
               {/foreach}
            {/if}
         {/foreach}
         </table>
         {elseif isset($SEARCH_HITS)}
         <p>{$LANG.translate.no_phrases_found|replace:'%s':$SEARCH_PHRASE}</p>
         {/if}
      {/if}
      {if $SECTIONS}
      <fieldset>
         <legend>{$LANG.translate.language_group_edit}</legend>
         <div>
            <select name="type" class="textbox update_form required">
               <option value="">{$LANG.form.please_select}</option>
               {foreach from=$SECTIONS item=section}
               <option value="{$section.name}" {$section.selected}>{$section.description}</option>
               {/foreach}
               <optgroup label="{$LANG.navigation.nav_modules}">
                  {foreach from=$MODULES item=module}
                  <option value="{$module.path}" {$module.selected}>{$module.name}</option>
                  {/foreach}
               </optgroup>
            </select>
         </div>
      </fieldset>
      {/if}
      {if isset($BACK)}
      <a href="{$BACK}">&laquo; {$LANG.common.back}</a>
      {/if}
      {if isset($STRINGS)}
      <fieldset>
         <legend>{$STRING_TYPE}</legend>
         <table class="phrases">
            {foreach from=$STRINGS item=string}
            <tr id="row_{$string.name}">
               <td class="phrase_row" rel="string_{$string.name}">
                  <label for="string_{$string.name}">{$string.name}</label>
                  <input type="hidden" id="defined_{$string.name}" value="{$string.defined}">
                  {if $string.multiline}
                  <textarea id="string_{$string.name}" name="string[{$string.type}][{$string.name}]" class="textbox editable_phrase" rel="{$string.name}"{if $string.disabled} disabled="disabled"{/if} title="{$LANG.common.click_edit}">{$string.value}</textarea>
                  {else}
                  <input type="text" id="string_{$string.name}" name="string[{$string.type}][{$string.name}]" value="{$string.value}" class="textbox editable_phrase" rel="{$string.name}"{if $string.disabled} disabled="disabled"{/if} title="{$LANG.common.click_edit}">
                  {/if}
               </td>
               <td class="actions">
                  <input type="hidden" id="default_{$string.name}" value="{$string.default}">
                  <a href="#" class="revert" id="revert_{$string.name}" rel="{$string.name}" title="{$LANG.common.revert}"><i class="fa fa-clock-o"></i></a>
               </td>
            </tr>
            {/foreach}
         </table>
      </fieldset>
      {/if}
      <div>
         <input type="hidden" name="previous-tab" id="previous-tab" value="">
         <input type="submit" name="save" value="{$LANG.common.save}">
      </div>
   </div>
   {/if}
   {if isset($DISPLAY_EXPORT)}
   <div class="tab_content" id="merge">
      <h3>{$LANG.translate.merge_db_file}</h3>
      <fieldset>
         <legend>{$LANG.catalogue.title_import_options}</legend>
         <div>
            <label for="export_opt_replace">{$LANG.translate.replace_original} ({$REPLACE_OPTION}.xml)</label>
            <span><input type="checkbox" name="export_opt[replace]" id="export_opt_replace" value="1"></span>
         </div>
      </fieldset>
   </div>
   {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
      {$tab}
   {/foreach}
  {/if}
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="submit" name="export" value="{$LANG.common.export}">
   </div>
   {/if}
   
</form>