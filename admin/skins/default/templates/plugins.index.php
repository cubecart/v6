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

   <div id="plugins" class="tab_content">
      <h3><i class="fa fa-bolt"></i> {$LANG.module.token_title}</h3>
      <p>{$LANG.module.token_desc}</p>
      <form action="{$VAL_SELF}" method="post">
      <fieldset>
         <legend>{$LANG.module.token}</legend>
         <div><label for="plugin_token">{$LANG.module.token}</label><span><input type="textbox" class="textbox" name="plugin_token" id="plugin_token" value="" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX-XXXX"></span></div>
         <div><label for="backup">{$LANG.module.backup_if_exists}</label><span><input type="hidden" id="backup" name="backup" value="1" class="toggle"></span></div>
         <div><label for="backup">{$LANG.module.backup_abort}</label><span><input type="hidden" id="abort" name="abort" value="1" class="toggle"></span></div>
         <div><label>&nbsp;</label><span><input type="submit" value="{$LANG.common.go}"></span></div>
      </fieldset>
      </form>
      <h3>{$LANG.module.available_plugins}</h3>
      {if is_array($MODULES)}
      <form action="{$VAL_SELF}" method="post">
      <table width="70%">
         <thead>
            <tr>
               <th width="45">{$LANG.common.status}</th>
               <th>{$LANG.common.name_and_desc}</th>
               <th>{$LANG.hooks.version}</th>
               <th>{$LANG.common.type}</th>
               <th>{$LANG.common.developer}</th>
               <th width="10">&nbsp;</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$MODULES item=module}
            <tr>
               <td style="text-align:center">
                  <input type="hidden" id="status_{$module.basename}" name="status[{$module.basename}]" value="{$module.config.status}" class="toggle">
                  <input type="hidden" name="type[{$module.basename}]" value="{$module.type}" />
               </td>
               <td><a href="{$module.edit_url}">{$module.name}</a><br>{$module.description}</td>
               <td>{$module.version}</td>
               <td>{$module.type|ucfirst}</td>
               <td>{$module.creator}</td>
               <td nowrap>
                  <a href="{$module.edit_url}" class="edit"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
                  <a href="{$module.delete_url}"  class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
                  {if $module.mobile_optimized=='true'}
                  <a href="javascript:alert('{$LANG.module.mobile_optimized}');"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/phone.png" title="{$LANG.module.mobile_optimized}"></a>
                  {/if}
               </td>
            </tr>
            {/foreach}
            </tbody>
      </table>
      <div class="form_control">
         <input type="submit" value="{$LANG.common.save}">
      </div>
      {else}
      <p>{$LANG.form.none}</p>
      {/if}
      
      </form>
      <form action="?_g=settings&node=language" method="post">
      {if is_array($LANGUAGES)}
      <hr>
      <h3>{$LANG.navigation.nav_languages}</h3>
      <table width="70%">
         <thead>
            <tr>
               <th width="45">{$LANG.common.status}</th>
               <th>{$LANG.common.name_and_desc}</th>
               <th width="10">&nbsp;</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$LANGUAGES item=module}
            <tr>
               <td style="text-align:center">
                  <input type="hidden" name="status[{$module.lang_code}]" id="status_{$module.lang_code}" value="{$module.status}" class="toggle">
               </td>
               <td><a href="{$module.edit_url}">{$module.name}</a></td>
               <td nowrap>
                  <a href="{$module.edit_url}" class="edit"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
                  <a href="{$module.delete_url}"  class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
               </td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      <div class="form_control">
         <input type="submit" value="{$LANG.common.save}">
      </div>
      {/if}
      
      </form>
      {if is_array($SKINS)}
      <hr>
      <h3>{$LANG.settings.logo_all_skins}</h3>
      <table width="70%">
         <thead>
            <tr>
               <th width="50">{$LANG.common.default}</th>
               <th>{$LANG.common.name}</th>
               <th>{$LANG.common.developer}</th>
               <th>{$LANG.hooks.version}</th>
            </tr>
         </thead>
         <tbody>
            {foreach from=$SKINS item=skin}
            <tr>
               <td width="10" align="center">{if $CONFIG.skin_folder == $skin.info.name}<i class="fa fa-check" aria-hidden="true"></i>{else}<i class="fa fa-times" aria-hidden="true"></i>{/if}</td>
               <td>{$skin.info.display}</td>
               <td>{$skin.info.creator}</td>
               <td>{$skin.info.version}</td>
            </tr>
            {/foreach}
         </tbody>
      </table>
      {/if}
</div>