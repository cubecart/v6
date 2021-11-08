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
{if $REDIRECTS}
<h3>{$LANG.settings.redirects}</h3>
<table>
	<thead>
		<tr>
			<th>{$LANG.common.path}</th>
			<th>{$LANG.common.status_code}</th>
			<th>{$LANG.form.action}</th>
		</tr>
	</thead>
	<tbody>
	{foreach $REDIRECTS item=redirect}
		<tr>
			<td>{$redirect.path}</td>
			<td style="text-align:center">{$redirect.redirect}</td>
			<td style="text-align:center"><a href="?_g=settings&node=redirects&delete={$redirect.id}&item_id={$redirect.item_id}&type={$redirect.type}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></td>
		</tr>
	{/foreach}
	</tbody>
</table>
{/if}