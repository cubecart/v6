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
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   <div id="general" class="tab_content">
      <h3>{$LANG.navigation.nav_subscribers}</h3>
      <fieldset class="width_30">
            <legend>{$LANG.common.filter}</legend>
            <div>
                  <label class="narrow">{$LANG.statistics.search_term}</label>
                  <input type="text" name="email_filter" value="{$EMAIL_FILTER}">
                  <input type="submit" name="submit" class="tiny" value="{$LANG.common.go}">
                  <a href="?_g=customers&node=subscribers&reset=1">{$LANG.common.reset}</a>
            </div>
      </fieldset>
      {if $SUBSCRIBERS}
      <table>
         <thead>
            <th></th>
            <th>{$LANG.common.email}</th>
            <th>{$LANG.common.ip_address}</th>
            <th>{$LANG.common.date}</th>
            <th>{$LANG.catalogue.imported}</th>
            <th>{$LANG.newsletter.dbl_opt_in}</th>
            <th></th>
         </thead>
         <tbody>
            {foreach from=$SUBSCRIBERS item=subscriber}
            <tr>
               <td><input type="checkbox" name="rem_subscriber[{$subscriber.subscriber_id}]" value="1" class="subscribers"></td>
               <td>{if $subscriber.customer_id > 0}<a href="?_g=customers&action=edit&customer_id={$subscriber.customer_id}">{$subscriber.email}</a>{else}{$subscriber.email}{/if}</td>
               <td style="text-align:center">{$subscriber.ip_address}</td>
               <td style="text-align:center">{$subscriber.date}</td>
               <td style="text-align:center">{if $subscriber.imported}<i class="fa fa-check"></i>{else}<i class="fa fa-times"></i>{/if}</td>
               <td style="text-align:center">{if $subscriber.dbl_opt}<i class="fa fa-check"></i>{else}<i class="fa fa-times"></i>{/if}</td>
               <td style="text-align:center"><a href="#" onclick="$.colorbox({ href:'{$STORE_URL}/{$SKIN_VARS.admin_file}?_g=xml&function=subscriber_log&email={$subscriber.email|escape:'url'}'})">{$LANG.common.log}</a> <a href="?_g=customers&node=subscribers&delete={$subscriber.subscriber_id}&token={$SESSION_TOKEN}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a></td>
            </tr>
            {/foreach}
         </tbody>
         <tfooter>
            <tr>
               <td><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt=""></td>
               <td>
                  <a href="#" class="check-all" rel="subscribers">{$LANG.form.check_uncheck}</a>
                  <select name="multi_subscriber_action">
                     <option value="">{$LANG.form.with_selected}</option>
                     <option value="delete">{$LANG.common.remove}</option>
                  </select>
                  <input type="submit" value="{$LANG.common.go}" name="go" class="tiny">
               </td>
            </tr>
         </tfooter>
      </table>
      <p>{$PAGINATION}</p>
      {else}
      <div>{$LANG.form.none}</div>
      {/if}
      <fieldset class="width_30">
            <legend>{$LANG.newsletter.log_search}</legend>
            <div><label class="narrow">{$LANG.user.email}</label>
            <input type="text" name="email_history" id="email_history" value="">
            <input type="button" name="submit" onclick="$.colorbox({ href:'{$STORE_URL}/{$SKIN_VARS.admin_file}?_g=xml&function=subscriber_log&email='+$('#email_history').val()})" class="tiny" value="{$LANG.common.go}">
            </div>
      </fieldset>
      <a href="?_g=customers&node=subscribers&purge=1" class="button delete" title="{$LANG.email.confirm_purge}">{$LANG.email.purge}</a>
   </div>
   <div id="import" class="tab_content">
      <h3>{$LANG.newsletter.import_subscribers}</h3>
      <fieldset>
            <legend>{$LANG.newsletter.import_subscribers}</legend>
            <div><label for="emails">{$LANG.newsletter.email_list}</label><br><textarea name="subscribers" class="textbox" placeholder="{$LANG.newsletter.email_list_placeholder}"></textarea></div>
      </fieldset>
      <div class="form_control">
            <input type="hidden" name="previous-tab" id="previous-tab" value="">
            <input type="submit" value="{$LANG.common.go}">
      </div>
   </div>
</form>
<div id="export_mailing_list" class="tab_content">
      <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" target="_self">
            <h3>{$LANG.email.title_export}</h3>
            <fieldset>
            <legend>{$LANG.email.title_export_settings}</legend>
            <div>
                  <label for="format">{$LANG.email.export_format}</label>
                  <span>
                  <input style="width:335px;" type="text" name="maillist_format" id="format" class="textbox" value="" title="{literal}e.g. &quot;{$FULL_NAME_SHORT}&quot; &lt;{$EMAIL_ADDRESS}&gt;{/literal}">
                        <select name="maillist_extension">
                              <option value="txt">.txt</option>
                              <option value="csv">.csv</option>
                        </select>
                  </span>
            </div>
            <div>
                  <label for="dbl_opt">{$LANG.newsletter.dbl_opt_in_only}</label>
                  <span>
                  <input type="checkbox" name="export_dbl_opt" value="1"{if $CONFIG.dbl_opt=='1'} checked="checked"{/if}>
                  </span>
            </div>
            <div>
                  <input type="submit" class="tiny" id="mailing_list_export" value="{$LANG.common.export}">
            </div>
            </fieldset>
            <table>
            <thead>
                  <tr>
                  <td>{$LANG.email.email_macro}</td>
                  <td>{$LANG.email.email_macro_available}</td>
                  <td>{$LANG.common.description}</td>
                  </tr>
            </thead>
            <tbody>
                  <tr>
                  <td>{literal}{$EMAIL_ADDRESS}{/literal}</td>
                  <td style="text-align:center"><i class="fa fa-check" alt="{$LANG.common.yes}"></i></td>
                  <td>{$LANG.user.email_address} {$LANG.email.example_email}</td>
                  </tr>
                  <tr>
                  <td>{literal}{$FULL_NAME_LONG}{/literal}</td>
                  <td style="text-align:center"><i class="fa fa-times" alt="{$LANG.common.no}"></i></td>
                  <td>{$LANG.user.fullname_long} {$LANG.email.example_fullname_long}</td>
                  </tr>
                  <tr>
                  <td>{literal}{$FULL_NAME_SHORT}{/literal}</td>
                  <td style="text-align:center"><i class="fa fa-times" alt="{$LANG.common.no}"></i></td>
                  <td>{$LANG.user.fullname_short} {$LANG.email.example_fullname_short}</td>
                  </tr>
                  <tr>
                  <td>{literal}{$TITLE}{/literal}</td>
                  <td style="text-align:center"><i class="fa fa-times" alt="{$LANG.common.no}"></i></td>
                  <td>{$LANG.user.title} {$LANG.email.example_title}</td>
                  </tr>
                  <tr>
                  <td>{literal}{$FIRST_NAME}{/literal}</td>
                  <td style="text-align:center"><i class="fa fa-times" alt="{$LANG.common.no}"></i></td>
                  <td>{$LANG.user.name_first} {$LANG.email.example_name_first}</td>
                  </tr>
                  <tr>
                  <td>{literal}{$LAST_NAME}{/literal}</td>
                  <td style="text-align:center"><i class="fa fa-times" alt="{$LANG.common.no}"></i></td>
                  <td>{$LANG.user.name_first} {$LANG.email.example_name_last}</td>
                  </tr>
            </tbody>
            </table>
            <p>{$LANG.email.help_macro}</p>
            <input type="hidden" name="previous-tab" id="previous-tab" value="">
      </form>
</div>

<div id="gdpr" class="tab_content">
      <h3>{$LANG.search.gdpr_tools}</h3>
      <a href="?_g=customers&node=subscribers&del_single_opt=1" title="{$LANG.notification.confirm_continue}" class="button delete">{$LANG.newsletter.delete_single_optin}</a>
</div>