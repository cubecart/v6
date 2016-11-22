{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div id="email_log" class="tab_content">
  <h3>{$LANG.settings.title_email_log}</h3>
  {if $EMAIL_LOG}
  <table>
	<thead>
	  <tr>
	  	<td>{$LANG.common.sent}</td>
		<td>{$LANG.common.subject}</td>
		<td>{$LANG.common.to}</td>
		<td colspan="2" align="center">{$LANG.common.read}</td>
		<td>{$LANG.common.date}</td>
		<td>{$LANG.common.edit}</td>
	  </tr>
	</thead>
	<tbody>
	{foreach from=$EMAIL_LOG item=log}
	  <tr>
	  	<td align="center">{if $log.result==1}<i class="fa fa-check" title="{$LANG.common.yes}"></i>{else}<i class="fa fa-times" title="{$LANG.common.no}"></i>{/if}</td>
		<td>{$log.subject}</td>
		<td>{$log.to|replace:',':'<br>'}</td>
		<td>
			<a href="#" onclick="{literal}$.colorbox({title:'{/literal}{$log.subject} ({$LANG.common.html}){literal}',width:'90%', height:'90%', html:'<iframe width=\'100%\' height=\'95%\' frameBorder=\'0\' src=\'?_g=xml&amp;function=viewEmail&amp;id={/literal}{$log.id}{literal}&amp;mode=content_html\'></iframe>'}){/literal}">{$LANG.common.html}</a>
		</td>
		<td>
			<a href="#" onclick="{literal}$.colorbox({title:'{/literal}{$log.subject} ({$LANG.common.plain_text}){literal}',width:'90%', height:'90%', html:'<iframe width=\'100%\' height=\'95%\' frameBorder=\'0\' src=\'?_g=xml&amp;function=viewEmail&amp;id={/literal}{$log.id}{literal}&amp;mode=content_text\'></iframe>'}){/literal}">{$LANG.common.plain_text}</a>
		</td>
		<td>{$log.date}</td>
		<td align="center">{if $log.email_content_id>0}<a href="?_g=documents&amp;node=email&amp;type=content&amp;action=edit&amp;content_id={$log.email_content_id}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>{/if}<a href="?_g=customers&q={$log.to}"><i class="fa fa-search" title="{$LANG.search.title_search_customers}"></i></a></td>
		
	  </tr>
	  {/foreach}
	</tbody>
  </table>
  {else}
  	{$LANG.form.none}
  {/if}
  <div>{$PAGINATION_EMAIL_LOG}</div>
</div>