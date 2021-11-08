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
<div id="email_log" class="tab_content">
  <h3>{$LANG.settings.title_email_log}</h3>
  <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
      <fieldset class="width_30">
            <legend>{$LANG.common.filter}</legend>
            <div>
                  <label class="narrow">{$LANG.catalogue.recipient_email}:</label>
                  <input type="text" class="testbox" name="email_filter" value="{$EMAIL_FILTER}">
                  <input type="submit" name="submit" class="tiny" value="{$LANG.common.go}">
                  <a href="?_g=statistics&node=emaillog&reset=1">{$LANG.common.reset}</a>
            </div>
      </fieldset>
  </form>
  {if $EMAIL_LOG}
  <table>
	<thead>
	  <tr>
	  	<td>{$LANG.common.sent}</td>
		<td>{$LANG.common.subject}</td>
		<td>{$LANG.common.to}</td>
		<td>{$LANG.common.from}</td>
		<td colspan="2" align="center">{$LANG.common.read}</td>
		<td>{$LANG.common.date}</td>
		<td>{$LANG.common.edit}</td>
		<td>{$LANG.common.resend}</td>
	  </tr>
	</thead>
	<tbody>
	{foreach from=$EMAIL_LOG item=log}
	  <tr>
	  	<td style="text-align:center">{if $log.result==1}<i class="fa fa-check" title="{$LANG.common.yes}"></i>{else}<i class="fa fa-times" title="{$LANG.common.no}"></i>{/if}</td>
		<td>{$log.subject}</td>
		<td>{foreach from=$log.to_email item=to}
			<a href="?_g=customers&q={$to.email}" title="{$LANG.search.title_search_customers}">{$to.name}</a><br>
		{/foreach}</td>
		<td>
			<a href="?_g=customers&q={$log.from_email}" title="{$LANG.search.title_search_customers}">{$log.from}</a><br>
		</td>
		<td>
			{if !empty($log.content_html)}
			<a href="#" onclick="{literal}$.colorbox({title:'{/literal}{addslashes(htmlentities($log.subject))} ({$LANG.common.html}){literal}',width:'90%', height:'90%', html:'<iframe width=\'100%\' height=\'95%\' frameBorder=\'0\' src=\'?_g=xml&amp;function=viewEmail&amp;id={/literal}{$log.id}{literal}&amp;mode=content_html\'></iframe>'}){/literal}">{$LANG.common.html}</a>
			{/if}
		</td>
		<td>
			{if !empty($log.content_text)}
			<a href="#" onclick="{literal}$.colorbox({title:'{/literal}{addslashes(htmlentities($log.subject))} ({$LANG.common.plain_text}){literal}',width:'90%', height:'90%', html:'<iframe width=\'100%\' height=\'95%\' frameBorder=\'0\' src=\'?_g=xml&amp;function=viewEmail&amp;id={/literal}{$log.id}{literal}&amp;mode=content_text\'></iframe>'}){/literal}">{$LANG.common.plain_text}</a>
			{/if}
		</td>
		<td>{$log.date}</td>
		<td style="text-align:center">{if $log.email_content_id>0}<a href="?_g=documents&amp;node=email&amp;type=content&amp;action=edit&amp;content_id={$log.email_content_id}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>{/if}</td>
		<td style="text-align:center"><a href="?_g=statistics&node=emaillog&resend={$log.id}"><i class="fa fa-paper-plane" title="{$LANG.common.resend}" aria-hidden="true"></i></i></a></td>
	  </tr>
		{if !empty($log.fail_reason)}
		<tr>
			<td class="row_error" colspan="9">{$log.fail_reason}</td>
		</tr>
		{/if}
	  {/foreach}
	</tbody>
  </table>
  {else}
  	{$LANG.form.none}
  {/if}
  <div>{$PAGINATION_EMAIL_LOG}</div>
</div>